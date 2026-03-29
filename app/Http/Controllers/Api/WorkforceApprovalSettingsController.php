<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\PermissionService;
use App\Services\WorkforceApprovalFlow;
use Illuminate\Http\Request;

class WorkforceApprovalSettingsController extends Controller
{
    public function show(Request $request)
    {
        $row = SystemSetting::firstOrCreate(['key' => 'workforce_approval_flow'], [
            'value' => null,
        ]);

        $payload = $row->value ? json_decode($row->value, true) : null;
        if (!is_array($payload)) {
            $flow = app(WorkforceApprovalFlow::class);
            $defaultChannels = $flow->getDefaultReminderChannels();
            $payload = [
                'default' => [
                    'steps' => $flow->defaultSteps(),
                    'sla_hours' => 24,
                    'reminder_enabled' => true,
                    'reminder_frequency_hours' => 6,
                    'reminder_channels' => $defaultChannels,
                ],
                'modules' => [],
            ];
        } else {
            $flow = app(WorkforceApprovalFlow::class);
            $defaultChannels = $flow->getDefaultReminderChannels();
            if (empty($payload['default']['reminder_channels'])) {
                $payload['default']['reminder_channels'] = $defaultChannels;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'default' => $payload['default'] ?? [],
                'modules' => $payload['modules'] ?? [],
                'roles' => ['admin', 'manager', 'staff'],
                'module_list' => ['daily_report', 'incident_report', 'timesheet'],
                'channel_list' => ['email', 'in_app'],
            ],
        ]);
    }

    public function update(Request $request)
    {
        if (!app(PermissionService::class)->can($request->user(), 'settings', 'edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'default' => ['required', 'array'],
            'default.steps' => ['required', 'array', 'min:1'],
            'default.steps.*.key' => ['required', 'string', 'max:30'],
            'default.steps.*.label' => ['required', 'string', 'max:60'],
            'default.steps.*.roles' => ['nullable', 'array'],
            'default.steps.*.roles.*' => ['string', 'max:30'],
            'default.steps.*.enabled' => ['nullable', 'boolean'],
            'default.sla_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'default.reminder_enabled' => ['nullable', 'boolean'],
            'default.reminder_frequency_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'default.reminder_channels' => ['nullable', 'array'],
            'default.reminder_channels.*' => ['string', 'max:30'],
            'modules' => ['nullable', 'array'],
        ]);

        $row = SystemSetting::firstOrCreate(['key' => 'workforce_approval_flow'], [
            'value' => null,
        ]);

        $flow = app(WorkforceApprovalFlow::class);
        $defaultChannels = $flow->getDefaultReminderChannels();

        $payload = [
            'default' => $this->normalizeConfig($validated['default'], $defaultChannels),
            'modules' => [],
        ];

        $modules = $validated['modules'] ?? [];
        foreach ($modules as $moduleKey => $moduleConfig) {
            if (!is_array($moduleConfig)) {
                continue;
            }
            $payload['modules'][$moduleKey] = $this->normalizeConfig($moduleConfig, $defaultChannels);
        }

        $row->value = json_encode($payload);
        $row->save();

        return response()->json([
            'success' => true,
            'data' => [
                'default' => $payload['default'],
                'modules' => $payload['modules'],
                'roles' => ['admin', 'manager', 'staff'],
                'module_list' => ['daily_report', 'incident_report', 'timesheet'],
                'channel_list' => ['email', 'in_app'],
            ],
        ]);
    }

    private function normalizeConfig(array $config, array $defaultChannels): array
    {
        $config['steps'] = $this->enforceStepOrder($config['steps'] ?? []);
        $channels = $config['reminder_channels'] ?? $defaultChannels;
        $channels = array_values(array_unique(array_filter($channels, function ($channel) {
            return in_array($channel, ['email', 'in_app'], true);
        })));
        if (!$channels) {
            $channels = $defaultChannels;
        }

        return [
            'steps' => $config['steps'],
            'sla_hours' => (int) ($config['sla_hours'] ?? 24),
            'reminder_enabled' => (bool) ($config['reminder_enabled'] ?? true),
            'reminder_frequency_hours' => (int) ($config['reminder_frequency_hours'] ?? 6),
            'reminder_channels' => $channels,
        ];
    }

    private function enforceStepOrder(array $steps): array
    {
        $required = [
            'submit' => [
                'key' => 'submit',
                'label' => 'Submit',
                'roles' => ['staff', 'manager', 'admin'],
                'enabled' => true,
            ],
            'approve' => [
                'key' => 'approve',
                'label' => 'Approve',
                'roles' => ['manager', 'admin'],
                'enabled' => true,
            ],
            'final' => [
                'key' => 'final',
                'label' => 'Finalize',
                'roles' => ['admin'],
                'enabled' => true,
            ],
        ];

        $normalized = [];
        foreach ($steps as $step) {
            if (!is_array($step)) {
                continue;
            }
            $key = $step['key'] ?? null;
            if ($key && isset($required[$key])) {
                $required[$key] = array_merge($required[$key], $step);
            } else {
                $normalized[] = $step;
            }
        }

        return array_values(array_merge(
            [$required['submit'], $required['approve'], $required['final']],
            $normalized
        ));
    }
}
