<?php

namespace Database\Seeders;

use App\Models\AccountSetup;
use App\Models\ServiceItem;
use Illuminate\Database\Seeder;

class ServiceItemSeeder extends Seeder
{
    public function run(): void
    {
        $projectIds = AccountSetup::query()
            ->join('users', 'users.id', '=', 'account_setups.user_id')
            ->where('users.is_active', true)
            ->where('users.role', '!=', 'super_admin')
            ->pluck('account_setups.id');

        $items = [
            ['Electrical Installation', 'HRS', 75.00, true, 'Electrical installation and wiring labor.'],
            ['Plumbing Installation', 'HRS', 65.00, true, 'Plumbing installation and repair labor.'],
            ['HVAC Maintenance', 'HRS', 95.00, true, 'Scheduled HVAC inspection and maintenance.'],
            ['Interior Painting', 'SQFT', 2.75, true, 'Interior wall preparation and painting.'],
            ['Flooring Installation', 'SQFT', 6.50, true, 'Flooring preparation and installation.'],
            ['General Labor', 'HRS', 45.00, true, 'General site labor and assistance.'],
            ['Equipment Rental', 'DAY', 180.00, true, 'Daily construction equipment rental.'],
            ['Site Inspection', 'EACH', 250.00, true, 'On-site inspection and written findings.'],
        ];

        foreach ($projectIds as $projectId) {
            foreach ($items as $index => [$name, $unit, $price, $taxable, $note]) {
                ServiceItem::withoutGlobalScopes()->updateOrCreate(
                    ['project_id' => $projectId, 'item_no' => 'SER-'.$projectId.'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                    ['item_name' => $name, 'unit_of_measure' => $unit, 'price' => $price, 'sales_tax_applicable' => $taxable, 'status' => 1, 'note' => $note, 'inserted_by' => 0, 'updated_by' => 0, 'is_deleted' => false]
                );
            }
        }

        $this->command?->info(sprintf('Seeded %d service items for %d account setup project(s).', count($items), $projectIds->count()));
    }
}
