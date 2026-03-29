<?php

namespace App\Services;

use App\Models\SystemSetting;

class WorkforceApprovalFlow
{
    public function getDefaultReminderChannels(): array
    {
        $row = SystemSetting::firstOrCreate(['key' => 'notification_defaults'], [
            'value' => null,
        ]);

        $defaults = $row->value ? json_decode($row->value, true) : [];
        if (!is_array($defaults)) {
            $defaults = [];
        }

        $channels = [];
        if (!empty($defaults['notify_channel_email'])) {
            $channels[] = 'email';
        }
        if (!empty($defaults['notify_channel_in_app'])) {
            $channels[] = 'in_app';
        }

        return $channels ?: ['in_app'];
    }

    public function getConfig(?string $module = null): array
    {
        $row = SystemSetting::firstOrCreate(['key' => 'workforce_approval_flow'], [
            'value' => null,
        ]);

        $payload = $row->value ? json_decode($row->value, true) : null;
        if (!is_array($payload)) {
            $payload = [
                'default' => [
                    'steps' => $this->defaultSteps(),
                    'sla_hours' => 24,
                    'reminder_enabled' => true,
                    'reminder_frequency_hours' => 6,
                    'reminder_channels' => $this->getDefaultReminderChannels(),
                ],
                'modules' => [],
            ];
            $row->value = json_encode($payload);
            $row->save();
        }

        $default = $payload['default'] ?? [
            'steps' => $this->defaultSteps(),
            'sla_hours' => 24,
            'reminder_enabled' => true,
            'reminder_frequency_hours' => 6,
            'reminder_channels' => $this->getDefaultReminderChannels(),
        ];

        if (empty($default['reminder_channels'])) {
            $default['reminder_channels'] = $this->getDefaultReminderChannels();
        }

        $moduleConfig = $module ? ($payload['modules'][$module] ?? []) : [];
        $steps = $this->enforceStepOrder($this->normalize($moduleConfig['steps'] ?? $default['steps']));

        return [
            'steps' => $steps,
            'sla_hours' => (int) ($moduleConfig['sla_hours'] ?? $default['sla_hours'] ?? 24),
            'reminder_enabled' => (bool) ($moduleConfig['reminder_enabled'] ?? $default['reminder_enabled'] ?? true),
            'reminder_frequency_hours' => (int) ($moduleConfig['reminder_frequency_hours'] ?? $default['reminder_frequency_hours'] ?? 6),
            'reminder_channels' => $moduleConfig['reminder_channels'] ?? $default['reminder_channels'] ?? $this->getDefaultReminderChannels(),
        ];
    }

    public function getFlow(?string $module = null): array
    {
        return $this->getConfig($module)['steps'];
    }

    public function rolesFor(string $stepKey, ?string $module = null): array
    {
        $steps = $this->getFlow($module);
        foreach ($steps as $step) {
            if (($step['key'] ?? '') === $stepKey && ($step['enabled'] ?? true)) {
                return $step['roles'] ?? [];
            }
        }

        return [];
    }

    public function isEnabled(string $stepKey, ?string $module = null): bool
    {
        $steps = $this->getFlow($module);
        foreach ($steps as $step) {
            if (($step['key'] ?? '') === $stepKey) {
                return (bool) ($step['enabled'] ?? true);
            }
        }

        return false;
    }

    public function isRoleAllowed(?string $role, string $stepKey, ?string $module = null): bool
    {
        if (!$role) {
            return false;
        }
        if (!$this->isEnabled($stepKey, $module)) {
            return false;
        }

        $roles = $this->rolesFor($stepKey, $module);
        return in_array($role, $roles, true);
    }

    public function getSlaHours(?string $module = null): int
    {
        return $this->getConfig($module)['sla_hours'];
    }

    public function isReminderEnabled(?string $module = null): bool
    {
        return $this->getConfig($module)['reminder_enabled'];
    }

    public function getReminderFrequencyHours(?string $module = null): int
    {
        return $this->getConfig($module)['reminder_frequency_hours'];
    }

    public function getReminderChannels(?string $module = null): array
    {
        return $this->getConfig($module)['reminder_channels'] ?? ['email', 'in_app'];
    }

    public function defaultSteps(): array
    {
        return [
            [
                'key' => 'submit',
                'label' => 'Submit',
                'roles' => ['staff', 'manager', 'admin'],
                'enabled' => true,
            ],
            [
                'key' => 'approve',
                'label' => 'Approve',
                'roles' => ['manager', 'admin'],
                'enabled' => true,
            ],
            [
                'key' => 'final',
                'label' => 'Finalize',
                'roles' => ['admin'],
                'enabled' => true,
            ],
        ];
    }

    private function normalize(array $steps): array
    {
        $allowedRoles = ['admin', 'manager', 'staff'];
        $normalized = [];
        foreach ($steps as $step) {
            $roles = array_values(array_filter($step['roles'] ?? [], function ($role) use ($allowedRoles) {
                return in_array($role, $allowedRoles, true);
            }));
            $normalized[] = [
                'key' => $step['key'] ?? 'step',
                'label' => $step['label'] ?? 'Step',
                'roles' => $roles,
                'enabled' => (bool) ($step['enabled'] ?? true),
            ];
        }
        return $normalized;
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

        $extras = [];
        foreach ($steps as $step) {
            if (!is_array($step)) {
                continue;
            }
            $key = $step['key'] ?? null;
            if ($key && isset($required[$key])) {
                $required[$key] = array_merge($required[$key], $step);
            } else {
                $extras[] = $step;
            }
        }

        return array_values(array_merge(
            [$required['submit'], $required['approve'], $required['final']],
            $extras
        ));
    }
}
