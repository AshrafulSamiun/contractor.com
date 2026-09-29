<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workforce_incident_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('user_id')->index();
            $table->index(['project_id', 'occurred_at'], 'workforce_incidents_project_occurred_index');
        });
    }

    public function down(): void
    {
        Schema::table('workforce_incident_reports', function (Blueprint $table) {
            $table->dropIndex('workforce_incidents_project_occurred_index');
            $table->dropColumn('project_id');
        });
    }
};
