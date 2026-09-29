<?php

namespace Database\Seeders;

use App\Models\CustomerCreditNote;
use App\Models\SalesInvoice;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerCreditNoteSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = SalesInvoice::query()
            ->whereIn('status', ['Open', 'Paid'])
            ->whereNotNull('project_id')
            ->with('returns')
            ->orderBy('project_id')
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        $seeded = 0;
        foreach ($invoices as $projectId => $projectInvoices) {
            foreach ($projectInvoices->take(3)->values() as $index => $invoice) {
                $approvedReturns = (float) $invoice->returns->where('status', 'Returned')->sum('total');
                $available = max(0, (float) $invoice->total - $approvedReturns);
                if ($available < 1) continue;

                $taxRate = (float) ($invoice->items[0]['tax_rate'] ?? 5);
                $unitPrice = round(min(150 + ($index * 75), $available / (1 + $taxRate / 100)), 2);
                $subtotal = $unitPrice;
                $tax = round($subtotal * $taxRate / 100, 2);
                $total = round($subtotal + $tax, 2);
                $status = ['Approved', 'Pending', 'Draft'][$index];
                $date = Carbon::today()->subDays(4 - $index);
                $number = 'CCN-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

                DB::transaction(function () use ($invoice, $projectId, $index, $unitPrice, $taxRate, $subtotal, $tax, $total, $status, $date, $number) {
                    $note = CustomerCreditNote::updateOrCreate(
                        ['project_id' => $projectId, 'credit_note_no' => $number],
                        [
                            'sales_invoice_id' => $invoice->id,
                            'created_by' => $invoice->created_by,
                            'credit_note_date' => $date->toDateString(),
                            'status' => $status,
                            'reason' => ['Price Adjustment', 'Additional Discount', 'Billing Error'][$index],
                            'subtotal' => $subtotal,
                            'discount' => 0,
                            'sales_tax' => $tax,
                            'total' => $total,
                            'items' => [[
                                'name' => $index === 0 ? 'Additional Discount' : 'Customer Account Adjustment',
                                'description' => 'Credit adjustment against '.$invoice->invoice_no,
                                'uom' => 'LS',
                                'quantity' => 1,
                                'unit_price' => $unitPrice,
                                'discount' => 0,
                                'tax_rate' => $taxRate,
                            ]],
                            'terms' => "1. This credit note is issued for the stated reason.\n2. The amount will be adjusted against the customer account.\n3. This document is not a cash refund.",
                            'notes' => 'Seeded customer credit note for the sales accounting workflow.',
                        ]
                    );

                    DB::table('customer_credit_note_activities')->updateOrInsert(
                        ['customer_credit_note_id' => $note->id, 'action' => 'Seeded'],
                        ['user_id' => $invoice->created_by, 'actor' => 'System Seeder', 'changes' => json_encode(['status' => $status]), 'created_at' => now(), 'updated_at' => now()]
                    );
                });
                $seeded++;
            }
        }

        if ($seeded === 0) {
            $this->command?->warn('Customer credit notes were not seeded because no eligible sales invoices were found.');
            return;
        }

        $this->command?->info("Seeded {$seeded} customer credit notes across {$invoices->count()} account setup project(s).");
    }
}
