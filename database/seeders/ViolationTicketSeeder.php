<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\ViolationTicket;
use Illuminate\Database\Seeder;

class ViolationTicketSeeder extends Seeder
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
            ['ticket_code' => 'TX900101', 'issue_date' => '2026-03-01', 'due_date' => '2026-03-11', 'driver_name' => 'John Doe', 'vehicle_number' => 'ABC-123', 'violation_type' => 'Speeding', 'issued_by' => 'City Police', 'fine_amount' => 145, 'points' => 2, 'ticket_scope' => 'Business', 'status' => 2, 'is_paid' => true, 'payment_method' => 'Credit Card', 'paid_date' => '2026-03-02', 'notes' => 'Resolved within 24 hours.'],
            ['ticket_code' => 'TX900102', 'issue_date' => '2026-03-01', 'due_date' => '2026-03-11', 'driver_name' => 'Jane Smith', 'vehicle_number' => 'XYZ-456', 'violation_type' => 'Parking Violation', 'issued_by' => 'City Parking Authority', 'fine_amount' => 75, 'points' => 0, 'ticket_scope' => 'Business', 'status' => 2, 'is_paid' => true, 'payment_method' => 'Online', 'paid_date' => '2026-03-02', 'notes' => 'Paid by operations team.'],
            ['ticket_code' => 'TX900103', 'issue_date' => '2026-03-02', 'due_date' => '2026-03-12', 'driver_name' => 'Michael Lee', 'vehicle_number' => 'DEF-789', 'violation_type' => 'Illegal Parking', 'issued_by' => 'Office Security', 'fine_amount' => 60, 'points' => 0, 'ticket_scope' => 'Business', 'status' => 2, 'is_paid' => true, 'payment_method' => 'Cash', 'paid_date' => '2026-03-03', 'notes' => 'Wrong spot.'],
            ['ticket_code' => 'TX900104', 'issue_date' => '2026-03-02', 'due_date' => '2026-03-12', 'driver_name' => 'Sarah Khan', 'vehicle_number' => 'JKL-654', 'violation_type' => 'Running Red Light', 'issued_by' => 'City Police', 'fine_amount' => 200, 'points' => 3, 'ticket_scope' => 'Business', 'status' => 2, 'is_paid' => true, 'payment_method' => 'Online', 'paid_date' => '2026-03-04', 'notes' => 'Driver counseled.'],
            ['ticket_code' => 'TX900105', 'issue_date' => '2026-03-03', 'due_date' => '2026-03-13', 'driver_name' => 'Robert Wilson', 'vehicle_number' => 'ABC-123', 'violation_type' => 'Failure to Signal', 'issued_by' => 'Traffic Authority', 'fine_amount' => 120, 'points' => 2, 'ticket_scope' => 'Business', 'status' => 1, 'is_paid' => false, 'payment_method' => null, 'paid_date' => null, 'notes' => 'Pending payment approval.'],
            ['ticket_code' => 'TX900106', 'issue_date' => '2026-03-03', 'due_date' => '2026-03-13', 'driver_name' => 'Emily Carter', 'vehicle_number' => 'XYZ-456', 'violation_type' => 'Parking Violation', 'issued_by' => 'City Parking Authority', 'fine_amount' => 80, 'points' => 0, 'ticket_scope' => 'Business', 'status' => 2, 'is_paid' => true, 'payment_method' => 'Credit Card', 'paid_date' => '2026-03-04', 'notes' => 'Administrative closure.'],
            ['ticket_code' => 'TX900107', 'issue_date' => '2026-03-04', 'due_date' => '2026-03-14', 'driver_name' => 'Daniel Brooks', 'vehicle_number' => 'DEF-789', 'violation_type' => 'Speeding', 'issued_by' => 'City Police', 'fine_amount' => 180, 'points' => 3, 'ticket_scope' => 'Business', 'status' => 2, 'is_paid' => true, 'payment_method' => 'Online', 'paid_date' => '2026-03-05', 'notes' => 'Paid and logged.'],
            ['ticket_code' => 'TX900108', 'issue_date' => '2026-03-04', 'due_date' => '2026-03-14', 'driver_name' => 'Olivia Reed', 'vehicle_number' => 'JKL-654', 'violation_type' => 'Illegal U-turn', 'issued_by' => 'Traffic Authority', 'fine_amount' => 150, 'points' => 2, 'ticket_scope' => 'Business', 'status' => 2, 'is_paid' => true, 'payment_method' => 'Cash', 'paid_date' => '2026-03-05', 'notes' => 'Supervisor informed.'],
            ['ticket_code' => 'TX900109', 'issue_date' => '2026-03-05', 'due_date' => '2026-03-15', 'driver_name' => 'Michael Lee', 'vehicle_number' => 'ABC-123', 'violation_type' => 'Parking Violation', 'issued_by' => 'Local Police', 'fine_amount' => 50, 'points' => 0, 'ticket_scope' => 'Personal', 'status' => 2, 'is_paid' => true, 'payment_method' => 'Online', 'paid_date' => '2026-03-06', 'notes' => 'Paid personally by driver.'],
            ['ticket_code' => 'TX900110', 'issue_date' => '2026-03-05', 'due_date' => '2026-03-15', 'driver_name' => 'John Doe', 'vehicle_number' => 'XYZ-456', 'violation_type' => 'Expired Registration', 'issued_by' => 'Vehicle Registration Office', 'fine_amount' => 100, 'points' => 1, 'ticket_scope' => 'Personal', 'status' => 1, 'is_paid' => false, 'payment_method' => null, 'paid_date' => null, 'notes' => 'Awaiting driver response.'],
        ];

        foreach ($items as $data) {
            $vehicleId = $vehicleIds->get($data['vehicle_number']);
            $driverId = $driverIds->get($data['driver_name']);

            if (! $vehicleId || ! $driverId) {
                continue;
            }

            ViolationTicket::updateOrCreate(
                ['ticket_code' => $data['ticket_code']],
                [
                    'project_id' => 1,
                    'ticket_code' => $data['ticket_code'],
                    'issue_date' => $data['issue_date'],
                    'due_date' => $data['due_date'],
                    'vehicle_id' => $vehicleId,
                    'driver_id' => $driverId,
                    'violation_type' => $data['violation_type'],
                    'issued_by' => $data['issued_by'],
                    'fine_amount' => $data['fine_amount'],
                    'points' => $data['points'],
                    'ticket_scope' => $data['ticket_scope'],
                    'status' => $data['status'],
                    'is_paid' => $data['is_paid'],
                    'payment_method' => $data['payment_method'],
                    'paid_date' => $data['paid_date'],
                    'notes' => $data['notes'],
                    'inserted_by' => 0,
                    'updated_by' => 0,
                    'is_deleted' => 0,
                ]
            );
        }
    }
}
