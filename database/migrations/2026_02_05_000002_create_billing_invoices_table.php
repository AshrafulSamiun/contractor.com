<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_invoice_id', 120)->unique();
            $table->string('stripe_subscription_id', 120)->nullable();
            $table->string('status', 40)->nullable();
            $table->string('currency', 10)->nullable();
            $table->integer('amount_due')->nullable();
            $table->integer('amount_paid')->nullable();
            $table->integer('amount_remaining')->nullable();
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->string('hosted_invoice_url', 500)->nullable();
            $table->string('invoice_pdf', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_invoices');
    }
};
