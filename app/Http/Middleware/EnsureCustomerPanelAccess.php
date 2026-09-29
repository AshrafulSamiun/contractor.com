<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCustomerPanelAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $isSuperAdmin = strtolower(trim((string) $user?->role)) === 'super_admin';

        // These endpoints are needed to restore or end any authenticated session.
        $isSessionEndpoint = $request->is('api/v1/me') || $request->is('api/v1/logout');
        $isSuperAdminEndpoint = $request->is('api/v1/super-admin/*');

        if ($isSuperAdmin && ! $isSessionEndpoint && ! $isSuperAdminEndpoint) {
            return response()->json([
                'success' => false,
                'message' => 'Super Admin accounts cannot access the Customer Admin panel.',
            ], 403);
        }

        return $next($request);
    }
}
