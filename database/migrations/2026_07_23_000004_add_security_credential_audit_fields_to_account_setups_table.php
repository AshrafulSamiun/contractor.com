<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_setups', function (Blueprint $table) {
            $table->timestamp('security_credentials_expires_at')->nullable();
            $table->string('mfa_email', 255)->nullable();
            $table->string('mfa_phone', 40)->nullable();
            $table->timestamp('mfa_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('account_setups', function (Blueprint $table) {
            $table->dropColumn(['security_credentials_expires_at', 'mfa_email', 'mfa_phone', 'mfa_verified_at']);
        });
    }
};
