<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\InsuranceCompany;
use Illuminate\Database\Seeder;

class InsuranceCompanySeeder extends Seeder
{
    public function run(): void
    {
        $projectId = 2;
        $countryId = Country::query()->where('iso_code', 'US')->value('id')
            ?? Country::query()->value('id');

        if (! $countryId) {
            $this->command?->warn('Insurance companies require at least one country record.');
            return;
        }

        $companies = [
            ['Global Shield Insurance', 'GS-2026-1001', 'Property Insurance', 'Active', 125450.00, '2027-06-15'],
            ['SafeDrive Insurance', 'SD-2026-1002', 'Vehicle Insurance', 'Active', 98760.25, '2027-07-20'],
            ['Premier Protect Insurance', 'PP-2026-1003', 'Property Insurance', 'Active', 210000.00, '2027-08-10'],
            ['Liberty Mutual Plus', 'LM-2026-1004', 'Vehicle Insurance', 'Pending', 75300.00, '2026-10-10'],
            ['Secure Future Insurance', 'SF-2026-1005', 'Other Insurance', 'Active', 64200.00, '2027-09-05'],
            ['Fortress Insurance Group', 'FG-2026-1006', 'Property Insurance', 'Inactive', 0, '2026-03-18'],
            ['RoadSafe Assurance', 'RS-2026-1007', 'Vehicle Insurance', 'Active', 56780.75, '2027-10-12'],
            ['Guardian Plus Insurance', 'GP-2026-1008', 'Other Insurance', 'Pending', 32150.00, '2026-10-20'],
            ['BluePeak Insurance', 'BP-2026-1009', 'Property Insurance', 'Active', 145890.50, '2027-11-30'],
            ['Unity Insurance Services', 'UI-2026-1010', 'Vehicle Insurance', 'Active', 237138.00, '2027-12-18'],
        ];

        foreach ($companies as $index => [$name, $policyNo, $insuranceType, $status, $balance, $expiry]) {
            InsuranceCompany::withTrashed()->updateOrCreate(
                ['project_id' => $projectId, 'company_no' => 'IC-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'company_name' => $name,
                    'company_type' => $index % 3 === 2 ? 'Insurance Broker' : 'Insurance Carrier',
                    'insurance_type' => $insuranceType,
                    'policy_no' => $policyNo,
                    'policy_status' => $status,
                    'balance' => $balance,
                    'expiry_date' => $expiry,
                    'status_active' => $status !== 'Inactive',
                    'agent_broker_name' => ['James Wilson', 'David Brown', 'Olivia Taylor', 'Noah Anderson'][$index % 4],
                    'agent_broker_phone' => '+1 555 210-'.str_pad((string) (1100 + $index), 4, '0', STR_PAD_LEFT),
                    'agent_broker_email' => 'broker'.($index + 1).'@insurance.example',
                    'primary_contact_name' => ['Maria Garcia', 'Sophia Martinez', 'Ethan Clark', 'Emma Thomas'][$index % 4],
                    'primary_contact_phone' => '+1 555 310-'.str_pad((string) (1200 + $index), 4, '0', STR_PAD_LEFT),
                    'primary_contact_email' => 'contact'.($index + 1).'@insurance.example',
                    'street_address' => (100 + $index * 25).' Business Avenue',
                    'city' => ['New York', 'Chicago', 'Boston', 'Seattle'][$index % 4],
                    'state_province' => ['NY', 'IL', 'MA', 'WA'][$index % 4],
                    'postal_code' => ['10006', '60601', '02109', '98101'][$index % 4],
                    'country_id' => $countryId,
                    'notes' => 'Sample insurance company profile for project 2.',
                    'created_by' => null,
                    'updated_by' => null,
                    'deleted_at' => null,
                ]
            );
        }

        $this->command?->info('Seeded insurance company data for project 2.');
    }
}
