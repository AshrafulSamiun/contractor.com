<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PickupRule;
use App\Models\PickupRuleAuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class PickupRuleController extends Controller
{
    private array $allowedRoles = ['admin', 'manager', 'staff'];

    public function index(Request $request)
    {
        $this->assertExternalLockerPlan($request, $request->string('type')->toString() ?: null);

        $query = PickupRule::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('updated_at');
        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->assertCanEdit($request);
        $validated = $this->validatePayload($request);
        $this->assertExternalLockerPlan($request, $validated['type'] ?? null);
        $validated['allowed_roles'] = $this->normalizeRoles($validated['allowed_roles'] ?? []);
        $validated['user_id'] = $request->user()->id;
        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        $rule = PickupRule::create($validated);
        $this->logChange($rule, $request->user()->id, 'created', null, $rule->toArray());

        return response()->json([
            'success' => true,
            'data' => $rule,
        ], 201);
    }

    public function update(Request $request, PickupRule $pickupRule)
    {
        $this->assertCanEdit($request, $pickupRule);
        $validated = $this->validatePayload($request, false);
        $this->assertExternalLockerPlan($request, $validated['type'] ?? $pickupRule->type);
        $validated['allowed_roles'] = $this->normalizeRoles($validated['allowed_roles'] ?? $pickupRule->allowed_roles);
        $validated['updated_by'] = $request->user()->id;

        $before = $pickupRule->toArray();
        $pickupRule->update($validated);
        $this->logChange($pickupRule, $request->user()->id, 'updated', $before, $pickupRule->toArray());

        return response()->json([
            'success' => true,
            'data' => $pickupRule,
        ]);
    }

    public function destroy(Request $request, PickupRule $pickupRule)
    {
        $this->assertCanEdit($request, $pickupRule);
        $before = $pickupRule->toArray();
        $pickupRule->delete();
        $this->logChange($pickupRule, $request->user()->id, 'deleted', $before, null);

        return response()->json(['success' => true]);
    }

    public function audits(Request $request, PickupRule $pickupRule)
    {
        $this->assertOwnedRule($request, $pickupRule);

        $logs = PickupRuleAuditLog::query()
            ->where('pickup_rule_id', $pickupRule->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $userIds = $logs->pluck('user_id')->filter()->unique()->values();
        $users = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name', 'role'])
            ->keyBy('id');

        $data = $logs->map(function ($log) use ($users) {
            $user = $log->user_id ? $users->get($log->user_id) : null;
            return [
                'id' => $log->id,
                'pickup_rule_id' => $log->pickup_rule_id,
                'user_id' => $log->user_id,
                'user_name' => $user?->name,
                'user_role' => $user?->role,
                'action' => $log->action,
                'before_json' => $log->before_json,
                'after_json' => $log->after_json,
                'created_at' => optional($log->created_at)->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function slaSummary(Request $request)
    {
        $this->assertExternalLockerPlan($request, $request->string('type')->toString() ?: null);

        $query = PickupRule::query()->where('user_id', $request->user()->id);
        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }
        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }
        }
        if ($request->filled('from')) {
            $query->whereDate('updated_at', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $query->whereDate('updated_at', '<=', $request->string('to')->toString());
        }

        $rules = $query->get();
        $summary = [];
        foreach (['front_desk', 'facility_locker', 'external_locker', 'counter_staff'] as $type) {
            $items = $rules->where('type', $type);
            $summary[$type] = [
                'total' => $items->count(),
                'breaches' => $items->filter(fn ($rule) => $this->isBreach($rule))->count(),
            ];
        }

        $breaches = $rules->filter(fn ($rule) => $this->isBreach($rule))
            ->values()
            ->map(function ($rule) {
                return [
                    'id' => $rule->id,
                    'type' => $rule->type,
                    'name' => $rule->name,
                    'window_hours' => $rule->window_hours,
                    'sla_hours' => $rule->sla_hours,
                    'updated_at' => optional($rule->updated_at)->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'breaches' => $breaches,
            ],
        ]);
    }

    public function exportSla(Request $request)
    {
        $this->assertExternalLockerPlan($request, $request->string('type')->toString() ?: null);

        $query = PickupRule::query()->where('user_id', $request->user()->id);
        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }
        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }
        }
        if ($request->filled('from')) {
            $query->whereDate('updated_at', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $query->whereDate('updated_at', '<=', $request->string('to')->toString());
        }

        $rules = $query->get()->filter(fn ($rule) => $this->isBreach($rule));
        $filename = 'pickup_rule_sla_breaches_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rules) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'type', 'name', 'window_hours', 'sla_hours', 'updated_at']);
            foreach ($rules as $rule) {
                fputcsv($handle, [
                    $rule->id,
                    $rule->type,
                    $rule->name,
                    $rule->window_hours,
                    $rule->sla_hours,
                    optional($rule->updated_at)->toDateTimeString(),
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function validatePayload(Request $request, bool $requireType = true): array
    {
        return $request->validate([
            'type' => [$requireType ? 'required' : 'sometimes', 'string', 'max:40', 'in:front_desk,facility_locker,external_locker,counter_staff'],
            'allowed_roles' => ['nullable', 'array'],
            'allowed_roles.*' => ['string', 'in:admin,manager,staff'],
            'name' => ['required', 'string', 'max:120'],
            'verification_method' => ['nullable', 'string', 'max:120'],
            'window_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'sla_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'reminder_enabled' => ['nullable', 'boolean'],
            'reminder_frequency_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'reminder_channels' => ['nullable', 'array'],
            'reminder_channels.*' => ['string', 'in:email,in_app'],
            'notify_recipient' => ['nullable', 'boolean'],
            'notify_staff' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function normalizeRoles(array $roles): array
    {
        $filtered = array_values(array_filter($roles, function ($role) {
            return in_array($role, $this->allowedRoles, true);
        }));
        return $filtered ?: $this->allowedRoles;
    }

    private function logChange(PickupRule $rule, ?int $userId, string $action, ?array $before, ?array $after): void
    {
        PickupRuleAuditLog::create([
            'pickup_rule_id' => $rule->id,
            'user_id' => $userId,
            'action' => $action,
            'before_json' => $before,
            'after_json' => $after,
        ]);
    }

    private function isBreach(PickupRule $rule): bool
    {
        return (int) ($rule->window_hours ?? 0) > (int) ($rule->sla_hours ?? 0);
    }

    private function assertCanEdit(Request $request, ?PickupRule $rule = null): void
    {
        if ($rule) {
            $this->assertOwnedRule($request, $rule);
        }

        $role = $request->user()->role ?? 'staff';
        if ($role === 'admin' || $role === 'manager') {
            return;
        }
        if (!$rule) {
            abort(403, 'Unauthorized');
        }
        $allowed = $rule->allowed_roles ?: [];
        if (!in_array($role, $allowed, true)) {
            abort(403, 'Unauthorized');
        }
    }

    private function assertOwnedRule(Request $request, PickupRule $rule): void
    {
        if ($rule->user_id !== $request->user()->id) {
            abort(404);
        }
    }

    private function assertExternalLockerPlan(Request $request, ?string $type): void
    {
        if ($type !== 'external_locker') {
            return;
        }

        $currentPlan = strtolower(trim((string) ($request->user()->selected_plan ?: config('plan_features.default_plan', 'standard'))));
        $plans = config('plan_features.plans', []);
        $currentRank = (int) ($plans[$currentPlan] ?? 0);
        $enterpriseRank = (int) ($plans['enterprise'] ?? PHP_INT_MAX);

        if ($currentRank < $enterpriseRank) {
            abort(403, 'External locker pickup is available on the Enterprise plan only.');
        }
    }
}
