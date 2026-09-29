<?php

namespace Database\Seeders;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $orders = PurchaseOrder::query()->with('documents')->where('approval_status', 'Approved')->get();
        if ($orders->isEmpty()) {
            $this->command?->warn('No approved purchase orders were found. Run PurchaseOrderSeeder first.');
            return;
        }

        foreach ($orders as $index => $order) {
            $date = Carbon::parse($order->po_date)->addDays(2);
            $days = preg_match('/Net\s+(\d+)/i', $order->payment_term ?? '', $match) ? (int) $match[1] : 0;
            $paid = $index % 3 === 0 ? (float) $order->total : ($index % 3 === 1 ? round((float) $order->total * .55, 2) : 0);
            $status = $paid >= (float) $order->total ? 'Paid' : ($paid > 0 ? 'Partial' : ($date->copy()->addDays($days)->isPast() ? 'Overdue' : 'Unpaid'));

            $invoice = PurchaseInvoice::updateOrCreate(
                ['project_id' => $order->project_id, 'invoice_no' => 'PINV-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'purchase_order_id' => $order->id, 'seller_id' => $order->seller_id, 'created_by' => $order->created_by,
                    'po_no' => $order->po_no, 'seller_no' => $order->seller_no, 'seller_name' => $order->seller_name,
                    'seller_details' => $order->seller_details, 'requested_by' => $order->requested_by,
                    'department' => $order->department, 'project' => $order->project, 'requester_details' => $order->requester_details,
                    'invoice_date' => $date->toDateString(), 'due_date' => $date->copy()->addDays($days)->toDateString(),
                    'delivery_date' => $order->expected_delivery_at?->toDateString(), 'delivery_location' => $order->delivery_location,
                    'delivery_details' => $order->delivery_details, 'currency_code' => $order->currency_code,
                    'status' => 'Approved', 'payment_status' => $status, 'invoice_type' => 'Regular',
                    'payment_term' => $order->payment_term, 'payment_method' => $order->payment_method,
                    'subtotal' => $order->subtotal, 'sales_tax' => $order->sales_tax, 'total' => $order->total,
                    'paid' => $paid, 'items' => $order->items, 'terms' => $order->terms,
                    'notes' => 'Seeded purchase invoice linked to '.$order->po_no.'.',
                ]
            );

            $invoice->payments()->delete();
            if ($paid > 0) {
                $invoice->payments()->create([
                    'payment_no' => 'BPAY-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'payment_date' => $date->copy()->addDays(5)->toDateString(), 'amount' => $paid,
                    'method' => $order->payment_method, 'reference' => 'SEED-REF-'.($index + 1), 'notes' => 'Seeded bill payment.',
                ]);
            }

            DB::table('purchase_invoice_activities')->where('purchase_invoice_id', $invoice->id)->delete();
            foreach (['Created', 'Approved'] as $step => $action) {
                DB::table('purchase_invoice_activities')->insert([
                    'purchase_invoice_id' => $invoice->id, 'user_id' => $order->created_by, 'actor' => 'Seeder',
                    'action' => $action, 'changes' => json_encode(['seed' => true]),
                    'created_at' => $date->copy()->addHours($step), 'updated_at' => $date->copy()->addHours($step),
                ]);
            }
            if ($paid > 0) {
                DB::table('purchase_invoice_activities')->insert([
                    'purchase_invoice_id' => $invoice->id, 'user_id' => $order->created_by, 'actor' => 'Seeder',
                    'action' => 'Received payment', 'changes' => json_encode(['amount' => $paid]),
                    'created_at' => $date->copy()->addDays(5), 'updated_at' => $date->copy()->addDays(5),
                ]);
            }
        }

        $this->command?->info('Seeded '.$orders->count().' purchase invoices.');
    }
}
