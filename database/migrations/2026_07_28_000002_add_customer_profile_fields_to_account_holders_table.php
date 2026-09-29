<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_holders', function (Blueprint $table) {
            $table->string('customer_type', 30)->nullable()->after('account_type');
            $table->unsignedInteger('total_invoices')->default(0)->after('customer_type');
            $table->decimal('total_outstanding', 15, 2)->default(0)->after('total_invoices');
            $table->decimal('current_balance', 15, 2)->default(0)->after('total_outstanding');
        });
    }

    public function down(): void
    {
        Schema::table('account_holders', function (Blueprint $table) {
            $table->dropColumn(['customer_type', 'total_invoices', 'total_outstanding', 'current_balance']);
        });
    }
};
