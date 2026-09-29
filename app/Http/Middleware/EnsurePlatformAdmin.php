<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || strtolower((string) $user->role) !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Super Admin access is required.',
            ], 403);
        }

        return $next($request);
    }
}
