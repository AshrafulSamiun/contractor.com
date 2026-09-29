<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ResolvesAccountSetupProject;
use App\Models\AccountSetup;
use App\Models\PurchaseOrder;
use App\Models\PurchaseInvoice;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    use ResolvesAccountSetupProject;

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['creator:id,name', 'documents'])
            ->where('project_id', $this->accountSetupProjectId($request))->latest('po_date')->latest('id');
        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('po_no', 'like', "%{$term}%")
                ->orWhere('seller_name', 'like', "%{$term}%")->orWhere('seller_no', 'like', "%{$term}%"));
        }
        foreach (['status', 'payment_status', 'department', 'project'] as $field) {
            if ($request->filled($field)) $query->where($field, $request->input($field));
        }
        if ($request->filled('seller')) $query->where('seller_name', $request->seller);
        if ($request->filled('from')) $query->whereDate('po_date', '>=', $request->from);
        if ($request->filled('to')) $query->whereDate('po_date', '<=', $request->to);
        return response()->json(['data' => $query->paginate(min(100, max(1, $request->integer('per_page', 15))))]);
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->ensureProject($request, $purchaseOrder);
        return response()->json(['data' => $this->present($purchaseOrder)]);
    }

    public function store(Request $request)
    {
        $projectId = $this->accountSetupProjectId($request);
        $data = $this->validated($request, $projectId);
        $order = DB::transaction(function () use ($request, $projectId, $data) {
            // Serialize number allocation within this account.
            AccountSetup::whereKey($projectId)->lockForUpdate()->firstOrFail();
            $data['po_no'] = $data['po_no'] ?: $this->nextNumber($projectId);
            abort_if(PurchaseOrder::where('project_id', $projectId)->where('po_no', $data['po_no'])->exists(), 422, 'This PO number is already in use.');
            $order = PurchaseOrder::create($data + [
                'project_id' => $projectId, 'created_by' => $request->user()->id,
                'approval_status' => 'Draft', 'payment_status' => 'Unpaid',
            ]);
            $this->audit($request, $order, 'Created');
            return $order;
        });
        return response()->json(['data' => $this->present($order)], 201);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->ensureProject($request, $purchaseOrder);
        $data = $this->validated($request, $purchaseOrder->project_id, $purchaseOrder);
        DB::transaction(function () use ($request, $purchaseOrder, $data) {
            $order = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            abort_if($order->documents()->exists(), 422, 'An invoiced order cannot be edited. Duplicate it to create a new order.');
            $order->fill($data);
            $changes = array_keys($order->getDirty());
            if ($changes) {
                $order->approval_status = 'Draft';
                $order->save();
                $this->audit($request, $order, 'Updated', $changes);
            }
        });
        return response()->json(['data' => $this->present($purchaseOrder->fresh())]);
    }

    public function destroy(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->ensureProject($request, $purchaseOrder);
        $paths = DB::transaction(function () use ($purchaseOrder) {
            $order = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            abort_if($order->documents()->exists(), 422, 'An order with financial documents cannot be deleted.');
            $paths = DB::table('purchase_order_attachments')->where('purchase_order_id', $order->id)->pluck('path')->all();
            $order->delete();
            return $paths;
        });
        Storage::disk('local')->delete($paths);
        return response()->json(['success' => true]);
    }

    public function workflow(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->ensureProject($request, $purchaseOrder);
        $action = $request->validate(['action' => 'required|in:submit,approve,reject,convert'])['action'];
        DB::transaction(function () use ($request, $purchaseOrder, $action) {
            $order = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            abort_if($order->documents()->exists(), 422, 'This order has already been converted to an invoice.');
            abort_if(in_array($order->status, ['Cancelled', 'Expired']) || ($order->expiry_at && $order->expiry_at->isPast()), 422, 'This order is cancelled or expired.');
            if ($action === 'convert') {
                abort_unless($order->approval_status === 'Approved', 422, 'Approve the purchase order before converting it.');
                $days = preg_match('/Net\s+(\d+)/i', $order->payment_term ?? '', $match) ? (int) $match[1] : 0;
                $due = $order->payment_term === 'End of Month' ? now()->endOfMonth() : now()->addDays($days);
                $order->documents()->create([
                    'type' => 'Purchase Invoice', 'number' => 'PINV-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                    'date' => now()->toDateString(), 'due_date' => $due->toDateString(),
                    'subtotal' => $order->subtotal, 'sales_tax' => $order->sales_tax, 'total' => $order->total,
                    'items' => $order->items,
                ]);
                PurchaseInvoice::firstOrCreate(
                    ['purchase_order_id' => $order->id],
                    [
                        'project_id' => $order->project_id, 'seller_id' => $order->seller_id,
                        'created_by' => $request->user()->id, 'invoice_no' => 'PINV-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                        'po_no' => $order->po_no, 'seller_no' => $order->seller_no, 'seller_name' => $order->seller_name,
                        'seller_details' => $order->seller_details, 'requested_by' => $order->requested_by,
                        'department' => $order->department, 'project' => $order->project, 'requester_details' => $order->requester_details,
                        'invoice_date' => now()->toDateString(), 'due_date' => $due->toDateString(),
                        'delivery_date' => $order->expected_delivery_at?->toDateString(), 'delivery_location' => $order->delivery_location,
                        'delivery_details' => $order->delivery_details, 'currency_code' => $order->currency_code,
                        'status' => 'Approved', 'payment_status' => 'Unpaid', 'invoice_type' => 'Regular',
                        'payment_term' => $order->payment_term, 'payment_method' => $order->payment_method,
                        'subtotal' => $order->subtotal, 'sales_tax' => $order->sales_tax, 'total' => $order->total,
                        'paid' => 0, 'items' => $order->items, 'terms' => $order->terms, 'notes' => $order->notes,
                    ]
                );
                $this->audit($request, $order, 'Converted to purchase invoice');
            } else {
                $allowed = $action === 'submit' ? ['Draft', 'Rejected'] : ['Draft', 'Submitted'];
                abort_unless(in_array($order->approval_status, $allowed), 422, 'This approval action is no longer available.');
                $order->approval_status = ['submit' => 'Submitted', 'approve' => 'Approved', 'reject' => 'Rejected'][$action];
                $order->status = ['submit' => 'Pending', 'approve' => 'Accepted', 'reject' => 'Cancelled'][$action];
                $order->save();
                $this->audit($request, $order, $order->approval_status);
            }
        });
        return response()->json(['data' => $this->present($purchaseOrder->fresh())]);
    }

    public function addDocument(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->ensureProject($request, $purchaseOrder);
        $data = $request->validate([
            'type' => 'required|in:Purchase Invoice Return,Debit Note,Credit Note,Bill Payment',
            'number' => ['required', 'string', 'max:100', Rule::unique('purchase_order_documents')->where('purchase_order_id', $purchaseOrder->id)],
            'date' => 'required|date', 'subtotal' => 'required|numeric|min:0|max:999999999999',
            'sales_tax' => 'required|numeric|min:0|max:999999999999', 'notes' => 'nullable|string|max:5000',
        ]);
        $data['subtotal'] = round($data['subtotal'], 2);
        $data['sales_tax'] = round($data['sales_tax'], 2);
        $data['total'] = $data['subtotal'] + $data['sales_tax'];
        abort_if($data['total'] <= 0, 422, 'The document total must be greater than zero.');
        DB::transaction(function () use ($request, $purchaseOrder, $data) {
            $order = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            abort_unless($order->documents()->where('type', 'Purchase Invoice')->exists(), 422, 'Convert the order to an invoice first.');
            // Buyer debit notes, supplier credits, returns and payments reduce accounts payable.
            $docs = $order->documents()->get();
            foreach (['subtotal', 'sales_tax'] as $field) {
                $remaining = round($docs->sum(fn ($doc) => ($doc->type === 'Purchase Invoice' ? 1 : -1) * (float) $doc->$field), 2);
                abort_if($data[$field] > $remaining, 422, 'The '.$field.' exceeds the remaining invoice amount.');
            }
            $balance = round($docs->sum(fn ($doc) => $doc->type === 'Purchase Invoice' ? (float) $doc->total : -(float) $doc->total), 2);
            abort_if($data['total'] > $balance, 422, 'The amount exceeds the outstanding invoice balance.');
            $order->documents()->create($data);
            $order->payment_status = round($balance - $data['total'], 2) <= 0 ? 'Paid' : 'Partially Paid';
            $order->save();
            $this->audit($request, $order, 'Recorded '.$data['type'], ['number' => $data['number'], 'total' => $data['total']]);
        });
        return response()->json(['data' => $this->present($purchaseOrder->fresh())]);
    }

    public function attach(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->ensureProject($request, $purchaseOrder);
        $request->validate(['file' => 'required|file|mimes:pdf,png,jpg,jpeg,txt,csv,doc,docx,xls,xlsx|max:10240']);
        $file = $request->file('file');
        $path = $file->store('purchase-orders/'.$purchaseOrder->id, 'local');
        try {
            DB::transaction(function () use ($request, $purchaseOrder, $file, $path) {
                DB::table('purchase_order_attachments')->insert([
                    'purchase_order_id' => $purchaseOrder->id, 'name' => basename($file->getClientOriginalName()),
                    'path' => $path, 'size' => $file->getSize(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->audit($request, $purchaseOrder, 'Attached file', ['name' => basename($file->getClientOriginalName())]);
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }
        return response()->json(['data' => $this->present($purchaseOrder->fresh())]);
    }

    public function download(Request $request, PurchaseOrder $purchaseOrder, int $attachment)
    {
        $this->ensureProject($request, $purchaseOrder);
        $file = DB::table('purchase_order_attachments')->where('purchase_order_id', $purchaseOrder->id)->where('id', $attachment)->first();
        abort_unless($file, 404);
        return Storage::disk('local')->download($file->path, $file->name);
    }

    private function present(PurchaseOrder $order): array
    {
        return $order->load(['creator:id,name', 'documents'])->toArray() + [
            'activities' => DB::table('purchase_order_activities')->where('purchase_order_id', $order->id)->latest('id')->get(),
            'attachments' => DB::table('purchase_order_attachments')->where('purchase_order_id', $order->id)->get(['id', 'name', 'size', 'created_at']),
        ];
    }

    private function audit(Request $request, PurchaseOrder $order, string $action, array $changes = []): void
    {
        DB::table('purchase_order_activities')->insert([
            'purchase_order_id' => $order->id, 'user_id' => $request->user()->id, 'actor' => $request->user()->name ?? 'User',
            'action' => $action, 'changes' => json_encode($changes), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function validated(Request $request, int $projectId, ?PurchaseOrder $order = null): array
    {
        $data = $request->validate([
            'po_no' => [$order ? 'required' : 'nullable', 'string', 'max:40', Rule::unique('purchase_orders')->where('project_id', $projectId)->ignore($order?->id)],
            'seller_id' => ['nullable', 'integer', Rule::exists('sellers', 'id')->where('project_id', $projectId)],
            'seller_no' => 'nullable|string|max:40', 'seller_name' => 'required|string|max:255',
            'seller_details' => 'nullable|array:contact_person,phone,email,website,address,tax_number,vendor_category',
            'seller_details.*' => 'nullable|string|max:2000',
            'requester_details' => 'nullable|array:phone,email,company_name,address', 'requester_details.*' => 'nullable|string|max:2000',
            'requested_by' => 'nullable|string|max:255', 'department' => 'nullable|string|max:255', 'project' => 'nullable|string|max:255',
            'po_date' => 'required|date', 'expiry_at' => 'nullable|date|after_or_equal:po_date', 'expected_delivery_at' => 'nullable|date|after_or_equal:po_date',
            'delivery_location' => 'nullable|string|max:255', 'delivery_details' => 'nullable|array:contact_person,phone,email', 'delivery_details.*' => 'nullable|string|max:255',
            'currency_code' => 'required|string|size:3', 'status' => 'required|in:Pending,Confirmed,Cancelled,Expired',
            'payment_term' => 'nullable|string|max:100', 'payment_method' => 'nullable|string|max:255',
            'items' => 'required|array|min:1|max:500', 'items.*.name' => 'required|string|max:255', 'items.*.code' => 'nullable|string|max:100',
            'items.*.uom' => 'nullable|string|max:40', 'items.*.quantity' => 'required|numeric|gt:0|max:1000000',
            'items.*.cost_rate' => 'required|numeric|min:0|max:1000000', 'items.*.sales_tax' => 'required|numeric|min:0|max:1000000000',
            'terms' => 'nullable|string|max:10000', 'notes' => 'nullable|string|max:10000',
        ]);
        $data['po_no'] ??= '';
        if (!empty($data['seller_id']) && (!$order || (int) $order->seller_id !== (int) $data['seller_id'])) {
            $seller = Seller::where('project_id', $projectId)->findOrFail($data['seller_id']);
            $data['seller_name'] = $seller->seller_name;
            $data['seller_no'] = 'SLR-'.str_pad((string) $seller->id, 3, '0', STR_PAD_LEFT);
            $data['seller_details'] = $seller->only(['contact_person', 'phone', 'email', 'website', 'address', 'tax_number', 'vendor_category']);
        }
        $items = collect($data['items']);
        $data['subtotal'] = round($items->sum(fn ($item) => round($item['quantity'] * $item['cost_rate'], 2)), 2);
        $data['sales_tax'] = round($items->sum(fn ($item) => round($item['sales_tax'], 2)), 2);
        $data['total'] = $data['subtotal'] + $data['sales_tax'];
        return $data;
    }

    private function ensureProject(Request $request, PurchaseOrder $order): void
    {
        abort_unless((int) $order->project_id === $this->accountSetupProjectId($request), 404);
    }

    private function nextNumber(int $projectId): string
    {
        $next = (int) PurchaseOrder::where('project_id', $projectId)->max('id') + 1;
        do {
            $number = 'PO-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT);
        } while (PurchaseOrder::where('project_id', $projectId)->where('po_no', $number)->exists());
        return $number;
    }
}
