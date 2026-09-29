<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->string('card_no', 60);
            $table->string('card_nickname');
            $table->text('card_number'); // encrypted; never exposed by the API
            $table->string('card_last_four', 4);
            $table->string('cardholder_name');
            $table->string('card_type', 50);
            $table->string('card_network', 50);
            $table->string('expiry_date', 5);
            $table->decimal('credit_limit', 15, 2);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->unsignedTinyInteger('billing_day');
            $table->unsignedTinyInteger('due_day');
            $table->unsignedTinyInteger('pay_day');
            $table->boolean('status_active')->default(true)->index();
            $table->string('card_category', 50)->default('Corporate');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignId('bank_profile_id')->nullable()->constrained('bank_profiles')->nullOnDelete();
            $table->unsignedBigInteger('account_holder_id')->nullable()->index();
            $table->decimal('annual_fee', 15, 2)->default(0);
            $table->unsignedTinyInteger('payment_due_day')->nullable();
            $table->unsignedSmallInteger('grace_period_days')->nullable();
            $table->decimal('minimum_payment_percent', 5, 2)->nullable();
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->string('billing_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 30)->nullable();
            $table->unsignedBigInteger('country_id')->nullable()->index();
            $table->string('phone_number', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('department')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->text('notes')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['project_id', 'card_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_cards');
    }
};
