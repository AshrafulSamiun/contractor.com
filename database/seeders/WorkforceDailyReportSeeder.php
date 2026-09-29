<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkforceDailyReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WorkforceDailyReportSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first() ?: User::query()->create([
            'name' => 'Demo Admin',
            'username' => 'demo.admin',
            'company_name' => 'Contractor Demo',
            'email' => 'demo.admin@contractor.test',
            'phone' => '555-0100',
            'country' => 'Canada',
            'verify_via' => 'email',
            'role' => 'admin',
            'is_active' => true,
            'project_id' => 1,
            'selected_plan' => 'business',
            'password' => Hash::make('password'),
        ]);

        $templates = [
            [
                'shift' => 'day',
                'shift_name' => 'Day Shift',
                'shift_time' => '08:00',
                'report_location' => '120 Maple St, Toronto',
                'employee_name' => 'John Smith',
                'employee_code' => 'EMP-001',
                'employee_phone' => '(416) 555-0101',
                'employee_email' => 'john@company.com',
                'status' => 'approved',
                'summary' => 'All scheduled work completed successfully.',
                'report_notes' => 'Concrete inspection completed and team signed off.',
                'worked_on_stat_holiday' => false,
                'regular_hours' => 8,
                'overtime_hours' => 2,
                'total_jobs' => 2,
                'customer' => 'ABC Construction',
                'job_site' => '120 Maple St, Toronto',
                'job_order_prefix' => 'JO-210',
                'is_valid' => true,
            ],
            [
                'shift' => 'day',
                'shift_name' => 'Day Shift',
                'shift_time' => '08:00',
                'report_location' => '85 King Ave, Toronto',
                'employee_name' => 'Sarah Johnson',
                'employee_code' => 'EMP-002',
                'employee_phone' => '(416) 555-0112',
                'employee_email' => 'sarah@company.com',
                'status' => 'submitted',
                'summary' => 'Electrical rough-in completed for level two.',
                'report_notes' => 'Awaiting supervisor review before closeout.',
                'worked_on_stat_holiday' => false,
                'regular_hours' => 8,
                'overtime_hours' => 0,
                'total_jobs' => 1,
                'customer' => 'Bright Electric',
                'job_site' => '85 King Ave, Toronto',
                'job_order_prefix' => 'JO-198',
                'is_valid' => true,
            ],
            [
                'shift' => 'day',
                'shift_name' => 'Day Shift',
                'shift_time' => '07:30',
                'report_location' => '44 Lake Rd, Mississauga',
                'employee_name' => 'Mike Peters',
                'employee_code' => 'EMP-003',
                'employee_phone' => '(905) 555-0144',
                'employee_email' => 'mike@company.com',
                'status' => 'approved',
                'summary' => 'Three maintenance jobs completed across the site.',
                'report_notes' => 'One hour overtime used to finish HVAC calibration.',
                'worked_on_stat_holiday' => false,
                'regular_hours' => 9,
                'overtime_hours' => 1,
                'total_jobs' => 3,
                'customer' => 'Lakeside Mechanical',
                'job_site' => '44 Lake Rd, Mississauga',
                'job_order_prefix' => 'JO-187',
                'is_valid' => true,
            ],
            [
                'shift' => 'night',
                'shift_name' => 'Night Shift',
                'shift_time' => '19:00',
                'report_location' => '77 Harbor Blvd, Toronto',
                'employee_name' => 'Emily Carter',
                'employee_code' => 'EMP-004',
                'employee_phone' => '(416) 555-0188',
                'employee_email' => 'emily@company.com',
                'status' => 'draft',
                'summary' => 'Night safety walk and access log review.',
                'report_notes' => 'Draft saved pending final totals.',
                'worked_on_stat_holiday' => true,
                'regular_hours' => 7.5,
                'overtime_hours' => 0.5,
                'total_jobs' => 1,
                'customer' => 'Harbor Security',
                'job_site' => '77 Harbor Blvd, Toronto',
                'job_order_prefix' => 'JO-180',
                'is_valid' => true,
            ],
            [
                'shift' => 'day',
                'shift_name' => 'Day Shift',
                'shift_time' => '06:30',
                'report_location' => '15 River Park Dr, Brampton',
                'employee_name' => 'David Walker',
                'employee_code' => 'EMP-005',
                'employee_phone' => '(905) 555-0165',
                'employee_email' => 'david@company.com',
                'status' => 'final',
                'summary' => 'Site grading and drainage checks completed.',
                'report_notes' => 'Finalized after project manager review.',
                'worked_on_stat_holiday' => false,
                'regular_hours' => 8,
                'overtime_hours' => 1.5,
                'total_jobs' => 2,
                'customer' => 'North Ridge Civil',
                'job_site' => '15 River Park Dr, Brampton',
                'job_order_prefix' => 'JO-240',
                'is_valid' => true,
            ],
        ];

        $items = [];
        for ($index = 0; $index < 15; $index++) {
            $template = $templates[$index % count($templates)];
            $reportNumber = $index + 1;
            $reportDate = now()->subDays($index)->toDateString();
            $regularHours = (float) $template['regular_hours'];
            $overtimeHours = (float) $template['overtime_hours'];
            $totalHours = $regularHours + $overtimeHours;
            $jobOrderBase = (int) preg_replace('/\D/', '', $template['job_order_prefix']);

            $details = [
                [
                    'date' => $reportDate,
                    'day' => now()->subDays($index)->format('l'),
                    'time_from' => $template['shift_time'],
                    'time_to' => $template['shift'] === 'night' ? '03:00' : '12:00',
                    'net_hours' => round($regularHours / 2, 1),
                    'overtime_hours' => 0,
                    'customer' => $template['customer'],
                    'job_site' => $template['job_site'],
                    'job_order' => sprintf('JO-%04d', $jobOrderBase + $reportNumber),
                ],
                [
                    'date' => $reportDate,
                    'day' => now()->subDays($index)->format('l'),
                    'time_from' => $template['shift'] === 'night' ? '03:30' : '13:00',
                    'time_to' => $template['shift'] === 'night' ? '07:30' : '17:00',
                    'net_hours' => round($regularHours - round($regularHours / 2, 1), 1),
                    'overtime_hours' => $overtimeHours,
                    'customer' => $template['customer'],
                    'job_site' => $template['job_site'],
                    'job_order' => sprintf('JO-%04d', $jobOrderBase + $reportNumber + 100),
                ],
            ];

            $items[] = [
                'report_no' => sprintf('DR-2026-%03d', $reportNumber),
                'report_date' => $reportDate,
                'shift' => $template['shift'],
                'shift_name' => $template['shift_name'],
                'shift_time' => $template['shift_time'],
                'report_location' => $template['report_location'],
                'employee_name' => $template['employee_name'],
                'employee_code' => $template['employee_code'],
                'employee_phone' => $template['employee_phone'],
                'employee_email' => $template['employee_email'],
                'status' => $template['status'],
                'summary' => $template['summary'],
                'report_notes' => $template['report_notes'],
                'worked_on_stat_holiday' => $template['worked_on_stat_holiday'],
                'metrics_json' => [
                    'regular_hours' => $regularHours,
                    'overtime_hours' => $overtimeHours,
                    'total_hours' => $totalHours,
                    'total_jobs' => $template['total_jobs'],
                    'deliveries' => min(2, $template['total_jobs']),
                    'pickups' => max(0, $template['total_jobs'] - 1),
                    'incidents' => 0,
                ],
                'details_json' => $details,
                'is_valid' => $template['is_valid'],
            ];
        }

        foreach ($items as $item) {
            WorkforceDailyReport::query()->updateOrCreate(
                ['report_no' => $item['report_no'], 'project_id' => $user->project_id],
                array_merge($item, [
                    'user_id' => $user->id,
                    'project_id' => $user->project_id,
                ]),
            );
        }
    }
}
