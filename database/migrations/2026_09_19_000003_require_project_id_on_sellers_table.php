<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('sellers')->whereNull('project_id')->exists()) {
            throw new RuntimeException('Cannot require sellers.project_id while unassigned sellers exist.');
        }

        DB::statement('ALTER TABLE sellers MODIFY project_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE sellers MODIFY project_id BIGINT UNSIGNED NULL');
    }
};
