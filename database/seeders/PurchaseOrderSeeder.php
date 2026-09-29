<?php

namespace Database\Seeders;

use App\Models\AccountSetup;
use App\Models\PurchaseOrder;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $projects = AccountSetup::query()
            ->join('users', 'users.id', '=', 'account_setups.user_id')
            ->where('users.is_active', true)
            ->where('users.role', '!=', 'super_admin')
            ->get([
                'account_setups.id as project_id',
                'account_setups.user_id as owner_user_id',
                'account_setups.company_name',
                'account_setups.company_address',
                'account_setups.company_phone',
                'account_setups.contact_business_email',
            ]);

        if ($projects->isEmpty()) {
            $this->command?->warn('Purchase orders were not seeded because no active account setup projects were found.');
            return;
        }

        $orderTemplates = [
            [
                'seller' => 0,
                'po_no' => 'PO-SEED-0001',
                'status' => 'Accepted',
                'approval_status' => 'Approved',
                'payment_status' => 'Partially Paid',
                'days_ago' => 18,
                'expiry_days' => 22,
                'delivery_days' => 7,
                'department' => 'Procurement',
                'project' => 'Downtown Office Renovation',
                'payment_term' => 'Net 30',
                'payment_method' => 'Bank Transfer',
                'items' => [
                    ['name' => 'Electrical Installation', 'code' => 'SER-SEED-001', 'uom' => 'HRS', 'quantity' => 32, 'cost_rate' => 75.00, 'sales_tax' => 312.00],
                    ['name' => 'General Labor', 'code' => 'SER-SEED-002', 'uom' => 'HRS', 'quantity' => 20, 'cost_rate' => 45.00, 'sales_tax' => 117.00],
                ],
                'documents' => [
                    ['type' => 'Purchase Invoice', 'number' => 'PINV-SEED-0001', 'date_offset' => -12, 'due_offset' => 18, 'factor' => 1],
                    ['type' => 'Bill Payment', 'number' => 'BPAY-SEED-0001', 'date_offset' => -5, 'factor' => 0.55],
                ],
            ],
            [
                'seller' => 1,
                'po_no' => 'PO-SEED-0002',
                'status' => 'Pending',
                'approval_status' => 'Submitted',
                'payment_status' => 'Unpaid',
                'days_ago' => 5,
                'expiry_days' => 20,
                'delivery_days' => 12,
                'department' => 'Operations',
                'project' => 'Warehouse Expansion',
                'payment_term' => 'Net 15',
                'payment_method' => 'Cheque',
                'items' => [
                    ['name' => 'Equipment Rental', 'code' => 'SER-SEED-003', 'uom' => 'DAY', 'quantity' => 6, 'cost_rate' => 180.00, 'sales_tax' => 140.40],
                    ['name' => 'Site Inspection', 'code' => 'SER-SEED-004', 'uom' => 'EACH', 'quantity' => 2, 'cost_rate' => 250.00, 'sales_tax' => 65.00],
                ],
                'documents' => [],
            ],
            [
                'seller' => 2,
                'po_no' => 'PO-SEED-0003',
                'status' => 'Confirmed',
                'approval_status' => 'Approved',
                'payment_status' => 'Paid',
                'days_ago' => 36,
                'expiry_days' => 10,
                'delivery_days' => -2,
                'department' => 'Field Services',
                'project' => 'Retail Fit-Out',
                'payment_term' => 'Due on Receipt',
                'payment_method' => 'Credit Card',
                'items' => [
                    ['name' => 'Interior Painting', 'code' => 'SER-SEED-005', 'uom' => 'SQFT', 'quantity' => 850, 'cost_rate' => 2.75, 'sales_tax' => 303.88],
                    ['name' => 'Flooring Installation', 'code' => 'SER-SEED-006', 'uom' => 'SQFT', 'quantity' => 430, 'cost_rate' => 6.50, 'sales_tax' => 363.35],
                ],
                'documents' => [
                    ['type' => 'Purchase Invoice', 'number' => 'PINV-SEED-0003', 'date_offset' => -30, 'due_offset' => -15, 'factor' => 1],
                    ['type' => 'Bill Payment', 'number' => 'BPAY-SEED-0003', 'date_offset' => -20, 'factor' => 1],
                ],
            ],
        ];

        foreach ($projects as $project) {
            $sellers = Seller::query()
                ->where('project_id', $project->project_id)
                ->where('is_active', true)
                ->orderBy('id')
                ->take(3)
                ->get();

            if ($sellers->isEmpty()) {
                $this->command?->warn("Purchase orders were skipped for project {$project->project_id} because no sellers were found.");
                continue;
            }

            foreach ($orderTemplates as $index => $template) {
                $seller = $sellers[$template['seller']] ?? $sellers->first();
                $items = $template['items'];
                $subtotal = $this->subtotal($items);
                $salesTax = $this->salesTax($items);
                $total = $subtotal + $salesTax;
                $poDate = Carbon::today()->subDays($template['days_ago']);

                $order = PurchaseOrder::updateOrCreate(
                    [
                        'project_id' => $project->project_id,
                        'po_no' => $template['po_no'],
                    ],
                    [
                        'created_by' => $project->owner_user_id,
                        'seller_id' => $seller->id,
                        'seller_no' => 'SLR-'.str_pad((string) $seller->id, 3, '0', STR_PAD_LEFT),
                        'seller_name' => $seller->seller_name,
                        'seller_details' => $seller->only(['contact_person', 'phone', 'email', 'website', 'address', 'tax_number', 'vendor_category']),
                        'requested_by' => $project->company_name ?: 'Accounts Payable',
                        'department' => $template['department'],
                        'project' => $template['project'],
                        'requester_details' => [
                            'phone' => $project->company_phone,
                            'email' => $project->contact_business_email,
                            'company_name' => $project->company_name,
                            'address' => $project->company_address,
                        ],
                        'po_date' => $poDate->toDateString(),
                        'expiry_at' => $poDate->copy()->addDays($template['expiry_days'])->setTime(17, 0),
                        'expected_delivery_at' => $poDate->copy()->addDays($template['delivery_days'])->setTime(10, 0),
                        'delivery_location' => $project->company_address ?: 'Main project site',
                        'delivery_details' => [
                            'contact_person' => $project->company_name ?: 'Site Coordinator',
                            'phone' => $project->company_phone,
                            'email' => $project->contact_business_email,
                        ],
                        'currency_code' => 'CAD',
                        'status' => $template['status'],
                        'approval_status' => $template['approval_status'],
                        'payment_status' => $template['payment_status'],
                        'payment_term' => $template['payment_term'],
                        'payment_method' => $template['payment_method'],
                        'subtotal' => $subtotal,
                        'sales_tax' => $salesTax,
                        'total' => $total,
                        'items' => $items,
                        'terms' => 'Materials and services must match the approved scope. Notify procurement before substitutions or schedule changes.',
                        'notes' => 'Seeded sample purchase order for the accounting purchase order workflow.',
                    ]
                );

                $this->refreshDocuments($order, $template['documents']);
                $this->refreshActivities($order, $project->owner_user_id, $template, $index);
            }
        }

        $this->command?->info(sprintf(
            'Seeded %d purchase orders for %d account setup project(s).',
            count($orderTemplates),
            $projects->count()
        ));
    }

    private function refreshDocuments(PurchaseOrder $order, array $documents): void
    {
        $order->documents()->delete();

        foreach ($documents as $document) {
            $subtotal = round((float) $order->subtotal * $document['factor'], 2);
            $salesTax = round((float) $order->sales_tax * $document['factor'], 2);

            $order->documents()->create([
                'type' => $document['type'],
                'number' => $document['number'],
                'date' => Carbon::today()->addDays($document['date_offset'])->toDateString(),
                'due_date' => isset($document['due_offset']) ? Carbon::today()->addDays($document['due_offset'])->toDateString() : null,
                'subtotal' => $subtotal,
                'sales_tax' => $salesTax,
                'total' => $subtotal + $salesTax,
                'items' => $document['type'] === 'Purchase Invoice' ? $order->items : null,
                'notes' => 'Seeded '.$document['type'].' for '.$order->po_no,
            ]);
        }
    }

    private function refreshActivities(PurchaseOrder $order, int $userId, array $template, int $index): void
    {
        DB::table('purchase_order_activities')->where('purchase_order_id', $order->id)->delete();

        $actions = ['Created'];
        if ($template['approval_status'] !== 'Draft') {
            $actions[] = $template['approval_status'];
        }
        if (! empty($template['documents'])) {
            $actions[] = 'Converted to purchase invoice';
        }

        foreach ($actions as $step => $action) {
            DB::table('purchase_order_activities')->insert([
                'purchase_order_id' => $order->id,
                'user_id' => $userId,
                'actor' => 'Seeder',
                'action' => $action,
                'changes' => json_encode(['seed' => true, 'sample' => $index + 1]),
                'created_at' => Carbon::now()->subDays(max(0, 10 - $step))->toDateTimeString(),
                'updated_at' => Carbon::now()->subDays(max(0, 10 - $step))->toDateTimeString(),
            ]);
        }
    }

    private function subtotal(array $items): float
    {
        return round(collect($items)->sum(fn ($item) => round($item['quantity'] * $item['cost_rate'], 2)), 2);
    }

    private function salesTax(array $items): float
    {
        return round(collect($items)->sum(fn ($item) => round($item['sales_tax'], 2)), 2);
    }
}
