<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\PlanChangeLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function current(Request $request)
    {
        $setup = $this->projectSetup($request);
        $billing = $this->billingOverview($setup);

        return response()->json([
            'success' => true,
            'data' => [
                'account_setup_id' => $setup->id,
                'subscription_plan' => $setup->subscription_plan,
                'billing_cycle' => $setup->billing_cycle,
                'monthly_payment' => $billing['amount'],
                'last_payment_date' => $billing['last_payment_date'],
                'service_charge_date' => $billing['service_charge_date'],
                'next_payment_date' => $billing['next_payment_date'],
                'updated_at' => optional($setup->updated_at)->toDateString(),
            ],
        ]);
    }

    public function select(Request $request)
    {
        $data = $request->validate([
            'plan' => 'required|in:basic,pro,premium,enterprise',
        ]);

        $user = $request->user();
        $setup = $this->projectSetup($request);
        $fromPlan = $setup->subscription_plan;
        $setup->subscription_plan = $data['plan'];
        $setup->save();

        $user->selected_plan = $data['plan'];
        $user->save();

        if ($fromPlan !== $user->selected_plan) {
            PlanChangeLog::create([
                'user_id' => $user->id,
                'from_plan' => $fromPlan,
                'to_plan' => $user->selected_plan,
                'changed_by_user_id' => $user->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'selected_plan' => $user->selected_plan,
            ],
        ]);
    }

    private function projectSetup(Request $request): AccountSetup
    {
        $projectId = (int) $request->user()->project_id;

        abort_if($projectId < 1, 404, 'No account setup is assigned to this project.');

        return AccountSetup::query()->findOrFail($projectId);
    }

    private function billingOverview(AccountSetup $setup): array
    {
        $items = data_get($setup->data, 'setup_form.payment_schedule_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        $items = collect($items)
            ->filter(fn ($item) => is_array($item) && ! empty($item['auto_charge_date']))
            ->sortBy('auto_charge_date')
            ->values();

        $today = now()->startOfDay();
        $past = $items->filter(fn ($item) => Carbon::parse($item['auto_charge_date'])->startOfDay()->lte($today));
        $future = $items->filter(fn ($item) => Carbon::parse($item['auto_charge_date'])->startOfDay()->gt($today))->values();
        $current = $future->first();
        $next = $future->get(1);

        return [
            'amount' => $current['amount'] ?? data_get($setup->data, 'setup_form.payment_schedule_amount'),
            'last_payment_date' => data_get($past->last(), 'auto_charge_date'),
            'service_charge_date' => data_get($current, 'auto_charge_date'),
            'next_payment_date' => data_get($next, 'auto_charge_date'),
        ];
    }
}
