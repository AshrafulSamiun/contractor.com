<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code', 50)->unique();
            $table->date('report_date')->nullable();
            $table->string('created_by_name', 150)->nullable();
            $table->string('incident_report_no', 100)->nullable();
            $table->string('incident_report_name', 255)->nullable();
            $table->string('accident_reference', 100)->nullable();
            $table->date('incident_date');
            $table->string('incident_time', 10)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('accident_type', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('at_fault_name', 150)->nullable();
            $table->string('at_fault_position', 100)->nullable();
            $table->string('repair_shop', 150)->nullable();
            $table->string('invoice_no', 100)->nullable();
            $table->date('invoice_date')->nullable();
            $table->decimal('subtotal_repair_cost', 15, 2)->nullable();
            $table->decimal('sales_tax_rate', 5, 2)->default(13.00);
            $table->decimal('sales_tax_amount', 15, 2)->nullable();
            $table->decimal('total_cost', 15, 2)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->boolean('is_paid')->default(false);
            $table->string('paid_by', 150)->nullable();
            $table->decimal('amount_paid_by_company', 15, 2)->nullable();
            $table->decimal('amount_paid_by_insurance', 15, 2)->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 = Open, 2 = Closed, 3 = Pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->unsignedBigInteger('inserted_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->nullOnDelete();
            $table->index('report_code');
            $table->index('incident_report_no');
            $table->index('accident_type');
            $table->index('status');
            $table->index('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accident_reports');
    }
};
