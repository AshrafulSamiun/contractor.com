<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_setups', function (Blueprint $table) {
            $table->string('primary_cardholder_name', 150)->nullable();
            $table->string('primary_card_last_four', 4)->nullable();
            $table->string('primary_card_type', 30)->nullable();
            $table->string('primary_card_expiry', 10)->nullable();
            $table->string('backup_cardholder_name', 150)->nullable();
            $table->string('backup_card_last_four', 4)->nullable();
            $table->string('backup_card_type', 30)->nullable();
            $table->string('backup_card_expiry', 10)->nullable();
            $table->boolean('payment_method_ack')->default(false);
            $table->timestamp('payment_method_acknowledged_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('account_setups', function (Blueprint $table) {
            $table->dropColumn([
                'primary_cardholder_name', 'primary_card_last_four', 'primary_card_type', 'primary_card_expiry',
                'backup_cardholder_name', 'backup_card_last_four', 'backup_card_type', 'backup_card_expiry',
                'payment_method_ack', 'payment_method_acknowledged_at',
            ]);
        });
    }
};
