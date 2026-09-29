<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 50)->unique();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('driver_id');
            $table->string('violation_type', 100);
            $table->string('issued_by', 150)->nullable();
            $table->decimal('fine_amount', 15, 2)->nullable();
            $table->unsignedInteger('points')->default(0);
            $table->string('ticket_scope', 50)->default('Business');
            $table->tinyInteger('status')->default(1)->comment('1 = Pending, 2 = Paid, 3 = Overdue, 4 = Disputed');
            $table->boolean('is_paid')->default(false);
            $table->string('payment_method', 50)->nullable();
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->unsignedBigInteger('inserted_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->index('ticket_code');
            $table->index('status');
            $table->index('violation_type');
            $table->index('is_paid');
            $table->index('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_tickets');
    }
};
