<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bill_payments')) Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('account_setups')->cascadeOnDelete();
            $table->unsignedBigInteger('seller_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('payment_no', 40);
            $table->date('payment_date');
            $table->string('status', 30)->default('Draft');
            $table->string('payment_method')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('reference_no')->nullable();
            $table->char('currency_code', 3)->default('CAD');
            $table->decimal('amount', 15, 2);
            $table->decimal('write_off', 15, 2)->default(0);
            $table->string('write_off_account')->nullable();
            $table->boolean('take_discount')->default(false);
            $table->boolean('group_by_invoice')->default(false);
            $table->date('discount_date')->nullable();
            $table->text('memo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'payment_no']);
        });

        if (! Schema::hasTable('bill_payment_allocations')) Schema::create('bill_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_invoice_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            $table->unique(['bill_payment_id', 'purchase_invoice_id']);
        });

        if (! Schema::hasTable('bill_payment_activities')) Schema::create('bill_payment_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_payment_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('actor');
            $table->string('action');
            $table->json('changes')->nullable();
            $table->timestamps();
        });

        if (! Schema::hasTable('bill_payment_attachments')) Schema::create('bill_payment_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_payment_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payment_attachments');
        Schema::dropIfExists('bill_payment_activities');
        Schema::dropIfExists('bill_payment_allocations');
        Schema::dropIfExists('bill_payments');
    }
};
