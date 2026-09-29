<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workforce_daily_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('user_id');
            $table->index(['project_id', 'report_date'], 'workforce_daily_reports_project_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('workforce_daily_reports', function (Blueprint $table) {
            $table->dropIndex('workforce_daily_reports_project_date_index');
            $table->dropColumn('project_id');
        });
    }
};
