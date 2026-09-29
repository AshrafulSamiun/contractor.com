<?php

namespace Database\Seeders;

use App\Models\PurchaseInvoice;
use App\Models\SellerDebitNote;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SellerDebitNoteSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = PurchaseInvoice::where('status', 'Approved')->orderBy('id')->get();
        if ($invoices->isEmpty()) {
            $this->command?->warn('No approved purchase invoices found. Run PurchaseInvoiceSeeder first.');
            return;
        }

        $reasons = ['Price Difference', 'Wrong Items', 'Quality Issue', 'Damaged Items', 'Short Quantity'];
        foreach ($invoices as $index => $invoice) {
            $source = collect($invoice->items)->first();
            if (! $source) continue;
            $quantity = max(.01, round((float) $source['quantity'] * .1, 2));
            $rate = round((float) $source['cost_rate'] * .5, 2);
            $subtotal = round($quantity * $rate, 2);
            $tax = round((float) ($source['sales_tax'] ?? 0) * .05, 2);
            $total = $subtotal + $tax;
            $date = Carbon::parse($invoice->invoice_date)->addDays(4);
            $status = ['Approved', 'Pending', 'Rejected'][$index % 3];
            $settled = $status === 'Approved' ? ($index % 2 === 0 ? $total : round($total * .5, 2)) : 0;

            $note = SellerDebitNote::updateOrCreate(
                ['project_id' => $invoice->project_id, 'debit_note_no' => 'SDN-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'purchase_invoice_id' => $invoice->id, 'created_by' => $invoice->created_by,
                    'debit_note_date' => $date->toDateString(), 'due_date' => $date->copy()->addDays(14)->toDateString(),
                    'status' => $status, 'settlement_status' => $settled >= $total ? 'Paid' : ($settled > 0 ? 'Partial' : 'Open'),
                    'reason' => $reasons[$index % count($reasons)], 'subtotal' => $subtotal, 'sales_tax' => $tax,
                    'total' => $total, 'settled' => $settled,
                    'items' => [[
                        'invoice_item_index' => 0, 'name' => $source['name'].' adjustment', 'code' => $source['code'] ?? '',
                        'uom' => $source['uom'] ?? 'PCS', 'quantity' => $quantity, 'cost_rate' => $rate, 'sales_tax' => $tax,
                    ]],
                    'notes' => 'Seeded seller debit note for '.$invoice->invoice_no.'.',
                ]
            );

            $note->settlements()->delete();
            if ($settled > 0) {
                $note->settlements()->create([
                    'settlement_no' => 'DSET-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'settlement_date' => $date->copy()->addDays(3)->toDateString(), 'amount' => $settled,
                    'method' => $invoice->payment_method, 'reference' => 'SDN-REF-'.($index + 1), 'notes' => 'Seeded debit note settlement.',
                ]);
            }
            DB::table('seller_debit_note_activities')->where('seller_debit_note_id', $note->id)->delete();
            foreach (['Created', $status] as $step => $action) {
                DB::table('seller_debit_note_activities')->insert(['seller_debit_note_id' => $note->id, 'user_id' => $invoice->created_by, 'actor' => 'Seeder', 'action' => $action, 'changes' => json_encode(['seed' => true]), 'created_at' => $date->copy()->addHours($step), 'updated_at' => $date->copy()->addHours($step)]);
            }
        }
        $this->command?->info('Seeded '.$invoices->count().' seller debit notes.');
    }
}
