<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seller_debit_notes')) Schema::create('seller_debit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('account_setups')->cascadeOnDelete();
            $table->foreignId('purchase_invoice_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('debit_note_no', 40);
            $table->date('debit_note_date');
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('Draft');
            $table->string('settlement_status', 30)->default('Open');
            $table->string('reason', 255);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('sales_tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->decimal('settled', 15, 2)->default(0);
            $table->json('items');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'debit_note_no']);
        });

        if (! Schema::hasTable('seller_debit_note_settlements')) Schema::create('seller_debit_note_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_debit_note_id')->constrained()->cascadeOnDelete();
            $table->string('settlement_no', 100);
            $table->date('settlement_date');
            $table->decimal('amount', 15, 2);
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['seller_debit_note_id', 'settlement_no']);
        });

        if (! Schema::hasTable('seller_debit_note_activities')) Schema::create('seller_debit_note_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_debit_note_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('actor');
            $table->string('action');
            $table->json('changes')->nullable();
            $table->timestamps();
        });

        if (! Schema::hasTable('seller_debit_note_attachments')) Schema::create('seller_debit_note_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_debit_note_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_debit_note_attachments');
        Schema::dropIfExists('seller_debit_note_activities');
        Schema::dropIfExists('seller_debit_note_settlements');
        Schema::dropIfExists('seller_debit_notes');
    }
};
