<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_settings', 'holding_default_days')) {
                $table->unsignedInteger('holding_default_days')->default(7)->after('theme_accent');
            }
            if (!Schema::hasColumn('user_settings', 'holding_max_days')) {
                $table->unsignedInteger('holding_max_days')->default(30)->after('holding_default_days');
            }
            if (!Schema::hasColumn('user_settings', 'holding_reminder_days')) {
                $table->unsignedInteger('holding_reminder_days')->default(2)->after('holding_max_days');
            }
            if (!Schema::hasColumn('user_settings', 'holding_auto_expire')) {
                $table->boolean('holding_auto_expire')->default(true)->after('holding_reminder_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_settings', 'holding_auto_expire')) {
                $table->dropColumn('holding_auto_expire');
            }
            if (Schema::hasColumn('user_settings', 'holding_reminder_days')) {
                $table->dropColumn('holding_reminder_days');
            }
            if (Schema::hasColumn('user_settings', 'holding_max_days')) {
                $table->dropColumn('holding_max_days');
            }
            if (Schema::hasColumn('user_settings', 'holding_default_days')) {
                $table->dropColumn('holding_default_days');
            }
        });
    }
};
