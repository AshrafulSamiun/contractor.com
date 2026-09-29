<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->string('insurance_code', 50)->unique();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('insurance_company', 150);
            $table->string('company_phone', 50)->nullable();
            $table->string('company_email', 150)->nullable();
            $table->string('company_address', 500)->nullable();
            $table->string('policy_number', 100)->unique();
            $table->string('coverage_type', 50)->nullable();
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('coverage_amount', 15, 2)->nullable();
            $table->decimal('deductible', 15, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->decimal('premium_amount', 15, 2)->nullable();
            $table->string('payment_frequency', 50)->nullable();
            $table->boolean('calendar_reminder')->default(false);
            $table->tinyInteger('status')->default(1)->comment('1 = Active, 2 = Expired, 3 = Pending, 4 = Cancelled');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->unsignedBigInteger('inserted_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->index('insurance_code');
            $table->index('policy_number');
            $table->index('status');
            $table->index('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_policies');
    }
};
