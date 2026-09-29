<?php

namespace Database\Seeders;

use App\Models\EmployeeProfile;
use Illuminate\Database\Seeder;

class EmployeeProfileSeeder extends Seeder
{
    public function run(): void
    {
        $projectId = 2;
        $employees = [
            ['John Anderson', 'Operations', 'Field Technician'],
            ['Sarah Mitchell', 'Administration', 'Office Manager'],
            ['Michael Brown', 'Operations', 'Plumber'],
            ['Emily Davis', 'Customer Service', 'CSR'],
            ['David Wilson', 'Operations', 'HVAC Technician'],
            ['Jessica Taylor', 'Finance', 'Accountant'],
            ['Daniel Martinez', 'Operations', 'Electrician'],
            ['Amanda Clark', 'Human Resources', 'HR Coordinator'],
            ['Christopher Lee', 'Operations', 'Carpenter'],
            ['Ashley Johnson', 'Marketing', 'Marketing Specialist'],
            ['Matthew Thomas', 'Operations', 'Installer'],
            ['Nicole Harris', 'Customer Service', 'Dispatcher'],
            ['Andrew White', 'Operations', 'Maintenance Technician'],
            ['Brittany Martin', 'Finance', 'Payroll Specialist'],
            ['Ryan Scott', 'Operations', 'Roofer'],
            ['Lauren Walker', 'Administration', 'Executive Assistant'],
            ['Joshua Hall', 'Operations', 'Welder'],
            ['Stephanie Young', 'Customer Service', 'CSR'],
            ['Tyler King', 'Operations', 'Equipment Operator'],
            ['Kayla Wright', 'Human Resources', 'HR Assistant'],
        ];

        foreach ($employees as $index => [$name, $department, $position]) {
            $sequence = $index + 157;

            EmployeeProfile::withoutGlobalScopes()->updateOrCreate(
                [
                    'project_id' => $projectId,
                    'system_no' => 'EMP-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT),
                ],
                [
                    'account_type' => 4,
                    'system_prefix' => 'EMP',
                    'employee_id' => 'EID-2024-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                    'account_name' => $name,
                    'department' => $department,
                    'position' => $position,
                    'hire_date' => '2024-05-'.str_pad((string) min($index + 1, 31), 2, '0', STR_PAD_LEFT),
                    'account_no' => 'AC-'.str_pad((string) (100245 + $index), 6, '0', STR_PAD_LEFT),
                    'cell_phone' => '(555) '.str_pad((string) (123 + $index * 11), 3, '0', STR_PAD_LEFT).'-'.str_pad((string) (4567 + $index * 111), 4, '0', STR_PAD_LEFT),
                    'email' => strtolower(str_replace(' ', '.', $name)).'@domain.com',
                    'screening_expires_at' => in_array($index, [2, 9, 17], true) ? '2025-05-31' : '2027-05-31',
                    'status_active' => ! in_array($index, [9, 13], true),
                    'inserted_by' => 0,
                    'updated_by' => 0,
                    'is_deleted' => false,
                ]
            );
        }

        $this->command?->info('Seeded employee profile data for project 2.');
    }
}
