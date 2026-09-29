<?php

namespace Database\Seeders;

use App\Models\AccountHolder;
use App\Models\AccountSetup;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $projects = AccountSetup::query()
            ->join('users', 'users.id', '=', 'account_setups.user_id')
            ->where('users.is_active', true)
            ->where('users.role', '!=', 'super_admin')
            ->get(['account_setups.id as project_id', 'account_setups.user_id as owner_user_id']);

        $seeded = 0;
        foreach ($projects as $project) {
            $orders = SalesOrder::where('project_id', $project->project_id)
                ->whereIn('status', ['Confirmed', 'Completed'])
                ->orderBy('id')->take(3)->get();
            $customers = AccountHolder::where('project_id', $project->project_id)
                ->where('account_type', 1)->where('is_deleted', false)
                ->where('status_active', true)->orderBy('id')->take(3)->get();

            if ($customers->isEmpty()) {
                continue;
            }

            for ($index = 0; $index < 3; $index++) {
                $order = $orders->get($index);
                $customer = $order
                    ? AccountHolder::where('project_id', $project->project_id)->find($order->customer_id)
                    : $customers->get($index % $customers->count());
                if (!$customer) {
                    continue;
                }

                $items = $order?->items ?: [
                    ['name' => 'Construction Materials', 'description' => 'Materials supplied for site work', 'uom' => 'LS', 'quantity' => 1, 'unit_price' => 850 + $index * 250, 'discount' => 0, 'tax_rate' => 5],
                    ['name' => 'Installation Labor', 'description' => 'On-site installation service', 'uom' => 'HRS', 'quantity' => 8, 'unit_price' => 45, 'discount' => 0, 'tax_rate' => 5],
                ];
                $subtotal = round(collect($items)->sum(fn ($item) => $item['quantity'] * $item['unit_price']), 2);
                $discount = round(collect($items)->sum(fn ($item) => $item['quantity'] * $item['unit_price'] * ($item['discount'] ?? 0) / 100), 2);
                $tax = round(collect($items)->sum(fn ($item) => $item['quantity'] * $item['unit_price'] * (1 - ($item['discount'] ?? 0) / 100) * ($item['tax_rate'] ?? 0) / 100), 2);
                $total = round($subtotal - $discount + $tax, 2);
                $paid = $index === 0 ? 0 : ($index === 1 ? round($total / 2, 2) : $total);
                $date = Carbon::today()->subDays(12 - $index * 3);
                $invoiceNo = 'SINV-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

                DB::transaction(function () use ($project, $order, $customer, $items, $subtotal, $discount, $tax, $total, $paid, $date, $invoiceNo, $index) {
                    $invoice = SalesInvoice::updateOrCreate(
                        ['project_id' => $project->project_id, 'invoice_no' => $invoiceNo],
                        [
                            'sales_order_id' => $order?->id,
                            'customer_id' => $customer->id,
                            'created_by' => $project->owner_user_id,
                            'invoice_date' => $date->toDateString(),
                            'due_date' => $date->copy()->addDays(30)->toDateString(),
                            'delivery_date' => $date->copy()->addDays(4)->toDateString(),
                            'customer_po_no' => $order?->customer_po_no ?: 'CPO-SEED-'.($index + 1),
                            'salesperson' => $order?->salesperson ?: 'Sales Team',
                            'department' => $order?->department ?: 'Sales',
                            'currency_code' => $order?->currency_code ?: 'USD',
                            'exchange_rate' => $order?->exchange_rate ?: 1,
                            'price_list' => $order?->price_list ?: 'Standard Price List',
                            'sales_channel' => $order?->sales_channel ?: 'Direct',
                            'delivery_location' => $order?->delivery_location ?: ($customer->address ?: 'Main Warehouse'),
                            'shipping_method' => $order?->shipping_method ?: 'Our Truck',
                            'tracking_no' => 'TRK-SEED-'.($index + 1),
                            'shipping_terms' => $order?->shipping_terms ?: 'FOB Destination',
                            'payment_terms' => $order?->payment_terms ?: 'Net 30',
                            'reference' => 'INV-SEED-REF-'.($index + 1),
                            'status' => 'Open',
                            'payment_status' => $paid >= $total ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid'),
                            'subtotal' => $subtotal,
                            'discount' => $discount,
                            'sales_tax' => $tax,
                            'total' => $total,
                            'paid' => $paid,
                            'customer_details' => [
                                'id' => $customer->id,
                                'customer_no' => $customer->system_no,
                                'name' => $customer->company_name ?: $customer->account_name,
                                'contact_person' => $customer->primary_contact_name ?: $customer->account_name,
                                'phone' => $customer->cell_phone ?: $customer->office_phone,
                                'email' => $customer->email,
                                'address' => $customer->address,
                                'website' => $customer->website,
                                'balance' => (float) $customer->current_balance,
                                'credit_limit' => (float) $customer->credit_limit,
                            ],
                            'items' => $items,
                            'terms' => $order?->terms ?: 'Payment is due within 30 days of the invoice date.',
                            'notes' => 'Sample sales invoice for the accounting workflow.',
                            'payment_information' => 'Bank transfer or approved customer payment method.',
                            'job_site_name' => $order?->delivery_location ?: 'Construction Site',
                            'job_site_address' => $order?->delivery_location ?: ($customer->address ?: ''),
                            'approved_by' => 'Sales Manager',
                        ]
                    );

                    if ($paid > 0) {
                        $invoice->payments()->updateOrCreate(
                            ['payment_no' => 'CPAY-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                            ['payment_date' => $date->copy()->addDays(5)->toDateString(), 'amount' => $paid, 'method' => 'Bank Transfer', 'reference' => 'SEED-PAY-'.($index + 1)]
                        );
                    }
                });
                $seeded++;
            }
        }

        $this->command?->info("Seeded {$seeded} sales invoices across {$projects->count()} account setup project(s).");
    }
}
