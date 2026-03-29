<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_settings')) {
            return;
        }

        if (Schema::hasColumn('user_settings', 'theme_mode')) {
            DB::table('user_settings')
                ->whereNull('theme_mode')
                ->orWhere('theme_mode', 'system')
                ->update(['theme_mode' => 'light']);
        }

        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE user_settings MODIFY theme_mode VARCHAR(10) NOT NULL DEFAULT 'light'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE user_settings ALTER COLUMN theme_mode SET DEFAULT 'light'");
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE user_settings ADD DEFAULT 'light' FOR theme_mode");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_settings') || !Schema::hasColumn('user_settings', 'theme_mode')) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE user_settings MODIFY theme_mode VARCHAR(10) NOT NULL DEFAULT 'system'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE user_settings ALTER COLUMN theme_mode SET DEFAULT 'system'");
            return;
        }
    }
};
