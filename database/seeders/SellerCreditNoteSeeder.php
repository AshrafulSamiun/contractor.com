<?php

namespace Database\Seeders;

use App\Models\PurchaseInvoice;
use App\Models\SellerCreditNote;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SellerCreditNoteSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = PurchaseInvoice::where('status', 'Approved')->orderBy('id')->get();
        if ($invoices->isEmpty()) {
            $this->command?->warn('No approved purchase invoices found. Run PurchaseInvoiceSeeder first.');
            return;
        }
        $reasons = ['Material Return', 'Price Adjustment', 'Damaged Goods', 'Overpayment', 'Service Credit'];
        foreach ($invoices as $index => $invoice) {
            $source = collect($invoice->items)->first();
            if (! $source) continue;
            $subtotal = round(min((float) $invoice->subtotal * .06, 200 + $index * 25), 2);
            $tax = round($subtotal * ((float) $invoice->sales_tax / max(1, (float) $invoice->subtotal)), 2);
            $date = Carbon::parse($invoice->invoice_date)->addDays(5);
            $status = ['Approved', 'Approved', 'Pending', 'Rejected'][$index % 4];
            $note = SellerCreditNote::updateOrCreate(
                ['project_id' => $invoice->project_id, 'credit_note_no' => 'CN-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'purchase_invoice_id' => $invoice->id, 'created_by' => $invoice->created_by,
                    'credit_note_date' => $date->toDateString(), 'due_date' => $date->toDateString(),
                    'status' => $status, 'reason' => $reasons[$index % count($reasons)],
                    'subtotal' => $subtotal, 'sales_tax' => $tax, 'total' => $subtotal + $tax,
                    'items' => [[
                        'invoice_item_index' => 0, 'name' => $reasons[$index % count($reasons)],
                        'code' => $source['code'] ?? 'CREDIT', 'uom' => 'LS', 'quantity' => 1,
                        'cost_rate' => $subtotal, 'sales_tax' => $tax,
                    ]],
                    'terms' => 'This credit note is issued for the amount shown above.',
                    'notes' => 'Seeded seller credit note for '.$invoice->invoice_no.'.',
                ]
            );
            DB::table('seller_credit_note_activities')->where('seller_credit_note_id', $note->id)->delete();
            foreach (['Created', $status] as $step => $action) {
                DB::table('seller_credit_note_activities')->insert(['seller_credit_note_id' => $note->id, 'user_id' => $invoice->created_by, 'actor' => 'Seeder', 'action' => $action, 'changes' => json_encode(['seed' => true]), 'created_at' => $date->copy()->addHours($step), 'updated_at' => $date->copy()->addHours($step)]);
            }
        }
        $this->command?->info('Seeded '.$invoices->count().' seller credit notes.');
    }
}
