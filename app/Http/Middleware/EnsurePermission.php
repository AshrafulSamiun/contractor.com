<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $module, string $action = 'read')
    {
        if (!(bool) config('permissions.enforce', false)) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $permissionService = app(PermissionService::class);
        if (!$permissionService->can($user, $module, $action)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
                'data' => [
                    'module' => $module,
                    'action' => $action,
                ],
            ], 403);
        }

        return $next($request);
    }
}
