<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationLogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $companyName = trim((string) $user->company_name);

        $query = NotificationLog::query()->orderByDesc('created_at');
        if ($companyName !== '') {
            $companyUserIds = User::query()
                ->where('company_name', $companyName)
                ->pluck('id');
            $query->whereIn('user_id', $companyUserIds);
        } else {
            $query->where('user_id', $user->id);
        }
        if ($request->filled('channel')) {
            $query->where('channel', $request->string('channel')->toString());
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->string('to')->toString());
        }
        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('to', 'like', "%{$term}%")
                    ->orWhere('context', 'like', "%{$term}%")
                    ->orWhere('error', 'like', "%{$term}%")
                    ->orWhere('channel', 'like', "%{$term}%")
                    ->orWhere('status', 'like', "%{$term}%");
            });
        }
        if ($request->filled('event')) {
            $event = $request->string('event')->toString();
            $query->where('context_json', 'like', '%"status":"' . $event . '"%');
        }

        $logs = $query->limit(100)->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
