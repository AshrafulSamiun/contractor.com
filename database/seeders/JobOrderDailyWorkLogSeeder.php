<?php

namespace Database\Seeders;

use App\Models\JobOrder;
use App\Models\JobOrderDailyWorkLog;
use App\Models\JobOrderDailyWorkLogEntry;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class JobOrderDailyWorkLogSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        JobOrderDailyWorkLogEntry::truncate();
        JobOrderDailyWorkLog::truncate();
        Schema::enableForeignKeyConstraints();

        $jobOrders = JobOrder::query()->take(5)->get();
        if ($jobOrders->isEmpty()) {
            return;
        }

        foreach ($jobOrders as $index => $jobOrder) {
            $periodStart = Carbon::now()->startOfMonth()->subMonths($index);
            $periodEnd = (clone $periodStart)->copy()->endOfMonth();

            $log = JobOrderDailyWorkLog::create([
                'job_order_id' => $jobOrder->id,
                'log_no' => sprintf('DWL-%s-%04d', date('Y'), $index + 1),
                'period_start' => $periodStart->format('Y-m-d'),
                'period_end' => $periodEnd->format('Y-m-d'),
                'project_manager' => $jobOrder->site_contact_person ?: 'Project Manager ' . ($index + 1),
                'total_wages' => 0,
                'total_materials' => 0,
                'total_overhead' => 0,
                'total_cost' => 0,
                'notes' => 'Seeded daily work log for ' . $jobOrder->job_order_no,
            ]);

            $runningTotal = 0;
            $wages = 0;
            $materials = 0;
            $overhead = 0;

            for ($day = 0; $day < 10; $day++) {
                $workDate = (clone $periodStart)->addDays($day);
                $rowWages = rand(1800, 3500);
                $rowMaterials = rand(700, 2500);
                $rowOverhead = rand(200, 800);
                $dailyTotal = $rowWages + $rowMaterials + $rowOverhead;
                $runningTotal += $dailyTotal;
                $wages += $rowWages;
                $materials += $rowMaterials;
                $overhead += $rowOverhead;

                JobOrderDailyWorkLogEntry::create([
                    'daily_work_log_id' => $log->id,
                    'work_date' => $workDate->format('Y-m-d'),
                    'day_name' => $workDate->format('D'),
                    'start_time' => '08:00',
                    'end_time' => '17:00',
                    'activity_description' => 'Daily site activity for ' . $jobOrder->job_description,
                    'wages' => $rowWages,
                    'materials' => $rowMaterials,
                    'overhead' => $rowOverhead,
                    'daily_total' => $dailyTotal,
                    'running_total' => $runningTotal,
                ]);
            }

            $log->update([
                'total_wages' => $wages,
                'total_materials' => $materials,
                'total_overhead' => $overhead,
                'total_cost' => $wages + $materials + $overhead,
            ]);
        }
    }
}
