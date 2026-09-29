<?php

namespace Database\Seeders;

use App\Models\DailyLog;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DailyLogSeeder extends Seeder
{
    public function run(): void
    {
        $vehicleIds = Vehicle::query()
            ->pluck('id', 'vehicle_number')
            ->map(fn ($id) => (int) $id);

        $driverIds = Driver::query()
            ->pluck('id', 'driver_name')
            ->map(fn ($id) => (int) $id);

        $today = now()->toDateString();

        $items = [
            ['log_code' => 'DL-2026-001', 'log_date' => $today, 'vehicle_number' => 'ABC-123', 'driver_name' => 'John Doe', 'start_time' => '06:30', 'end_time' => '14:45', 'start_odometer' => 15280, 'end_odometer' => 15420, 'fuel_added' => 15, 'purpose_location' => 'Job Site - Downtown Project', 'status' => 2],
            ['log_code' => 'DL-2026-002', 'log_date' => $today, 'vehicle_number' => 'DEF-789', 'driver_name' => 'Sarah Khan', 'start_time' => '07:00', 'end_time' => null, 'start_odometer' => 28540, 'end_odometer' => null, 'fuel_added' => null, 'purpose_location' => 'Material Pickup & Delivery', 'status' => 1],
            ['log_code' => 'DL-2026-003', 'log_date' => $today, 'vehicle_number' => 'XYZ-456', 'driver_name' => 'Michael Lee', 'start_time' => '06:45', 'end_time' => '13:30', 'start_odometer' => 8260, 'end_odometer' => 8340, 'fuel_added' => 10, 'purpose_location' => 'Client Meeting - Westside', 'status' => 2],
            ['log_code' => 'DL-2026-004', 'log_date' => $today, 'vehicle_number' => 'GHI-321', 'driver_name' => 'Robert Wilson', 'start_time' => '08:00', 'end_time' => null, 'start_odometer' => 42050, 'end_odometer' => null, 'fuel_added' => null, 'purpose_location' => 'Inspection - Harbor Site', 'status' => 1],
            ['log_code' => 'DL-2026-005', 'log_date' => $today, 'vehicle_number' => 'VWX-333', 'driver_name' => 'Emily Carter', 'start_time' => '07:30', 'end_time' => '15:15', 'start_odometer' => 19620, 'end_odometer' => 19735, 'fuel_added' => 12, 'purpose_location' => 'Equipment Transport', 'status' => 2],
            ['log_code' => 'DL-2026-006', 'log_date' => $today, 'vehicle_number' => 'STU-222', 'driver_name' => 'Olivia Reed', 'start_time' => '09:00', 'end_time' => null, 'start_odometer' => 31890, 'end_odometer' => null, 'fuel_added' => null, 'purpose_location' => 'Emergency Repair Call', 'status' => 1],
        ];

        foreach ($items as $data) {
            $vehicleId = $vehicleIds->get($data['vehicle_number']);
            $driverId = $driverIds->get($data['driver_name']);

            if (! $vehicleId || ! $driverId) {
                continue;
            }

            $miles = $data['end_odometer'] !== null
                ? $data['end_odometer'] - $data['start_odometer']
                : null;

            DailyLog::updateOrCreate(
                ['log_code' => $data['log_code']],
                [
                    'project_id' => 1,
                    'log_code' => $data['log_code'],
                    'log_date' => $data['log_date'],
                    'vehicle_id' => $vehicleId,
                    'driver_id' => $driverId,
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'start_odometer' => $data['start_odometer'],
                    'end_odometer' => $data['end_odometer'],
                    'miles_driven' => $miles,
                    'fuel_added' => $data['fuel_added'],
                    'purpose_location' => $data['purpose_location'],
                    'status' => $data['status'],
                    'notes' => null,
                    'inserted_by' => 0,
                    'updated_by' => 0,
                    'is_deleted' => 0,
                ]
            );
        }
    }
}
