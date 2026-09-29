<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('driver_code', 50)->unique();
            $table->string('driver_name', 150);
            $table->string('contact_number', 50);
            $table->string('email', 150)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_number', 50)->nullable();
            $table->string('license_number', 100)->unique();
            $table->date('license_expiry_date')->nullable();
            $table->string('license_class', 50)->nullable();
            $table->json('assigned_vehicle_ids')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('employment_type', 50)->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 = Active, 2 = Inactive, 3 = Suspended');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->unsignedBigInteger('inserted_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index('driver_code');
            $table->index('driver_name');
            $table->index('contact_number');
            $table->index('status');
            $table->index('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
