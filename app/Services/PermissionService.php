<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PermissionService
{
    public function can(User $user, string $module, string $action): bool
    {
        $row = $this->resolvedRow($user, $module, $action);

        return (bool) ($row['allowed'] ?? false);
    }

    public function catalog(): array
    {
        return config('permissions.modules', []);
    }

    public function matrixFor(User $user): array
    {
        $rows = $this->rowsFor($user);
        $matrix = [];
        foreach ($rows as $row) {
            $module = $row['module'];
            $action = $row['action'];
            $matrix[$module] ??= [];
            $matrix[$module][$action] = (bool) $row['allowed'];
        }

        return $matrix;
    }

    public function rowsFor(User $user): array
    {
        $modules = $this->catalog();
        $role = $this->normalizeRole($user->role);
        $useDb = $this->hasPermissionTables();
        $roleMap = [];
        $userMap = [];
        $permissionMap = [];

        if ($useDb) {
            $permissions = Permission::query()
                ->orderBy('module')
                ->orderBy('action')
                ->get(['id', 'module', 'action']);
            foreach ($permissions as $permission) {
                $permissionMap[$permission->module . '.' . $permission->action] = (int) $permission->id;
            }

            if ($permissionMap) {
                $permissionIds = array_values($permissionMap);

                $roleRows = RolePermission::query()
                    ->where('role', $role)
                    ->whereIn('permission_id', $permissionIds)
                    ->get(['permission_id', 'allowed']);
                foreach ($roleRows as $roleRow) {
                    $roleMap[(int) $roleRow->permission_id] = (bool) $roleRow->allowed;
                }

                $userRows = UserPermission::query()
                    ->where('user_id', $user->id)
                    ->whereIn('permission_id', $permissionIds)
                    ->get(['permission_id', 'allowed']);
                foreach ($userRows as $userRow) {
                    $userMap[(int) $userRow->permission_id] = (bool) $userRow->allowed;
                }
            }
        }

        $rows = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $source = 'default';
                $allowed = $this->defaultAllowed($role, $module, $action);

                if ($useDb) {
                    $key = $module . '.' . $action;
                    $permissionId = $permissionMap[$key] ?? null;
                    if ($permissionId !== null && array_key_exists($permissionId, $roleMap)) {
                        $allowed = (bool) $roleMap[$permissionId];
                        $source = 'role';
                    }
                    if ($permissionId !== null && array_key_exists($permissionId, $userMap)) {
                        $allowed = (bool) $userMap[$permissionId];
                        $source = 'user';
                    }
                }

                $rows[] = [
                    'module' => $module,
                    'action' => $action,
                    'allowed' => $allowed,
                    'source' => $source,
                ];
            }
        }

        return $rows;
    }

    public function updateUserOverrides(User $actor, User $target, array $permissions): void
    {
        if (!$this->hasPermissionTables()) {
            throw ValidationException::withMessages([
                'permissions' => ['Permission tables are not available. Run migrations first.'],
            ]);
        }

        $catalog = $this->catalog();
        $preparedRows = [];
        $now = now();

        foreach ($permissions as $index => $item) {
            $module = strtolower(trim((string) ($item['module'] ?? '')));
            $action = strtolower(trim((string) ($item['action'] ?? '')));
            $allowed = (bool) ($item['allowed'] ?? false);

            if ($module === '' || $action === '') {
                throw ValidationException::withMessages([
                    "permissions.{$index}" => ['Module and action are required.'],
                ]);
            }
            if (!isset($catalog[$module]) || !in_array($action, $catalog[$module], true)) {
                throw ValidationException::withMessages([
                    "permissions.{$index}" => ['Invalid module/action permission pair.'],
                ]);
            }

            $permission = Permission::query()->firstOrCreate(
                ['module' => $module, 'action' => $action],
                ['key' => $module . '.' . $action]
            );

            $preparedRows[] = [
                'user_id' => $target->id,
                'permission_id' => $permission->id,
                'allowed' => $allowed,
                'granted_by_user_id' => $actor->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($target, $preparedRows) {
            UserPermission::query()->where('user_id', $target->id)->delete();

            if ($preparedRows) {
                UserPermission::query()->insert($preparedRows);
            }
        });
    }

    private function resolvedRow(User $user, string $module, string $action): array
    {
        foreach ($this->rowsFor($user) as $row) {
            if ($row['module'] === $module && $row['action'] === $action) {
                return $row;
            }
        }

        return [
            'module' => $module,
            'action' => $action,
            'allowed' => false,
            'source' => 'none',
        ];
    }

    private function defaultAllowed(string $role, string $module, string $action): bool
    {
        $defaults = config('permissions.defaults', []);
        $roleMap = $defaults[$role] ?? [];
        $all = $roleMap['*'] ?? [];

        if (in_array('*', $all, true)) {
            return true;
        }

        $moduleActions = $roleMap[$module] ?? [];
        if (in_array('*', $moduleActions, true)) {
            return true;
        }

        return in_array($action, $moduleActions, true);
    }

    private function normalizeRole(?string $role): string
    {
        $value = strtolower(trim((string) $role));
        $defaults = config('permissions.defaults', []);

        if ($value !== '' && isset($defaults[$value])) {
            return $value;
        }

        return 'staff';
    }

    private function hasPermissionTables(): bool
    {
        return Schema::hasTable('permissions')
            && Schema::hasTable('role_permissions')
            && Schema::hasTable('user_permissions');
    }
}
