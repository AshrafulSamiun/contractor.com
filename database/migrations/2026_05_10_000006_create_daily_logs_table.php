<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_code', 50)->unique();
            $table->date('log_date');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('driver_id');
            $table->string('start_time', 10);
            $table->string('end_time', 10)->nullable();
            $table->unsignedInteger('start_odometer');
            $table->unsignedInteger('end_odometer')->nullable();
            $table->unsignedInteger('miles_driven')->nullable();
            $table->decimal('fuel_added', 10, 2)->nullable();
            $table->string('purpose_location', 255)->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 = Active, 2 = Completed, 3 = Cancelled');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->unsignedBigInteger('inserted_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->index('log_date');
            $table->index('status');
            $table->index('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
