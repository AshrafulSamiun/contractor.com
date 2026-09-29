<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('po_no', 40);
            $table->string('seller_no', 40)->nullable();
            $table->string('seller_name');
            $table->json('seller_details')->nullable();
            $table->string('requested_by')->nullable();
            $table->string('department')->nullable();
            $table->string('project')->nullable();
            $table->date('po_date');
            $table->dateTime('expiry_at')->nullable();
            $table->dateTime('expected_delivery_at')->nullable();
            $table->string('delivery_location')->nullable();
            $table->json('delivery_details')->nullable();
            $table->string('currency_code', 12)->default('USD');
            $table->string('status', 30)->default('Pending');
            $table->string('payment_status', 30)->default('Unpaid');
            $table->string('payment_term')->nullable();
            $table->string('payment_method')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('sales_tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->json('items')->nullable();
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'po_no']);
            $table->index(['project_id', 'status', 'po_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('purchase_orders'); }
};
