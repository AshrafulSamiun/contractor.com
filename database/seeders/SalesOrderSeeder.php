<?php

namespace Database\Seeders;

use App\Models\AccountHolder;
use App\Models\Estimation;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = AccountHolder::where('account_type', 1)->where('is_deleted', false)->where('status_active', true)->orderBy('id')->get();
        if ($customers->isEmpty()) { $this->command?->warn('No active customers found. Run CustomerProfileSeeder first.'); return; }
        foreach ($customers->take(3)->values() as $index => $customer) {
            $items = [
                ['name' => 'PVC Pipe 1/2 inch', 'description' => 'PVC Pipe 1/2 inch Standard', 'uom' => 'FT', 'quantity' => 100, 'unit_price' => 1.20, 'discount' => 0, 'tax_rate' => 8],
                ['name' => 'Labor Installation', 'description' => 'Labor for installation work', 'uom' => 'HRS', 'quantity' => 20, 'unit_price' => 45, 'discount' => $index * 2, 'tax_rate' => 8],
            ];
            $subtotal = collect($items)->sum(fn ($i) => $i['quantity'] * $i['unit_price']);
            $discount = collect($items)->sum(fn ($i) => $i['quantity'] * $i['unit_price'] * $i['discount'] / 100);
            $tax = collect($items)->sum(fn ($i) => ($i['quantity'] * $i['unit_price'] * (1 - $i['discount'] / 100)) * $i['tax_rate'] / 100);
            $date = Carbon::today()->subDays(12 - $index * 3);
            $estimate = Estimation::where('customer_id', $customer->id)->where('status', 2)->first();
            $order = SalesOrder::updateOrCreate(
                ['project_id' => $customer->project_id, 'order_no' => 'SO-SEED-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                ['customer_id' => $customer->id, 'estimation_id' => $estimate?->id, 'created_by' => $customer->inserted_by ?: 1,
                    'order_date' => $date, 'valid_until' => $date->copy()->addDays(15), 'customer_po_no' => 'CPO-SEED-'.($index + 1),
                    'salesperson' => 'Sales Team', 'department' => 'Sales', 'currency_code' => 'CAD', 'exchange_rate' => 1,
                    'price_list' => 'Standard Price List', 'sales_channel' => 'Direct', 'delivery_location' => $customer->address ?: 'Main Warehouse',
                    'shipping_method' => 'Our Truck', 'expected_delivery_date' => $date->copy()->addDays(7), 'shipping_terms' => 'FOB Destination',
                    'payment_terms' => $customer->payment_terms ?: 'Net 15', 'reference' => 'RFQ-SEED-'.($index + 1),
                    'status' => ['Confirmed', 'Pending Approval', 'Draft'][$index],
                    'customer_details' => ['id' => $customer->id, 'customer_no' => $customer->system_no, 'name' => $customer->company_name ?: $customer->account_name, 'contact_person' => $customer->primary_contact_name ?: $customer->account_name, 'phone' => $customer->cell_phone ?: $customer->office_phone, 'email' => $customer->email, 'address' => $customer->address, 'website' => $customer->website, 'tax_number' => $customer->tax_id_no, 'balance' => (float) $customer->current_balance, 'credit_limit' => (float) $customer->credit_limit, 'payment_terms' => $customer->payment_terms],
                    'items' => $items, 'subtotal' => $subtotal, 'discount' => $discount, 'sales_tax' => $tax, 'total' => $subtotal - $discount + $tax,
                    'terms' => 'Prices are subject to change without prior notice. Payment is due within the agreed terms.', 'notes' => 'Seeded sales order. Please deliver during working hours.']
            );
            DB::table('sales_order_activities')->where('sales_order_id', $order->id)->delete();
            foreach (['Created', $order->status] as $step => $action) DB::table('sales_order_activities')->insert(['sales_order_id' => $order->id, 'user_id' => $order->created_by, 'actor' => 'Seeder', 'action' => $action, 'changes' => json_encode(['seed' => true]), 'created_at' => $date->copy()->addHours($step), 'updated_at' => $date->copy()->addHours($step)]);
        }
        $this->command?->info('Seeded '.min(3, $customers->count()).' sales orders.');
    }
}
