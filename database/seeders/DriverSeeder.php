<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $vehicleIds = Vehicle::query()
            ->pluck('id', 'vehicle_number')
            ->map(fn ($id) => (int) $id);

        $items = [
            [
                'driver_name' => 'John Doe',
                'contact_number' => '123-456-7890',
                'email' => 'john.doe@example.com',
                'date_of_birth' => '1989-04-12',
                'address' => '123 Main Street, City, State, ZIP',
                'emergency_contact_name' => 'Jane Doe',
                'emergency_contact_number' => '098-765-4321',
                'license_number' => 'D1234567',
                'license_expiry_date' => '2025-12-08',
                'license_class' => 'Class A',
                'assigned_vehicle_numbers' => ['ABC-123'],
                'hire_date' => '2023-01-15',
                'employment_type' => 'Full Time',
                'hourly_rate' => 25,
                'status' => 1,
                'notes' => 'Lead site driver for primary dispatch vehicle.',
            ],
            [
                'driver_name' => 'Jane Smith',
                'contact_number' => '234-567-8901',
                'email' => 'jane.smith@example.com',
                'date_of_birth' => '1991-09-20',
                'address' => '221 Lake Road, City, State, ZIP',
                'emergency_contact_name' => 'Tom Smith',
                'emergency_contact_number' => '090-111-2233',
                'license_number' => 'S9876543',
                'license_expiry_date' => '2024-11-30',
                'license_class' => 'Class B',
                'assigned_vehicle_numbers' => ['DEF-789'],
                'hire_date' => '2022-08-10',
                'employment_type' => 'Full Time',
                'hourly_rate' => 24,
                'status' => 1,
                'notes' => 'Assigned to supervisor support fleet.',
            ],
            [
                'driver_name' => 'Michael Lee',
                'contact_number' => '345-678-9012',
                'email' => 'michael.lee@example.com',
                'date_of_birth' => '1987-06-03',
                'address' => '18 Garden Avenue, City, State, ZIP',
                'emergency_contact_name' => 'Susan Lee',
                'emergency_contact_number' => '091-222-3344',
                'license_number' => 'L5647382',
                'license_expiry_date' => '2026-02-15',
                'license_class' => 'Class C',
                'assigned_vehicle_numbers' => [],
                'hire_date' => '2021-06-18',
                'employment_type' => 'Contract',
                'hourly_rate' => 21.5,
                'status' => 2,
                'notes' => 'Currently off rotation.',
            ],
            [
                'driver_name' => 'Sarah Khan',
                'contact_number' => '456-789-0123',
                'email' => 'sarah.khan@example.com',
                'date_of_birth' => '1993-12-15',
                'address' => '55 Station Road, City, State, ZIP',
                'emergency_contact_name' => 'Ahmed Khan',
                'emergency_contact_number' => '092-333-4455',
                'license_number' => 'K1122334',
                'license_expiry_date' => '2025-05-20',
                'license_class' => 'Class A',
                'assigned_vehicle_numbers' => ['XYZ-456'],
                'hire_date' => '2024-02-01',
                'employment_type' => 'Part Time',
                'hourly_rate' => 22,
                'status' => 1,
                'notes' => 'Part-time logistics and transport support.',
            ],
            [
                'driver_name' => 'Robert Wilson',
                'contact_number' => '567-890-1234',
                'email' => 'robert.wilson@example.com',
                'date_of_birth' => '1984-02-27',
                'address' => '79 Market Street, City, State, ZIP',
                'emergency_contact_name' => 'Anna Wilson',
                'emergency_contact_number' => '093-444-5566',
                'license_number' => 'W7788990',
                'license_expiry_date' => '2024-09-12',
                'license_class' => 'Class B',
                'assigned_vehicle_numbers' => ['GHI-321', 'JKL-654'],
                'hire_date' => '2020-10-25',
                'employment_type' => 'Full Time',
                'hourly_rate' => 27,
                'status' => 3,
                'notes' => 'Suspended pending compliance review.',
            ],
            [
                'driver_name' => 'Emily Carter',
                'contact_number' => '678-901-2345',
                'email' => 'emily.carter@example.com',
                'date_of_birth' => '1995-01-17',
                'address' => '12 River View, City, State, ZIP',
                'emergency_contact_name' => 'Chris Carter',
                'emergency_contact_number' => '094-555-6677',
                'license_number' => 'E4455667',
                'license_expiry_date' => '2026-07-03',
                'license_class' => 'Class C',
                'assigned_vehicle_numbers' => ['MNO-987'],
                'hire_date' => '2023-05-08',
                'employment_type' => 'Full Time',
                'hourly_rate' => 23.75,
                'status' => 1,
                'notes' => 'Regional support driver.',
            ],
            [
                'driver_name' => 'Daniel Brooks',
                'contact_number' => '789-012-3456',
                'email' => 'daniel.brooks@example.com',
                'date_of_birth' => '1988-11-09',
                'address' => '88 North Lane, City, State, ZIP',
                'emergency_contact_name' => 'Laura Brooks',
                'emergency_contact_number' => '095-666-7788',
                'license_number' => 'D9988776',
                'license_expiry_date' => '2027-03-18',
                'license_class' => 'Class D',
                'assigned_vehicle_numbers' => ['STU-222', 'VWX-333'],
                'hire_date' => '2019-07-19',
                'employment_type' => 'Full Time',
                'hourly_rate' => 26.5,
                'status' => 1,
                'notes' => 'Manages van rotation for large crews.',
            ],
            [
                'driver_name' => 'Olivia Reed',
                'contact_number' => '890-123-4567',
                'email' => 'olivia.reed@example.com',
                'date_of_birth' => '1990-08-24',
                'address' => '42 Elm Street, City, State, ZIP',
                'emergency_contact_name' => 'Henry Reed',
                'emergency_contact_number' => '096-777-8899',
                'license_number' => 'O2233445',
                'license_expiry_date' => '2026-10-22',
                'license_class' => 'Class B',
                'assigned_vehicle_numbers' => ['YZA-444'],
                'hire_date' => '2022-04-12',
                'employment_type' => 'Contract',
                'hourly_rate' => 24.25,
                'status' => 1,
                'notes' => 'Contract driver for warehouse distribution.',
            ],
        ];

        $currentYear = date('Y');

        foreach ($items as $index => $data) {
            $driverCode = sprintf('DRV-%s-%03d', $currentYear, $index + 1);
            $assignedVehicleIds = collect($data['assigned_vehicle_numbers'])
                ->map(fn ($number) => $vehicleIds->get($number))
                ->filter()
                ->values()
                ->all();

            unset($data['assigned_vehicle_numbers']);

            Driver::updateOrCreate(
                ['license_number' => $data['license_number']],
                array_merge($data, [
                    'project_id' => 1,
                    'driver_code' => $driverCode,
                    'assigned_vehicle_ids' => $assignedVehicleIds,
                    'inserted_by' => 0,
                    'updated_by' => 0,
                    'is_deleted' => 0,
                ])
            );
        }
    }
}
