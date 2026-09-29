<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_orders')) Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('account_setups')->cascadeOnDelete();
            $table->unsignedInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('estimation_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('order_no', 40);
            $table->date('order_date');
            $table->date('valid_until')->nullable();
            $table->string('customer_po_no')->nullable();
            $table->string('salesperson')->nullable();
            $table->string('department')->nullable();
            $table->char('currency_code', 3)->default('CAD');
            $table->decimal('exchange_rate', 15, 6)->default(1);
            $table->string('price_list')->nullable();
            $table->string('sales_channel')->nullable();
            $table->string('delivery_location')->nullable();
            $table->string('shipping_method')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->string('shipping_terms')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('reference')->nullable();
            $table->string('status', 30)->default('Draft');
            $table->json('customer_details');
            $table->json('items');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('sales_tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'order_no']);
        });
        if (! Schema::hasTable('sales_order_activities')) Schema::create('sales_order_activities', function (Blueprint $table) {
            $table->id(); $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable(); $table->string('actor'); $table->string('action');
            $table->json('changes')->nullable(); $table->timestamps();
        });
        if (! Schema::hasTable('sales_order_attachments')) Schema::create('sales_order_attachments', function (Blueprint $table) {
            $table->id(); $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->string('name'); $table->string('path'); $table->unsignedBigInteger('size'); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_attachments');
        Schema::dropIfExists('sales_order_activities');
        Schema::dropIfExists('sales_orders');
    }
};
