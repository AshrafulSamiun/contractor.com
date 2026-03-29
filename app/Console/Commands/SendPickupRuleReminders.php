<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use App\Models\PickupRule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPickupRuleReminders extends Command
{
    protected $signature = 'pickup:send-rule-reminders';
    protected $description = 'Send SLA breach alerts for pickup rules.';

    public function handle(): int
    {
        $rules = PickupRule::query()
            ->where('active', true)
            ->where('reminder_enabled', true)
            ->get();

        if ($rules->isEmpty()) {
            return self::SUCCESS;
        }

        $admins = User::query()->where('role', 'admin')->get();
        if ($admins->isEmpty()) {
            return self::SUCCESS;
        }

        foreach ($rules as $rule) {
            if (!$this->isBreach($rule)) {
                continue;
            }
            $frequency = max(1, (int) ($rule->reminder_frequency_hours ?? 24));
            $last = $rule->last_reminder_at;
            if ($last && Carbon::parse($last)->diffInHours(now()) < $frequency) {
                continue;
            }
            $channels = $rule->reminder_channels ?? [];
            $sendEmail = in_array('email', $channels, true);
            $sendInApp = in_array('in_app', $channels, true);

            foreach ($admins as $admin) {
                $context = [
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->name,
                    'type' => $rule->type,
                    'window_hours' => $rule->window_hours,
                    'sla_hours' => $rule->sla_hours,
                ];

                if ($sendInApp) {
                    NotificationLog::create([
                        'user_id' => $admin->id,
                        'channel' => 'in_app',
                        'status' => 'queued',
                        'to' => $admin->id,
                        'context' => 'Pickup rule SLA breach',
                        'context_json' => json_encode($context),
                    ]);
                }

                if ($sendEmail && $admin->email) {
                    try {
                        $subject = 'Pickup Rule SLA Breach';
                        $body = "Rule: {$rule->name}\nType: {$rule->type}\nWindow: {$rule->window_hours}h\nSLA: {$rule->sla_hours}h";
                        Mail::raw($body, function ($message) use ($admin, $subject) {
                            $message->to($admin->email)->subject($subject);
                        });
                        NotificationLog::create([
                            'user_id' => $admin->id,
                            'channel' => 'email',
                            'status' => 'sent',
                            'to' => $admin->email,
                            'context' => 'Pickup rule SLA breach',
                            'context_json' => json_encode($context),
                        ]);
                    } catch (\Throwable $e) {
                        NotificationLog::create([
                            'user_id' => $admin->id,
                            'channel' => 'email',
                            'status' => 'failed',
                            'to' => $admin->email,
                            'context' => 'Pickup rule SLA breach',
                            'context_json' => json_encode($context),
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $rule->update([
                'last_reminder_at' => now(),
                'sla_breach_notified_at' => now(),
            ]);
        }

        return self::SUCCESS;
    }

    private function isBreach(PickupRule $rule): bool
    {
        return (int) ($rule->window_hours ?? 0) > (int) ($rule->sla_hours ?? 0);
    }
}
