<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_setups', function (Blueprint $table) {
            $table->string('primary_cardholder_kind', 20)->nullable()->after('primary_cardholder_name');
            $table->string('backup_cardholder_kind', 20)->nullable()->after('backup_cardholder_name');
        });
    }

    public function down(): void
    {
        Schema::table('account_setups', function (Blueprint $table) {
            $table->dropColumn(['primary_cardholder_kind', 'backup_cardholder_kind']);
        });
    }
};
