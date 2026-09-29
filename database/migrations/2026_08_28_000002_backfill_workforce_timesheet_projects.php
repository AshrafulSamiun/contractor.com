<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'UPDATE workforce_timesheets AS timesheets
             INNER JOIN users AS users ON users.id = timesheets.user_id
             SET timesheets.project_id = users.project_id
             WHERE timesheets.project_id IS NULL'
        );
    }

    public function down(): void
    {
        // Project ownership is required for access control and must not be removed.
    }
};
