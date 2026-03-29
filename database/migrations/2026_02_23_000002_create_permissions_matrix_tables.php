<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module', 60);
            $table->string('action', 60);
            $table->string('key', 140)->unique();
            $table->timestamps();

            $table->unique(['module', 'action']);
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role', 30)->index();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->boolean('allowed')->default(true);
            $table->timestamps();

            $table->unique(['role', 'permission_id']);
        });

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->boolean('allowed')->default(true);
            $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'permission_id']);
        });

        $this->seedDefaultPermissionRows();
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }

    private function seedDefaultPermissionRows(): void
    {
        $modules = config('permissions.modules', []);
        $now = now();

        $permissionRows = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissionRows[] = [
                    'module' => $module,
                    'action' => $action,
                    'key' => $module . '.' . $action,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($permissionRows) {
            DB::table('permissions')->insert($permissionRows);
        }

        $permissionRecords = DB::table('permissions')->get(['id', 'module', 'action']);
        $defaults = config('permissions.defaults', []);
        $roleRows = [];

        foreach ($defaults as $role => $map) {
            foreach ($permissionRecords as $permission) {
                $roleRows[] = [
                    'role' => $role,
                    'permission_id' => $permission->id,
                    'allowed' => $this->defaultAllowed($map, $permission->module, $permission->action),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($roleRows) {
            DB::table('role_permissions')->insert($roleRows);
        }
    }

    private function defaultAllowed(array $roleMap, string $module, string $action): bool
    {
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
};
