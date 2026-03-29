<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workforce_daily_reports', function (Blueprint $table) {
            $table->string('report_no', 40)->nullable()->after('user_id');
            $table->string('report_location', 120)->nullable()->after('report_no');
            $table->string('shift_name', 60)->nullable()->after('shift');
            $table->time('shift_time')->nullable()->after('shift_name');
            $table->text('report_notes')->nullable()->after('summary');
            $table->string('employee_name', 120)->nullable()->after('report_notes');
            $table->date('expire_date')->nullable()->after('employee_name');
            $table->string('licence_no', 60)->nullable()->after('expire_date');
            $table->boolean('is_valid')->default(true)->after('licence_no');
        });

        Schema::table('workforce_incident_reports', function (Blueprint $table) {
            $table->string('incident_no', 40)->nullable()->after('user_id');
            $table->string('jobsite', 120)->nullable()->after('incident_no');
            $table->string('shift_name', 60)->nullable()->after('jobsite');
            $table->string('incident_location', 255)->nullable()->after('location');
            $table->text('incident_notes')->nullable()->after('description');
            $table->string('employee_name', 120)->nullable()->after('incident_notes');
            $table->date('expire_date')->nullable()->after('employee_name');
            $table->string('license_no', 60)->nullable()->after('expire_date');
            $table->boolean('is_valid')->default(true)->after('license_no');
            $table->string('incident_type', 80)->nullable()->after('is_valid');
            $table->json('incident_categories')->nullable()->after('incident_type');
            $table->text('incident_description')->nullable()->after('incident_categories');
            $table->text('incident_damages')->nullable()->after('incident_description');
            $table->text('incident_injuries')->nullable()->after('incident_damages');
            $table->text('action_taken')->nullable()->after('incident_injuries');
            $table->json('involved_people')->nullable()->after('action_taken');
            $table->json('witness_people')->nullable()->after('involved_people');
            $table->boolean('police_called')->default(false)->after('witness_people');
            $table->string('police_file_no', 80)->nullable()->after('police_called');
            $table->string('police_officer_name', 120)->nullable()->after('police_file_no');
            $table->string('badge_no', 80)->nullable()->after('police_officer_name');
            $table->boolean('fire_dept_called')->default(false)->after('badge_no');
            $table->boolean('ambulance_called')->default(false)->after('fire_dept_called');
            $table->text('emergency_details')->nullable()->after('ambulance_called');
        });

        Schema::table('workforce_timesheets', function (Blueprint $table) {
            $table->string('timesheet_no', 40)->nullable()->after('user_id');
            $table->string('employee_name', 120)->nullable()->after('timesheet_no');
            $table->string('employee_code', 60)->nullable()->after('employee_name');
            $table->string('role_title', 80)->nullable()->after('employee_code');
            $table->date('week_end')->nullable()->after('week_start');
            $table->decimal('regular_hours', 6, 2)->default(0)->after('week_end');
            $table->decimal('overtime_hours', 6, 2)->default(0)->after('regular_hours');
            $table->string('approved_by', 120)->nullable()->after('status');
            $table->dateTime('approved_at')->nullable()->after('approved_by');
            $table->text('notes')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('workforce_daily_reports', function (Blueprint $table) {
            $table->dropColumn([
                'report_no',
                'report_location',
                'shift_name',
                'shift_time',
                'report_notes',
                'employee_name',
                'expire_date',
                'licence_no',
                'is_valid',
            ]);
        });

        Schema::table('workforce_incident_reports', function (Blueprint $table) {
            $table->dropColumn([
                'incident_no',
                'jobsite',
                'shift_name',
                'incident_location',
                'incident_notes',
                'employee_name',
                'expire_date',
                'license_no',
                'is_valid',
                'incident_type',
                'incident_categories',
                'incident_description',
                'incident_damages',
                'incident_injuries',
                'action_taken',
                'involved_people',
                'witness_people',
                'police_called',
                'police_file_no',
                'police_officer_name',
                'badge_no',
                'fire_dept_called',
                'ambulance_called',
                'emergency_details',
            ]);
        });

        Schema::table('workforce_timesheets', function (Blueprint $table) {
            $table->dropColumn([
                'timesheet_no',
                'employee_name',
                'employee_code',
                'role_title',
                'week_end',
                'regular_hours',
                'overtime_hours',
                'approved_by',
                'approved_at',
                'notes',
            ]);
        });
    }
};
