<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('account_setups')->cascadeOnDelete();
            $table->foreignId('sales_invoice_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('credit_note_no', 40);
            $table->date('credit_note_date');
            $table->string('status', 30)->default('Draft');
            $table->string('reason');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('sales_tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->json('items');
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'credit_note_no']);
        });

        Schema::create('customer_credit_note_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_credit_note_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('actor');
            $table->string('action');
            $table->json('changes')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_credit_note_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_credit_note_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_credit_note_attachments');
        Schema::dropIfExists('customer_credit_note_activities');
        Schema::dropIfExists('customer_credit_notes');
    }
};
