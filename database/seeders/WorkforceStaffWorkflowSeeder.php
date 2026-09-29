<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkforceStaffAttendance;
use App\Models\WorkforceStaffRequest;
use App\Models\WorkforceTaskAssignment;
use App\Models\WorkforceWorkSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WorkforceStaffWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first() ?: User::query()->create([
            'name' => 'Workflow Administrator',
            'username' => 'workflow.admin',
            'email' => 'workflow.admin@contractor.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'project_id' => 1,
            'is_active' => true,
        ]);

        $projectId = $user->project_id;
        $staff = [
            ['Michael Brown', 'EMP-1001', 'Maintenance Technician', 'Maintenance'],
            ['Sarah Johnson', 'EMP-1002', 'Plumber', 'Plumbing'],
            ['David Lee', 'EMP-1003', 'Electrician', 'Electrical'],
            ['Jessica White', 'EMP-1004', 'HVAC Technician', 'HVAC'],
            ['Robert Taylor', 'EMP-1005', 'Carpenter', 'Carpentry'],
            ['Daniel Martin', 'EMP-1006', 'Painter', 'Painting'],
            ['Lisa Anderson', 'EMP-1007', 'Cleaner', 'Cleaning'],
            ['James Wilson', 'EMP-1008', 'General Labor', 'General Labor'],
            ['Kevin Clark', 'EMP-1009', 'Security Officer', 'Security'],
            ['Amanda Harris', 'EMP-1010', 'Admin Assistant', 'Administration'],
            ['Mark Davis', 'EMP-1011', 'Maintenance Technician', 'Maintenance'],
            ['Emily Chen', 'EMP-1012', 'Site Coordinator', 'Operations'],
        ];
        $customers = ['ABC Properties Ltd.', 'Greenfield Estates', 'Sunrise Towers', 'Harbour View Residences'];
        $locations = ['Main Tower', 'East Wing', 'West Wing', 'North Building'];

        foreach ($staff as $index => [$name, $employeeId, $position, $department]) {
            $date = now()->subDays($index)->toDateString();
            $customer = $customers[$index % count($customers)];
            $location = $locations[$index % count($locations)];
            $reportNo = 'SA-' . now()->format('Ymd') . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

            WorkforceStaffAttendance::query()->updateOrCreate(
                ['project_id' => $projectId, 'report_no' => $reportNo],
                [
                    'user_id' => $user->id, 'attendance_date' => $date, 'prepared_by' => $user->name,
                    'staff_name' => $name, 'employee_id' => $employeeId, 'position_title' => $position,
                    'phone' => '(604) 555-' . str_pad((string) (1000 + $index), 4, '0', STR_PAD_LEFT),
                    'email' => strtolower(str_replace(' ', '.', $name)) . '@company.test',
                    'location_site' => $location, 'customer_job_site' => $customer, 'department' => $department,
                    'shift_name' => 'Day Shift', 'check_in_time' => '08:00:00', 'check_out_time' => '16:30:00',
                    'total_minutes' => 510, 'status' => 'present', 'overtime' => $index % 4 === 0,
                    'late_arrival' => false, 'early_departure' => false,
                    'work_performed' => 'Completed scheduled site work and daily safety inspection.',
                    'notes' => 'Seeded Staff Workflow attendance record.', 'remarks' => null,
                ]
            );

            $recordDate = now()->subDays($index)->toDateString();
            $requestNo = 'SR-' . now()->format('Ymd') . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            WorkforceStaffRequest::query()->updateOrCreate(
                ['project_id' => $projectId, 'record_no' => $requestNo],
                $this->record($user->id, $projectId, $requestNo, $recordDate, "Leave request for {$name}", ['vacation', 'sick_leave', 'remote_work'][$index % 3], $name, $department, $location, $customer, ['new', 'pending', 'approved'][$index % 3], ['normal', 'high', 'low'][$index % 3], now()->addDays($index + 2), now()->addDays($index + 3))
            );

            $taskNo = 'TA-' . now()->format('Ymd') . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            WorkforceTaskAssignment::query()->updateOrCreate(
                ['project_id' => $projectId, 'record_no' => $taskNo],
                $this->record($user->id, $projectId, $taskNo, $recordDate, "Inspect and complete scheduled work at {$location}", ['maintenance', 'inspection', 'repair', 'cleaning'][$index % 4], $name, $department, $location, $customer, ['open', 'assigned', 'in_progress', 'completed'][$index % 4], ['normal', 'high', 'urgent', 'low'][$index % 4], now()->addDays($index), now()->addDays($index + 1), true)
            );

            $scheduleNo = 'WS-' . now()->format('Ymd') . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            WorkforceWorkSchedule::query()->updateOrCreate(
                ['project_id' => $projectId, 'record_no' => $scheduleNo],
                $this->record($user->id, $projectId, $scheduleNo, $recordDate, "{$position} - Day Shift", ['day_shift', 'evening_shift', 'night_shift'][$index % 3], $name, $department, $location, $customer, ['scheduled', 'confirmed', 'completed'][$index % 3], 'normal', now()->startOfWeek()->addDays($index % 5)->setTime(8, 0), now()->startOfWeek()->addDays($index % 5)->setTime(16, 30))
            );
        }
    }

    private function record(int $userId, $projectId, string $recordNo, string $date, string $title, string $type, string $staffName, string $department, string $location, string $customer, string $status, string $priority, $start, $end, bool $task = false): array
    {
        return [
            'user_id' => $userId, 'project_id' => $projectId, 'record_date' => $date, 'title' => $title,
            'request_type' => $type, 'staff_name' => $task ? null : $staffName, 'assigned_to' => $task ? $staffName : null,
            'location_site' => $location, 'customer_job_site' => $customer, 'department' => $department,
            'start_at' => $start, 'end_at' => $end, 'status' => $status, 'priority' => $priority,
            'notes' => 'Seeded Staff Workflow record.',
            'details_json' => ['position_title' => $department . ' Technician', 'supervisor' => 'John Smith', 'approval_status' => $status],
        ];
    }
}
