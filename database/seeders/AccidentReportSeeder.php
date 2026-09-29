<?php

namespace Database\Seeders;

use App\Models\AccidentReport;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class AccidentReportSeeder extends Seeder
{
    public function run(): void
    {
        $vehicleIds = Vehicle::query()
            ->pluck('id', 'vehicle_number')
            ->map(fn ($id) => (int) $id);

        $driverIds = Driver::query()
            ->pluck('id', 'driver_name')
            ->map(fn ($id) => (int) $id);

        $items = [
            [
                'report_code' => 'ACC-2025-001',
                'report_date' => '2025-01-12',
                'created_by_name' => 'John Doe',
                'incident_report_no' => 'INC-001',
                'incident_report_name' => 'Minor Collision at Gate',
                'accident_reference' => 'ACCID-4088',
                'incident_date' => '2025-01-12',
                'incident_time' => '10:15',
                'location' => 'Dhaka',
                'accident_type' => 'Collision',
                'description' => 'Front bumper damaged during gate entry.',
                'vehicle_number' => 'ABC-123',
                'driver_name' => 'John Doe',
                'at_fault_name' => null,
                'at_fault_position' => 'Unknown',
                'repair_shop' => 'Auto Repair Center',
                'invoice_no' => 'INV-001',
                'invoice_date' => '2025-01-15',
                'subtotal_repair_cost' => 2212.39,
                'sales_tax_rate' => 13,
                'payment_method' => 'Insurance',
                'is_paid' => false,
                'paid_by' => 'Company/Insurance',
                'amount_paid_by_company' => 1500,
                'amount_paid_by_insurance' => 1000,
                'status' => 1,
                'notes' => 'Awaiting final insurance confirmation.',
            ],
            [
                'report_code' => 'ACC-2025-002',
                'report_date' => '2025-02-22',
                'created_by_name' => 'John Doe',
                'incident_report_no' => 'INC-002',
                'incident_report_name' => 'Engine Trouble on Route',
                'accident_reference' => 'ACCID-4092',
                'incident_date' => '2025-02-22',
                'incident_time' => '08:40',
                'location' => 'Gazipur',
                'accident_type' => 'Breakdown',
                'description' => 'Vehicle stalled due to engine overheating.',
                'vehicle_number' => 'DEF-789',
                'driver_name' => 'Jane Smith',
                'at_fault_name' => null,
                'at_fault_position' => 'Unknown',
                'repair_shop' => 'Highway Garage',
                'invoice_no' => 'INV-002',
                'invoice_date' => '2025-02-23',
                'subtotal_repair_cost' => 796.46,
                'sales_tax_rate' => 13,
                'payment_method' => 'Cash',
                'is_paid' => true,
                'paid_by' => 'Company',
                'amount_paid_by_company' => 900,
                'amount_paid_by_insurance' => 0,
                'status' => 2,
                'notes' => 'Repair completed and vehicle returned to service.',
            ],
            [
                'report_code' => 'ACC-2025-003',
                'report_date' => '2025-03-05',
                'created_by_name' => 'John Doe',
                'incident_report_no' => 'INC-003',
                'incident_report_name' => 'Parking Lot Scratch',
                'accident_reference' => 'ACCID-4101',
                'incident_date' => '2025-03-05',
                'incident_time' => '14:00',
                'location' => 'Chittagong',
                'accident_type' => 'Minor Accident',
                'description' => 'Side panel scratched while reversing.',
                'vehicle_number' => 'XYZ-456',
                'driver_name' => 'Sarah Johnson',
                'at_fault_name' => 'Unknown Driver',
                'at_fault_position' => 'Third Party',
                'repair_shop' => 'Coastal Auto Works',
                'invoice_no' => 'INV-003',
                'invoice_date' => '2025-03-06',
                'subtotal_repair_cost' => 398.23,
                'sales_tax_rate' => 13,
                'payment_method' => 'Card',
                'is_paid' => false,
                'paid_by' => 'Insurance',
                'amount_paid_by_company' => 0,
                'amount_paid_by_insurance' => 450,
                'status' => 3,
                'notes' => 'Pending approval from insurer.',
            ],
            [
                'report_code' => 'ACC-2025-004',
                'report_date' => '2025-03-15',
                'created_by_name' => 'John Doe',
                'incident_report_no' => 'INC-004',
                'incident_report_name' => 'Mirror Damage During Loading',
                'accident_reference' => 'ACCID-4110',
                'incident_date' => '2025-03-15',
                'incident_time' => '16:20',
                'location' => 'Sylhet',
                'accident_type' => 'Other',
                'description' => 'Side mirror damaged in a loading zone.',
                'vehicle_number' => 'ABC-123',
                'driver_name' => 'John Doe',
                'at_fault_name' => null,
                'at_fault_position' => 'Company',
                'repair_shop' => 'Metro Repairs',
                'invoice_no' => 'INV-004',
                'invoice_date' => '2025-03-16',
                'subtotal_repair_cost' => 1061.95,
                'sales_tax_rate' => 13,
                'payment_method' => 'Bank Transfer',
                'is_paid' => false,
                'paid_by' => 'Company',
                'amount_paid_by_company' => 1200,
                'amount_paid_by_insurance' => 0,
                'status' => 1,
                'notes' => 'Replacement part ordered.',
            ],
        ];

        foreach ($items as $data) {
            $vehicleId = $vehicleIds->get($data['vehicle_number']);

            if (! $vehicleId) {
                continue;
            }

            $driverId = $driverIds->get($data['driver_name']);
            $salesTaxAmount = round(($data['subtotal_repair_cost'] * $data['sales_tax_rate']) / 100, 2);
            $totalCost = round($data['subtotal_repair_cost'] + $salesTaxAmount, 2);

            AccidentReport::updateOrCreate(
                ['report_code' => $data['report_code']],
                [
                    'project_id' => 1,
                    'report_code' => $data['report_code'],
                    'report_date' => $data['report_date'],
                    'created_by_name' => $data['created_by_name'],
                    'incident_report_no' => $data['incident_report_no'],
                    'incident_report_name' => $data['incident_report_name'],
                    'accident_reference' => $data['accident_reference'],
                    'incident_date' => $data['incident_date'],
                    'incident_time' => $data['incident_time'],
                    'location' => $data['location'],
                    'accident_type' => $data['accident_type'],
                    'description' => $data['description'],
                    'vehicle_id' => $vehicleId,
                    'driver_id' => $driverId,
                    'at_fault_name' => $data['at_fault_name'],
                    'at_fault_position' => $data['at_fault_position'],
                    'repair_shop' => $data['repair_shop'],
                    'invoice_no' => $data['invoice_no'],
                    'invoice_date' => $data['invoice_date'],
                    'subtotal_repair_cost' => $data['subtotal_repair_cost'],
                    'sales_tax_rate' => $data['sales_tax_rate'],
                    'sales_tax_amount' => $salesTaxAmount,
                    'total_cost' => $totalCost,
                    'payment_method' => $data['payment_method'],
                    'is_paid' => $data['is_paid'],
                    'paid_by' => $data['paid_by'],
                    'amount_paid_by_company' => $data['amount_paid_by_company'],
                    'amount_paid_by_insurance' => $data['amount_paid_by_insurance'],
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
