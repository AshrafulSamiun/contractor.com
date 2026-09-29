<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->string('report_no', 40)->nullable();
            $table->date('attendance_date')->index();
            $table->string('prepared_by', 120)->nullable();
            $table->string('staff_name', 120);
            $table->string('employee_id', 60)->nullable();
            $table->string('position_title', 120)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('location_site', 120)->nullable();
            $table->string('customer_job_site', 160)->nullable();
            $table->string('department', 120)->nullable();
            $table->string('shift_name', 80)->nullable();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->unsignedInteger('total_minutes')->default(0);
            $table->string('status', 30)->default('present');
            $table->boolean('overtime')->default(false);
            $table->boolean('late_arrival')->default(false);
            $table->boolean('early_departure')->default(false);
            $table->text('work_performed')->nullable();
            $table->text('notes')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'attendance_date']);
            $table->index(['project_id', 'staff_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workforce_staff_attendances');
    }
};
