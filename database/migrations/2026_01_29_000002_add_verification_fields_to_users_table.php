<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('email_otp', 10)->nullable()->after('verify_via');
            $table->string('phone_otp', 10)->nullable()->after('email_otp');
            $table->timestamp('otp_expires_at')->nullable()->after('phone_otp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_verified_at',
                'email_otp',
                'phone_otp',
                'otp_expires_at',
            ]);
        });
    }
};
