<?php

namespace Database\Seeders;

use App\Models\BillPayment;
use App\Models\PurchaseInvoice;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = PurchaseInvoice::where('status', 'Approved')->whereColumn('paid', '<', 'total')->orderBy('id')->get();
        if ($invoices->isEmpty()) {
            $this->command?->warn('No approved unpaid purchase invoices found. Run PurchaseInvoiceSeeder first.');
            return;
        }
        foreach ($invoices as $index => $invoice) {
            $balance = round((float) $invoice->total - (float) $invoice->paid, 2);
            $amount = round(min($balance, max(50, $balance * .25)), 2);
            $date = Carbon::parse($invoice->invoice_date)->addDays(7);
            $payment = BillPayment::updateOrCreate(
                ['project_id' => $invoice->project_id, 'payment_no' => 'BP-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'seller_id' => $invoice->seller_id, 'created_by' => $invoice->created_by,
                    'payment_date' => $date->toDateString(), 'status' => 'Draft',
                    'payment_method' => $invoice->payment_method ?: 'Bank Transfer', 'bank_account' => 'Operating Account',
                    'reference_no' => 'PAY-SEED-'.($index + 1), 'currency_code' => $invoice->currency_code,
                    'amount' => $amount, 'write_off' => 0, 'take_discount' => false, 'group_by_invoice' => false,
                    'memo' => 'Payment for '.$invoice->invoice_no, 'notes' => 'Seeded draft bill payment.', 'posted_at' => null,
                ]
            );
            $payment->allocations()->delete();
            $payment->allocations()->create(['purchase_invoice_id' => $invoice->id, 'amount' => $amount]);
            DB::table('bill_payment_activities')->where('bill_payment_id', $payment->id)->delete();
            DB::table('bill_payment_activities')->insert(['bill_payment_id' => $payment->id, 'user_id' => $invoice->created_by, 'actor' => 'Seeder', 'action' => 'Created', 'changes' => json_encode(['seed' => true]), 'created_at' => $date, 'updated_at' => $date]);
        }
        $this->command?->info('Seeded '.$invoices->count().' draft bill payments.');
    }
}
