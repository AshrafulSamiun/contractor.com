<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_settings', 'theme_mode')) {
                $table->string('theme_mode', 10)->default('system')->after('week_start');
            }
            if (!Schema::hasColumn('user_settings', 'theme_accent')) {
                $table->string('theme_accent', 20)->default('blue')->after('theme_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_settings', 'theme_accent')) {
                $table->dropColumn('theme_accent');
            }
            if (Schema::hasColumn('user_settings', 'theme_mode')) {
                $table->dropColumn('theme_mode');
            }
        });
    }
};
