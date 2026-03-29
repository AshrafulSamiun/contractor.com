<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique()->after('name');
            $table->string('company_name')->nullable()->after('username');
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('country', 100)->nullable()->after('phone');
            $table->string('postal_code', 30)->nullable()->after('country');
            $table->string('verify_via', 10)->nullable()->after('postal_code');
            $table->string('avatar_url')->nullable()->after('verify_via');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'company_name',
                'phone',
                'country',
                'postal_code',
                'verify_via',
                'avatar_url',
            ]);
        });
    }
};
