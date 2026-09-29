<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE workforce_incident_reports AS reports INNER JOIN users AS users ON users.id = reports.user_id SET reports.project_id = users.project_id WHERE reports.project_id IS NULL');
    }

    public function down(): void
    {
        // Project ownership is historical data and must not be removed on rollback.
    }
};
