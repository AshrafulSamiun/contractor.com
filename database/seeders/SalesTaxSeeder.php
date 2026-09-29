<?php

namespace Database\Seeders;

use App\Models\SalesTax;
use Illuminate\Database\Seeder;

class SalesTaxSeeder extends Seeder
{
    public function run(): void
    {
        $salesTaxes = [
            // Application Reason 1: General Sales
            ['tax_name' => 'State Sales Tax', 'tax_type' => 1, 'tax_rate' => 6.2500, 'application_reason' => 1, 'notes' => 'Standard state sales tax applied to most goods', 'status_active' => true],
            ['tax_name' => 'Local Sales Tax', 'tax_type' => 2, 'tax_rate' => 2.0000, 'application_reason' => 1, 'notes' => 'Local municipal sales tax', 'status_active' => true],

            // Application Reason 2: Services
            ['tax_name' => 'Service Tax - Labor', 'tax_type' => 3, 'tax_rate' => 5.0000, 'application_reason' => 2, 'notes' => 'Tax on labor/services provided', 'status_active' => true],
            ['tax_name' => 'Service Tax - Consulting', 'tax_type' => 1, 'tax_rate' => 4.5000, 'application_reason' => 2, 'notes' => 'Tax on consulting services', 'status_active' => true],

            // Application Reason 3: Digital Products
            ['tax_name' => 'Digital Goods Tax', 'tax_type' => 4, 'tax_rate' => 7.0000, 'application_reason' => 3, 'notes' => 'Tax on digital goods and downloads', 'status_active' => true],
            ['tax_name' => 'Software License Tax', 'tax_type' => 4, 'tax_rate' => 6.5000, 'application_reason' => 3, 'notes' => 'Tax on software licenses', 'status_active' => true],

            // Application Reason 4: Food & Beverage
            ['tax_name' => 'Restaurant Tax', 'tax_type' => 5, 'tax_rate' => 8.2500, 'application_reason' => 4, 'notes' => 'Tax on prepared foods and beverages', 'status_active' => true],
            ['tax_name' => 'Catering Tax', 'tax_type' => 5, 'tax_rate' => 7.5000, 'application_reason' => 4, 'notes' => 'Tax on catering services', 'status_active' => true],

            // Application Reason 5: Accommodation
            ['tax_name' => 'Hotel Occupancy Tax', 'tax_type' => 2, 'tax_rate' => 10.0000, 'application_reason' => 5, 'notes' => 'Tax on hotel rooms and lodging', 'status_active' => true],
            ['tax_name' => 'Vacation Rental Tax', 'tax_type' => 2, 'tax_rate' => 8.0000, 'application_reason' => 5, 'notes' => 'Tax on vacation rental properties', 'status_active' => true],

            // Application Reason 6: Transportation
            ['tax_name' => 'Fuel Tax', 'tax_type' => 1, 'tax_rate' => 28.5000, 'application_reason' => 6, 'notes' => 'Excise tax on fuel', 'status_active' => true],
            ['tax_name' => 'Vehicle Tax', 'tax_type' => 1, 'tax_rate' => 5.0000, 'application_reason' => 6, 'notes' => 'Tax on vehicle purchases', 'status_active' => true],

            // Application Reason 7: Entertainment
            ['tax_name' => 'Movie Ticket Tax', 'tax_type' => 5, 'tax_rate' => 10.0000, 'application_reason' => 7, 'notes' => 'Tax on movie tickets', 'status_active' => true],
            ['tax_name' => 'Event Ticket Tax', 'tax_type' => 5, 'tax_rate' => 8.5000, 'application_reason' => 7, 'notes' => 'Tax on event tickets', 'status_active' => true],

            // Application Reason 8: Health & Fitness
            ['tax_name' => 'Gym Membership Tax', 'tax_type' => 3, 'tax_rate' => 5.5000, 'application_reason' => 8, 'notes' => 'Tax on gym memberships', 'status_active' => true],
            ['tax_name' => 'Spa Services Tax', 'tax_type' => 3, 'tax_rate' => 6.0000, 'application_reason' => 8, 'notes' => 'Tax on spa and wellness services', 'status_active' => true],

            // Application Reason 9: Professional Services
            ['tax_name' => 'Legal Services Tax', 'tax_type' => 3, 'tax_rate' => 6.2500, 'application_reason' => 9, 'notes' => 'Tax on legal services', 'status_active' => true],
            ['tax_name' => 'Medical Services Tax', 'tax_type' => 3, 'tax_rate' => 4.0000, 'application_reason' => 9, 'notes' => 'Tax on medical services', 'status_active' => true],

            // Application Reason 10: Construction
            ['tax_name' => 'Construction Materials Tax', 'tax_type' => 1, 'tax_rate' => 6.2500, 'application_reason' => 10, 'notes' => 'Tax on construction materials', 'status_active' => true],
            ['tax_name' => 'Contractor Services Tax', 'tax_type' => 3, 'tax_rate' => 7.0000, 'application_reason' => 10, 'notes' => 'Tax on contractor services', 'status_active' => true],
        ];

        $currentYear = date('Y');
        $prefix = 'ST';

        foreach ($salesTaxes as $index => $data) {
            $systemNo = sprintf('%s-%d-%03d', $prefix, $currentYear, $index + 1);

            SalesTax::updateOrCreate(
                ['system_no' => $systemNo],
                array_merge($data, [
                    'project_id' => 1,
                    'system_prefix' => $prefix,
                    'system_no' => $systemNo,
                    'inserted_by' => 0,
                    'updated_by' => 0,
                    'is_deleted' => 0,
                ])
            );
        }
    }
}
