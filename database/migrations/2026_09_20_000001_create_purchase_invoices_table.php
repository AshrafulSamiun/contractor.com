<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('account_setups')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('seller_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('invoice_no', 40);
            $table->string('po_no', 40)->nullable();
            $table->string('seller_no', 40)->nullable();
            $table->string('seller_name');
            $table->json('seller_details')->nullable();
            $table->string('requested_by')->nullable();
            $table->string('department')->nullable();
            $table->string('project')->nullable();
            $table->json('requester_details')->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('delivery_location')->nullable();
            $table->json('delivery_details')->nullable();
            $table->char('currency_code', 3)->default('CAD');
            $table->string('status', 30)->default('Draft');
            $table->string('payment_status', 30)->default('Unpaid');
            $table->string('invoice_type', 30)->default('Regular');
            $table->string('payment_term', 100)->nullable();
            $table->string('payment_method')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('sales_tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid', 15, 2)->default(0);
            $table->json('items');
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'invoice_no']);
        });

        Schema::create('purchase_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('payment_no', 100);
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['purchase_invoice_id', 'payment_no']);
        });

        Schema::create('purchase_invoice_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('actor');
            $table->string('action');
            $table->json('changes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_invoice_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_attachments');
        Schema::dropIfExists('purchase_invoice_activities');
        Schema::dropIfExists('purchase_invoice_payments');
        Schema::dropIfExists('purchase_invoices');
    }
};
