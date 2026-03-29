<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_settings', 'quiet_hours_schedule')) {
                $table->json('quiet_hours_schedule')->nullable()->after('quiet_hours_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_settings', 'quiet_hours_schedule')) {
                $table->dropColumn('quiet_hours_schedule');
            }
        });
    }
};
