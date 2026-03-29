<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $today = Carbon::today();
        $parcels = Parcel::query()->where('user_id', $userId);

        $totalParcels = (clone $parcels)->count();
        $pendingParcels = (clone $parcels)->where('status', 'pending')->count();
        $deliveredToday = (clone $parcels)->whereDate('delivered_at', $today)->count();
        $alerts = (clone $parcels)->whereIn('status', ['held', 'returned'])->count();

        $weekly = collect(range(6, 0))->map(function ($offset) use ($userId) {
            $date = Carbon::today()->subDays($offset);
            return [
                'date' => $date->format('Y-m-d'),
                'count' => Parcel::query()
                    ->where('user_id', $userId)
                    ->whereDate('received_at', $date)
                    ->count(),
            ];
        });

        $recent = (clone $parcels)->orderByDesc('created_at')->limit(5)->get();

        $locations = (clone $parcels)->selectRaw('facility_name, count(*) as total')
            ->whereNotNull('facility_name')
            ->groupBy('facility_name')
            ->orderByDesc('total')
            ->limit(4)
            ->get();

        $emailSent = NotificationLog::where('user_id', $userId)->where('channel', 'email')->where('status', 'sent')->count();
        $smsSent = NotificationLog::where('user_id', $userId)->where('channel', 'sms')->where('status', 'sent')->count();
        $failed = NotificationLog::where('user_id', $userId)->where('status', 'failed')->count();

        $activeUsersQuery = User::query()->where('is_active', true);
        if (!empty($user->company_name)) {
            $activeUsersQuery->where('company_name', $user->company_name);
        } else {
            // Prevent cross-tenant leakage when company_name is not set.
            $activeUsersQuery->whereKey($userId);
        }
        $activeUsers = $activeUsersQuery->count();
        $planName = $user->selected_plan ?: 'standard';
        $nextBilling = Carbon::now()->endOfMonth()->format('M d, Y');

        return response()->json([
            'success' => true,
            'data' => [
                'kpis' => [
                    'total' => $totalParcels,
                    'pending' => $pendingParcels,
                    'delivered_today' => $deliveredToday,
                    'alerts' => $alerts,
                ],
                'weekly' => $weekly,
                'recent' => $recent,
                'locations' => $locations,
                'plan' => [
                    'name' => $planName,
                    'next_billing' => $nextBilling,
                    'active_users' => $activeUsers,
                ],
                'notifications' => [
                    'email_sent' => $emailSent,
                    'sms_sent' => $smsSent,
                    'failed' => $failed,
                    'system_alerts' => $alerts,
                ],
            ],
        ]);
    }
}
