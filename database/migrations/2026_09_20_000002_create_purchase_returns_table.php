<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('account_setups')->cascadeOnDelete();
            $table->foreignId('purchase_invoice_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('return_no', 40);
            $table->date('return_date');
            $table->date('return_due_date')->nullable();
            $table->string('status', 30)->default('Draft');
            $table->string('refund_status', 30)->default('Unpaid');
            $table->string('reason', 255);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('sales_tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->decimal('refunded', 15, 2)->default(0);
            $table->json('items');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'return_no']);
        });

        Schema::create('purchase_return_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
            $table->string('refund_no', 100);
            $table->date('refund_date');
            $table->decimal('amount', 15, 2);
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['purchase_return_id', 'refund_no']);
        });

        Schema::create('purchase_return_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('actor');
            $table->string('action');
            $table->json('changes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_return_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_attachments');
        Schema::dropIfExists('purchase_return_activities');
        Schema::dropIfExists('purchase_return_refunds');
        Schema::dropIfExists('purchase_returns');
    }
};
