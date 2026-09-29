<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('account_setups')->cascadeOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('invoice_no', 40);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('customer_po_no')->nullable();
            $table->string('salesperson')->nullable();
            $table->string('department')->nullable();
            $table->char('currency_code', 3)->default('USD');
            $table->decimal('exchange_rate', 15, 6)->default(1);
            $table->string('price_list')->nullable();
            $table->string('sales_channel')->nullable();
            $table->string('delivery_location')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('tracking_no')->nullable();
            $table->string('shipping_terms')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('reference')->nullable();
            $table->string('status', 30)->default('Draft');
            $table->string('payment_status', 30)->default('Unpaid');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('sales_tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid', 15, 2)->default(0);
            $table->json('customer_details')->nullable();
            $table->json('items');
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->text('payment_information')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'invoice_no']);
        });

        Schema::create('sales_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('payment_no', 100);
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
            $table->unique(['sales_invoice_id', 'payment_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_payments');
        Schema::dropIfExists('sales_invoices');
    }
};
