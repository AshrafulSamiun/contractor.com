<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_code', 50)->unique();
            $table->string('vehicle_number', 100)->unique();
            $table->string('make_brand', 150);
            $table->string('model', 150);
            $table->unsignedSmallInteger('vehicle_year')->nullable();
            $table->string('color', 50)->nullable();
            $table->string('vin', 100)->nullable();
            $table->string('fuel_type', 50)->nullable();
            $table->string('plate_number', 100)->nullable();
            $table->date('plate_expiry_date')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->unsignedInteger('current_mileage')->nullable();
            $table->string('insurance_provider', 150)->nullable();
            $table->string('policy_number', 100)->nullable();
            $table->date('insurance_expiry_date')->nullable();
            $table->string('assigned_driver', 150)->nullable();
            $table->tinyInteger('status')->default(2)->comment('1 = In Use, 2 = Idle, 3 = Maintenance, 4 = Out of Service');
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->unsignedBigInteger('inserted_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index('vehicle_code');
            $table->index('vehicle_number');
            $table->index('status');
            $table->index('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
