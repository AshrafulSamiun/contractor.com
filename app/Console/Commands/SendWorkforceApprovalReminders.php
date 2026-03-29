<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WorkforceApproval;
use App\Models\WorkforceDailyReport;
use App\Models\WorkforceIncidentReport;
use App\Models\WorkforceTimesheet;
use App\Services\NotificationDispatcher;
use App\Services\WorkforceApprovalFlow;
use Illuminate\Console\Command;

class SendWorkforceApprovalReminders extends Command
{
    protected $signature = 'workforce:send-approval-reminders';
    protected $description = 'Send SLA reminders for pending workforce approvals.';

    public function handle(): int
    {
        $flow = app(WorkforceApprovalFlow::class);
        $dispatcher = app(NotificationDispatcher::class);

        $modules = [
            'daily_report' => WorkforceDailyReport::class,
            'incident_report' => WorkforceIncidentReport::class,
            'timesheet' => WorkforceTimesheet::class,
        ];

        foreach ($modules as $key => $model) {
            if (!$flow->isReminderEnabled($key)) {
                continue;
            }

            $slaHours = max(1, $flow->getSlaHours($key));
            $frequencyHours = max(1, $flow->getReminderFrequencyHours($key));
            $channels = $flow->getReminderChannels($key);
            $cutoff = now()->subHours($slaHours);

            $this->sendStepReminders($key, 'submitted', 'approve', $model, $cutoff, $slaHours, $frequencyHours, $channels, $flow, $dispatcher);

            if ($flow->isEnabled('final', $key)) {
                $this->sendStepReminders($key, 'approved', 'final', $model, $cutoff, $slaHours, $frequencyHours, $channels, $flow, $dispatcher);
            }
        }

        return self::SUCCESS;
    }

    private function sendStepReminders(
        string $moduleKey,
        string $status,
        string $step,
        string $model,
        $cutoff,
        int $slaHours,
        int $frequencyHours,
        array $channels,
        WorkforceApprovalFlow $flow,
        NotificationDispatcher $dispatcher
    ): void {
        $roles = $flow->rolesFor($step, $moduleKey);
        if (!$roles) {
            return;
        }

        $items = $model::query()
            ->where('status', $status)
            ->where('updated_at', '<=', $cutoff)
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $approvers = User::query()->whereIn('role', $roles)->get();
        if ($approvers->isEmpty()) {
            return;
        }

        foreach ($items as $item) {
            $lastReminder = WorkforceApproval::query()
                ->where('entity_type', $moduleKey)
                ->where('entity_id', $item->id)
                ->where('status', 'reminder')
                ->orderByDesc('created_at')
                ->first();

            if ($lastReminder && $lastReminder->created_at && $lastReminder->created_at->gt(now()->subHours($frequencyHours))) {
                continue;
            }

            foreach ($approvers as $approver) {
                $dispatcher->sendWorkforceEvent($approver, [
                    'subject' => 'Approval reminder',
                    'message' => "Pending {$moduleKey} {$status} over {$slaHours} hours.",
                    'entity_type' => $moduleKey,
                    'entity_id' => $item->id,
                    'status' => $status,
                ], "{$moduleKey}_reminder", $channels);
            }

            WorkforceApproval::create([
                'user_id' => $item->user_id,
                'entity_type' => $moduleKey,
                'entity_id' => $item->id,
                'status' => 'reminder',
                'step' => $step,
                'approved_by' => null,
                'actor_role' => null,
                'approved_at' => now(),
                'note' => "Reminder sent (every {$frequencyHours}h).",
                'meta' => [
                    'frequency_hours' => $frequencyHours,
                    'sla_hours' => $slaHours,
                    'channels' => $channels,
                ],
            ]);
        }
    }
}
