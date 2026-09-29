<?php

namespace Database\Seeders;

use App\Models\SalesInvoice;
use App\Models\SalesReturn;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesReturnSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = SalesInvoice::query()
            ->whereIn('status', ['Open', 'Paid'])
            ->whereNotNull('project_id')
            ->orderBy('project_id')
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        $seeded = 0;
        foreach ($invoices as $projectId => $projectInvoices) {
            foreach ($projectInvoices->take(4)->values() as $index => $invoice) {
                $items = collect($invoice->items ?: [])->take($index === 0 ? 2 : 1)->map(function ($item, $itemIndex) {
                    return [
                        'invoice_item_index' => $itemIndex,
                        'name' => $item['name'],
                        'description' => $item['description'] ?? '',
                        'uom' => $item['uom'] ?? 'PCS',
                        'quantity' => min(1, (float) $item['quantity']),
                        'unit_price' => (float) $item['unit_price'],
                        'discount' => (float) ($item['discount'] ?? 0),
                        'tax_rate' => (float) ($item['tax_rate'] ?? 0),
                    ];
                })->values();

                if ($items->isEmpty()) continue;

                $subtotal = round($items->sum(fn ($item) => $item['quantity'] * $item['unit_price']), 2);
                $discount = round($items->sum(fn ($item) => $item['quantity'] * $item['unit_price'] * $item['discount'] / 100), 2);
                $tax = round($items->sum(fn ($item) => $item['quantity'] * $item['unit_price'] * (1 - $item['discount'] / 100) * $item['tax_rate'] / 100), 2);
                $total = round($subtotal - $discount + $tax, 2);
                $status = ['Returned', 'Pending', 'Draft', 'Rejected'][$index];
                $refunded = $index === 0 ? round($total / 2, 2) : 0;
                $date = Carbon::today()->subDays(7 - $index);
                $returnNo = 'SIR-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

                DB::transaction(function () use ($invoice, $projectId, $items, $subtotal, $discount, $tax, $total, $status, $refunded, $date, $returnNo, $index) {
                    $return = SalesReturn::updateOrCreate(
                        ['project_id' => $projectId, 'return_no' => $returnNo],
                        [
                            'sales_invoice_id' => $invoice->id,
                            'created_by' => $invoice->created_by,
                            'return_date' => $date->toDateString(),
                            'expected_pickup_date' => $date->copy()->addDays(3)->toDateString(),
                            'status' => $status,
                            'refund_status' => $refunded > 0 ? 'Partial' : 'Unpaid',
                            'reason' => ['Damaged items', 'Wrong item supplied', 'Incorrect size', 'Customer cancellation'][$index],
                            'return_type' => 'Goods Return',
                            'warehouse' => $index % 2 === 0 ? 'Main Warehouse' : 'Returns Warehouse',
                            'shipping_method' => $invoice->shipping_method ?: 'Our Truck',
                            'reference' => 'RET-SEED-REF-'.($index + 1),
                            'subtotal' => $subtotal,
                            'discount' => $discount,
                            'sales_tax' => $tax,
                            'total' => $total,
                            'refunded' => $refunded,
                            'items' => $items->all(),
                            'terms' => "1. Return accepted within 7 days of delivery.\n2. Goods must include original packaging.\n3. Refunds are processed after inspection.",
                            'notes' => 'Seeded sales return for testing the customer return workflow.',
                        ]
                    );

                    DB::table('sales_return_activities')->updateOrInsert(
                        ['sales_return_id' => $return->id, 'action' => 'Seeded'],
                        ['user_id' => $invoice->created_by, 'actor' => 'System Seeder', 'changes' => json_encode(['status' => $status]), 'created_at' => now(), 'updated_at' => now()]
                    );

                    if ($refunded > 0) {
                        $return->refunds()->updateOrCreate(
                            ['refund_no' => 'CRF-SEED-0001'],
                            ['refund_date' => $date->copy()->addDays(2)->toDateString(), 'amount' => $refunded, 'method' => 'Bank Transfer', 'reference' => 'SEED-CREDIT-1', 'notes' => 'Partial customer refund.']
                        );
                    }
                });
                $seeded++;
            }
        }

        if ($seeded === 0) {
            $this->command?->warn('Sales returns were not seeded because no eligible sales invoices were found.');
            return;
        }

        $this->command?->info("Seeded {$seeded} sales returns across {$invoices->count()} account setup project(s).");
    }
}
