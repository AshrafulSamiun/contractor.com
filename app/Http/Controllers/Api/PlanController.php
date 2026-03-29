<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanChangeLog;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function select(Request $request)
    {
        $data = $request->validate([
            'plan' => 'required|in:basic,standard,enterprise',
        ]);

        $user = $request->user();
        $fromPlan = $user->selected_plan;
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
}
