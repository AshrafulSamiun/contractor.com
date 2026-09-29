<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_holders', function (Blueprint $table) {
            $table->string('payment_terms')->nullable();
            $table->string('invoice_terms')->nullable();
            $table->text('seller_notes')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('account_holders', function (Blueprint $table) {
            $table->dropColumn(['payment_terms','invoice_terms','seller_notes']);
        });
    }
};
