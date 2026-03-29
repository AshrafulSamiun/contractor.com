<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanChangeLog;
use App\Models\User;
use Illuminate\Http\Request;

class PlanHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $companyName = trim((string) $user->company_name);

        $query = PlanChangeLog::query()->orderByDesc('created_at');
        if ($companyName !== '') {
            $companyUserIds = User::query()
                ->where('company_name', $companyName)
                ->pluck('id');
            $query->whereIn('user_id', $companyUserIds);
        } else {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit(100)->get(),
        ]);
    }
}
