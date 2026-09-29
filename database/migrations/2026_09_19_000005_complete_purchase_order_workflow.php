<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('seller_id')->nullable()->index();
            $table->json('requester_details')->nullable();
            $table->string('approval_status', 30)->default('Draft');
        });
        Schema::table('sellers', function (Blueprint $table) {
            $table->text('address')->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('vendor_category', 100)->nullable();
        });
        Schema::create('purchase_order_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('number', 100);
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('sales_tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->json('items')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['purchase_order_id', 'number']);
        });
        Schema::create('purchase_order_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('actor');
            $table->string('action');
            $table->json('changes')->nullable();
            $table->timestamps();
        });
        Schema::create('purchase_order_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_attachments');
        Schema::dropIfExists('purchase_order_activities');
        Schema::dropIfExists('purchase_order_documents');
        Schema::table('sellers', fn (Blueprint $table) => $table->dropColumn(['address', 'tax_number', 'vendor_category']));
        Schema::table('purchase_orders', fn (Blueprint $table) => $table->dropColumn(['seller_id', 'requester_details', 'approval_status']));
    }
};
