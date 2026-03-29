<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePlanFeature
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        if (!(bool) config('plan_features.enforce', false)) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $features = config('plan_features.features', []);
        if (!isset($features[$feature])) {
            // If a feature key is misconfigured, fail closed.
            return response()->json([
                'success' => false,
                'message' => 'Feature gate configuration is invalid.',
            ], 500);
        }

        $requiredPlan = $this->normalizePlan($features[$feature]);
        $currentPlan = $this->normalizePlan($user->selected_plan ?: config('plan_features.default_plan', 'standard'));

        if ($this->planRank($currentPlan) < $this->planRank($requiredPlan)) {
            return response()->json([
                'success' => false,
                'message' => 'Your current plan does not include this feature.',
                'data' => [
                    'feature' => $feature,
                    'current_plan' => $currentPlan,
                    'required_plan' => $requiredPlan,
                ],
            ], 403);
        }

        return $next($request);
    }

    private function planRank(string $plan): int
    {
        $plans = config('plan_features.plans', []);

        return (int) ($plans[$plan] ?? 0);
    }

    private function normalizePlan(?string $plan): string
    {
        $value = strtolower(trim((string) $plan));
        $plans = config('plan_features.plans', []);
        if ($value !== '' && isset($plans[$value])) {
            return $value;
        }

        return (string) config('plan_features.default_plan', 'standard');
    }
}
