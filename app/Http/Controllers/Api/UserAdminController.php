<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->orderByDesc('created_at');
        $companyName = trim((string) $request->user()->company_name);
        if ($companyName !== '') {
            $query->where('company_name', $companyName);
        } else {
            $query->whereKey($request->user()->id);
        }
        $users = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', 'in:admin,manager,staff'],
        ]);

        if (($validated['role'] ?? null) === 'admin' && ($request->user()->role ?? null) !== 'admin') {
            abort(403, 'Only admin can create admin users.');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'staff',
            'is_active' => true,
            'company_name' => $request->user()->company_name,
            'project_id' => $request->user()->project_id,
        ]);

        app(ActivityLogService::class)->log(
            $request->user(),
            'users',
            'create',
            $user,
            'User account created.',
            [
                'email' => $user->email,
                'role' => $user->role,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $user,
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        if (!$this->isSameCompany($request, $user)) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'in:admin,manager,staff'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (($validated['role'] ?? null) === 'admin' && ($request->user()->role ?? null) !== 'admin') {
            abort(403, 'Only admin can assign admin role.');
        }

        $before = [
            'name' => $user->name,
            'role' => $user->role,
            'is_active' => (bool) $user->is_active,
        ];
        $user->update($validated);

        app(ActivityLogService::class)->log(
            $request->user(),
            'users',
            'edit',
            $user,
            'User profile updated.',
            [
                'before' => $before,
                'after' => [
                    'name' => $user->name,
                    'role' => $user->role,
                    'is_active' => (bool) $user->is_active,
                ],
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function permissions(Request $request, User $user)
    {
        if (!$this->isSameCompany($request, $user)) {
            abort(404);
        }

        $service = app(PermissionService::class);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'catalog' => $service->catalog(),
                'rows' => $service->rowsFor($user),
                'matrix' => $service->matrixFor($user),
            ],
        ]);
    }

    public function updatePermissions(Request $request, User $user)
    {
        if (!$this->isSameCompany($request, $user)) {
            abort(404);
        }
        if (($user->role ?? null) === 'admin' && ($request->user()->role ?? null) !== 'admin') {
            abort(403, 'Only admin can update admin permissions.');
        }

        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*.module' => ['required', 'string', 'max:60'],
            'permissions.*.action' => ['required', 'string', 'max:60'],
            'permissions.*.allowed' => ['required', 'boolean'],
        ]);

        $service = app(PermissionService::class);
        $service->updateUserOverrides($request->user(), $user, $validated['permissions']);

        app(ActivityLogService::class)->log(
            $request->user(),
            'users',
            'permissions',
            $user,
            'User permissions updated.',
            [
                'count' => count($validated['permissions']),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->fresh(),
                'catalog' => $service->catalog(),
                'rows' => $service->rowsFor($user->fresh()),
                'matrix' => $service->matrixFor($user->fresh()),
            ],
        ]);
    }

    protected function isSameCompany(Request $request, User $user): bool
    {
        $companyName = trim((string) $request->user()->company_name);
        if ($companyName === '') {
            return $user->id === $request->user()->id;
        }

        return trim((string) $user->company_name) === $companyName;
    }
}
