<?php

namespace Database\Seeders;

use App\Models\InsurancePolicy;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class InsurancePolicySeeder extends Seeder
{
    public function run(): void
    {
        $vehicleIds = Vehicle::query()
            ->pluck('id', 'vehicle_number')
            ->map(fn ($id) => (int) $id);

        $items = [
            [
                'vehicle_number' => 'ABC-123',
                'insurance_company' => 'ABC Insurance',
                'company_phone' => '01XXXXXXXXX',
                'company_email' => 'info@abcinsurance.com',
                'company_address' => '123 Insurance Street',
                'policy_number' => 'POL123456',
                'coverage_type' => 'Comprehensive',
                'start_date' => '2023-01-01',
                'expiry_date' => '2024-12-31',
                'coverage_amount' => 50000,
                'deductible' => 1000,
                'currency' => 'USD',
                'premium_amount' => 1200,
                'payment_frequency' => 'Annual',
                'calendar_reminder' => true,
                'status' => 1,
                'notes' => 'Primary insurance policy for executive pickup.',
            ],
            [
                'vehicle_number' => 'DEF-789',
                'insurance_company' => 'XYZ Insurance',
                'company_phone' => '01XXXXXXXXX',
                'company_email' => 'contact@xyzinsurance.com',
                'company_address' => '45 Policy Avenue',
                'policy_number' => 'POL987654',
                'coverage_type' => 'Third-Party',
                'start_date' => '2022-06-01',
                'expiry_date' => '2023-05-31',
                'coverage_amount' => 30000,
                'deductible' => 750,
                'currency' => 'USD',
                'premium_amount' => 900,
                'payment_frequency' => 'Annual',
                'calendar_reminder' => false,
                'status' => 2,
                'notes' => 'Expired policy waiting for renewal.',
            ],
            [
                'vehicle_number' => 'XYZ-456',
                'insurance_company' => 'LMN Insurance',
                'company_phone' => '01XXXXXXXXX',
                'company_email' => 'support@lmninsurance.com',
                'company_address' => '88 Cover Road',
                'policy_number' => 'POL564738',
                'coverage_type' => 'Comprehensive',
                'start_date' => '2023-03-15',
                'expiry_date' => '2024-03-14',
                'coverage_amount' => 45000,
                'deductible' => 900,
                'currency' => 'USD',
                'premium_amount' => 1100,
                'payment_frequency' => 'Quarterly',
                'calendar_reminder' => true,
                'status' => 1,
                'notes' => 'Quarterly payment plan.',
            ],
            [
                'vehicle_number' => 'GHI-321',
                'insurance_company' => 'Delta Insurance',
                'company_phone' => '01XXXXXXXXX',
                'company_email' => 'hello@deltainsurance.com',
                'company_address' => '9 Shield Plaza',
                'policy_number' => 'POL112233',
                'coverage_type' => 'Collision',
                'start_date' => '2025-01-10',
                'expiry_date' => '2026-01-10',
                'coverage_amount' => 65000,
                'deductible' => 1500,
                'currency' => 'USD',
                'premium_amount' => 1350,
                'payment_frequency' => 'Monthly',
                'calendar_reminder' => true,
                'status' => 3,
                'notes' => 'Pending verification from insurer.',
            ],
            [
                'vehicle_number' => 'STU-222',
                'insurance_company' => 'Prime Fleet Cover',
                'company_phone' => '01XXXXXXXXX',
                'company_email' => 'fleet@primecover.com',
                'company_address' => '77 Fleet House',
                'policy_number' => 'POL667788',
                'coverage_type' => 'Liability',
                'start_date' => '2024-02-01',
                'expiry_date' => '2025-01-31',
                'coverage_amount' => 55000,
                'deductible' => 1200,
                'currency' => 'USD',
                'premium_amount' => 1285,
                'payment_frequency' => 'Semi-Annual',
                'calendar_reminder' => true,
                'status' => 4,
                'notes' => 'Cancelled after policy transfer.',
            ],
        ];

        $currentYear = date('Y');

        foreach ($items as $index => $data) {
            $insuranceCode = sprintf('INS-%s-%03d', $currentYear, $index + 1);
            $vehicleId = $vehicleIds->get($data['vehicle_number']);

            if (! $vehicleId) {
                continue;
            }

            InsurancePolicy::updateOrCreate(
                ['policy_number' => $data['policy_number']],
                [
                    'project_id' => 1,
                    'insurance_code' => $insuranceCode,
                    'vehicle_id' => $vehicleId,
                    'insurance_company' => $data['insurance_company'],
                    'company_phone' => $data['company_phone'],
                    'company_email' => $data['company_email'],
                    'company_address' => $data['company_address'],
                    'policy_number' => $data['policy_number'],
                    'coverage_type' => $data['coverage_type'],
                    'start_date' => $data['start_date'],
                    'expiry_date' => $data['expiry_date'],
                    'coverage_amount' => $data['coverage_amount'],
                    'deductible' => $data['deductible'],
                    'currency' => $data['currency'],
                    'premium_amount' => $data['premium_amount'],
                    'payment_frequency' => $data['payment_frequency'],
                    'calendar_reminder' => $data['calendar_reminder'],
                    'status' => $data['status'],
                    'notes' => $data['notes'],
                    'inserted_by' => 0,
                    'updated_by' => 0,
                    'is_deleted' => 0,
                ]
            );
        }
    }
}
