<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_order_daily_work_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_order_id');
            $table->string('log_no', 50)->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('project_manager', 255)->nullable();
            $table->decimal('total_wages', 15, 2)->default(0);
            $table->decimal('total_materials', 15, 2)->default(0);
            $table->decimal('total_overhead', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('job_order_id')->references('id')->on('job_orders')->cascadeOnDelete();
            $table->index(['job_order_id', 'period_start']);
        });

        Schema::create('job_order_daily_work_log_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daily_work_log_id');
            $table->date('work_date');
            $table->string('day_name', 20)->nullable();
            $table->string('start_time', 5)->nullable();
            $table->string('end_time', 5)->nullable();
            $table->string('activity_description', 1000)->nullable();
            $table->decimal('wages', 15, 2)->default(0);
            $table->decimal('materials', 15, 2)->default(0);
            $table->decimal('overhead', 15, 2)->default(0);
            $table->decimal('daily_total', 15, 2)->default(0);
            $table->decimal('running_total', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('daily_work_log_id')->references('id')->on('job_order_daily_work_logs')->cascadeOnDelete();
            $table->index(['daily_work_log_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order_daily_work_log_entries');
        Schema::dropIfExists('job_order_daily_work_logs');
    }
};
