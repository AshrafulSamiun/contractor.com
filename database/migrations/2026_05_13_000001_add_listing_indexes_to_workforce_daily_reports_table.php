<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workforce_daily_reports', function (Blueprint $table) {
            $table->index('report_no', 'wdr_report_no_idx');
            $table->index('employee_name', 'wdr_employee_name_idx');
            $table->index('status', 'wdr_status_idx');
            $table->index(['user_id', 'status', 'report_date'], 'wdr_user_status_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('workforce_daily_reports', function (Blueprint $table) {
            $table->dropIndex('wdr_report_no_idx');
            $table->dropIndex('wdr_employee_name_idx');
            $table->dropIndex('wdr_status_idx');
            $table->dropIndex('wdr_user_status_date_idx');
        });
    }
};
