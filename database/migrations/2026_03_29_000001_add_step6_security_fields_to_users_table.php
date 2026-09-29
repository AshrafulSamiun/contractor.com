<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'security_pin_code')) {
                $table->string('security_pin_code', 255)->nullable()->after('password');
            }

            if (!Schema::hasColumn('users', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('security_pin_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'expiry_date')) {
                $table->dropColumn('expiry_date');
            }

            if (Schema::hasColumn('users', 'security_pin_code')) {
                $table->dropColumn('security_pin_code');
            }
        });
    }
};
