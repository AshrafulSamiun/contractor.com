<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['item_name' => 'Standard Packing Box - Small', 'unit_of_measure' => 'Each', 'price' => 2.50, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Small cardboard box for light items'],
            ['item_name' => 'Standard Packing Box - Medium', 'unit_of_measure' => 'Each', 'price' => 4.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Medium cardboard box for medium items'],
            ['item_name' => 'Standard Packing Box - Large', 'unit_of_measure' => 'Each', 'price' => 6.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Large cardboard box for bulky items'],
            ['item_name' => 'Bubble Wrap Roll', 'unit_of_measure' => 'Roll', 'price' => 15.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Protective bubble wrap for fragile items'],
            ['item_name' => 'Packing Tape', 'unit_of_measure' => 'Roll', 'price' => 3.50, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Clear packing tape for sealing boxes'],
            ['item_name' => 'Stretch Wrap', 'unit_of_measure' => 'Roll', 'price' => 12.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Plastic stretch wrap for pallet wrapping'],
            ['item_name' => 'Foam Peanuts', 'unit_of_measure' => 'Bag', 'price' => 8.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Loose fill foam peanuts'],
            ['item_name' => 'Kraft Paper', 'unit_of_measure' => 'Roll', 'price' => 10.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Kraft paper for wrapping and cushioning'],
            ['item_name' => 'Packing Labels', 'unit_of_measure' => 'Sheet', 'price' => 5.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => ' adhesive labels for marking'],
            ['item_name' => 'Fragile Stickers', 'unit_of_measure' => 'Roll', 'price' => 4.50, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Fragile warning stickers'],
            ['item_name' => 'Shipping Envelope - Small', 'unit_of_measure' => 'Each', 'price' => 1.25, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Small padded envelope'],
            ['item_name' => 'Shipping Envelope - Medium', 'unit_of_measure' => 'Each', 'price' => 2.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Medium padded envelope'],
            ['item_name' => 'Shipping Envelope - Large', 'unit_of_measure' => 'Each', 'price' => 3.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Large padded envelope'],
            ['item_name' => 'Document Mailer', 'unit_of_measure' => 'Each', 'price' => 0.75, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Plain document mailer envelope'],
            ['item_name' => 'Plastic Bag - Small', 'unit_of_measure' => 'Pack', 'price' => 6.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => '100 pack small plastic bags'],
            ['item_name' => 'Plastic Bag - Medium', 'unit_of_measure' => 'Pack', 'price' => 8.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => '100 pack medium plastic bags'],
            ['item_name' => 'Plastic Bag - Large', 'unit_of_measure' => 'Pack', 'price' => 12.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => '100 pack large plastic bags'],
            ['item_name' => 'Corrugated Wrap', 'unit_of_measure' => 'Sheet', 'price' => 0.50, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Single sheet corrugated wrap'],
            ['item_name' => 'Edge Protectors', 'unit_of_measure' => 'Bundle', 'price' => 18.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Cardboard edge protectors'],
            ['item_name' => 'Newsprint Paper', 'unit_of_measure' => 'Ream', 'price' => 14.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Newsprint wrapping paper'],
            ['item_name' => 'Void Fill Paper', 'unit_of_measure' => 'Roll', 'price' => 16.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Crumpled paper void fill'],
            ['item_name' => 'Pallet Box', 'unit_of_measure' => 'Each', 'price' => 45.00, 'sales_tax_applicable' => true, 'status' => 1, 'note' => 'Large pallet sized box'],
            ['item_name' => 'Shipping Calculator', 'unit_of_measure' => 'Each', 'price' => 99.00, 'sales_tax_applicable' => false, 'status' => 2, 'note' => 'Digital shipping scale calculator'],
            ['item_name' => 'Label Printer', 'unit_of_measure' => 'Each', 'price' => 250.00, 'sales_tax_applicable' => false, 'status' => 1, 'note' => 'Thermal label printer'],
            ['item_name' => 'Barcode Scanner', 'unit_of_measure' => 'Each', 'price' => 175.00, 'sales_tax_applicable' => false, 'status' => 1, 'note' => 'Handheld barcode scanner'],
        ];

        $currentYear = date('Y');
        $prefix = 'INV';

        foreach ($items as $index => $data) {
            $itemNo = sprintf('%s-%d-%04d', $prefix, $currentYear, $index + 1);

            InventoryItem::updateOrCreate(
                ['item_no' => $itemNo],
                array_merge($data, [
                    'project_id' => 1,
                    'item_no' => $itemNo,
                    'inserted_by' => 0,
                    'updated_by' => 0,
                    'is_deleted' => 0,
                ])
            );
        }
    }
}
