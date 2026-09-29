<?php

namespace Database\Seeders;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseReturnSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = PurchaseInvoice::where('status', 'Approved')->orderBy('id')->get();
        if ($invoices->isEmpty()) {
            $this->command?->warn('No approved purchase invoices found. Run PurchaseInvoiceSeeder first.');
            return;
        }

        $reasons = ['Material defect', 'Wrong item delivered', 'Quality issue', 'Damaged in transit', 'Over order'];
        foreach ($invoices as $index => $invoice) {
            $sourceItems = collect($invoice->items)->take(2)->values();
            $items = $sourceItems->map(function ($item, $itemIndex) {
                $quantity = max(0.01, round((float) $item['quantity'] * .25, 2));
                $ratio = $quantity / (float) $item['quantity'];
                return [
                    'invoice_item_index' => $itemIndex, 'name' => $item['name'], 'code' => $item['code'] ?? '',
                    'uom' => $item['uom'] ?? 'PCS', 'quantity' => $quantity, 'cost_rate' => (float) $item['cost_rate'],
                    'sales_tax' => round((float) ($item['sales_tax'] ?? 0) * $ratio, 2),
                ];
            })->all();
            $subtotal = round(collect($items)->sum(fn ($item) => $item['quantity'] * $item['cost_rate']), 2);
            $tax = round(collect($items)->sum('sales_tax'), 2);
            $total = $subtotal + $tax;
            $returnDate = Carbon::parse($invoice->invoice_date)->addDays(3);
            $status = $index % 3 === 2 ? 'Pending' : 'Returned';
            $refunded = $status === 'Returned' ? ($index % 2 === 0 ? $total : round($total * .5, 2)) : 0;

            $return = PurchaseReturn::updateOrCreate(
                ['project_id' => $invoice->project_id, 'return_no' => 'PRT-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'purchase_invoice_id' => $invoice->id, 'created_by' => $invoice->created_by,
                    'return_date' => $returnDate->toDateString(), 'return_due_date' => $returnDate->copy()->addDays(14)->toDateString(),
                    'status' => $status, 'refund_status' => $refunded >= $total ? 'Paid' : ($refunded > 0 ? 'Partial' : 'Unpaid'),
                    'reason' => $reasons[$index % count($reasons)], 'subtotal' => $subtotal, 'sales_tax' => $tax,
                    'total' => $total, 'refunded' => $refunded, 'items' => $items,
                    'notes' => 'Seeded purchase return for '.$invoice->invoice_no.'.',
                ]
            );

            $return->refunds()->delete();
            if ($refunded > 0) {
                $return->refunds()->create([
                    'refund_no' => 'RFND-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'refund_date' => $returnDate->copy()->addDays(5)->toDateString(), 'amount' => $refunded,
                    'method' => $invoice->payment_method, 'reference' => 'RETURN-SEED-'.($index + 1), 'notes' => 'Seeded seller refund.',
                ]);
            }

            DB::table('purchase_return_activities')->where('purchase_return_id', $return->id)->delete();
            foreach (['Created', $status] as $step => $action) {
                DB::table('purchase_return_activities')->insert([
                    'purchase_return_id' => $return->id, 'user_id' => $invoice->created_by, 'actor' => 'Seeder',
                    'action' => $action, 'changes' => json_encode(['seed' => true]),
                    'created_at' => $returnDate->copy()->addHours($step), 'updated_at' => $returnDate->copy()->addHours($step),
                ]);
            }
        }

        $this->command?->info('Seeded '.$invoices->count().' purchase returns.');
    }
}
