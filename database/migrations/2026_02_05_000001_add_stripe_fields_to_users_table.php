<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id', 120)->nullable()->after('selected_plan');
            $table->string('stripe_subscription_id', 120)->nullable()->after('stripe_customer_id');
            $table->string('stripe_subscription_status', 50)->nullable()->after('stripe_subscription_id');
            $table->string('stripe_payment_method_id', 120)->nullable()->after('stripe_subscription_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_customer_id',
                'stripe_subscription_id',
                'stripe_subscription_status',
                'stripe_payment_method_id',
            ]);
        });
    }
};
