<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

trait AuthorizesApiRequests
{
    protected function hasPermission(Request $request, string $module, string $action = 'read'): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        return Gate::forUser($user)->allows('permission', [$module, $action]);
    }

    protected function denyUnlessOwns(Request $request, mixed $resource): ?JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (!Gate::forUser($user)->allows('owns-resource', $resource)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return null;
    }

    protected function denyUnlessPermitted(Request $request, string $module, string $action = 'read'): ?JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (!Gate::forUser($user)->allows('permission', [$module, $action])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
                'data' => [
                    'module' => $module,
                    'action' => $action,
                ],
            ], 403);
        }

        return null;
    }
}
