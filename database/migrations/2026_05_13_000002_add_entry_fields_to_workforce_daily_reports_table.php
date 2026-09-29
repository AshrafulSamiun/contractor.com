<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workforce_daily_reports', function (Blueprint $table) {
            $table->string('employee_code', 60)->nullable()->after('employee_name');
            $table->string('employee_phone', 40)->nullable()->after('employee_code');
            $table->string('employee_email', 120)->nullable()->after('employee_phone');
            $table->json('details_json')->nullable()->after('metrics_json');
            $table->boolean('worked_on_stat_holiday')->default(false)->after('is_valid');
        });
    }

    public function down(): void
    {
        Schema::table('workforce_daily_reports', function (Blueprint $table) {
            $table->dropColumn([
                'employee_code',
                'employee_phone',
                'employee_email',
                'details_json',
                'worked_on_stat_holiday',
            ]);
        });
    }
};
