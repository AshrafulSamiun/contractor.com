<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('billing_invoices', function (Blueprint $table) {
            $table->string('plan_name', 255)->nullable()->after('amount_remaining');
            $table->integer('tax_amount')->nullable()->after('plan_name');
        });
    }

    public function down(): void
    {
        Schema::table('billing_invoices', function (Blueprint $table) {
            $table->dropColumn(['plan_name', 'tax_amount']);
        });
    }
};
