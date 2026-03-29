<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_settings', 'quiet_hours_days')) {
                $table->string('quiet_hours_days', 40)->nullable()->after('quiet_hours_end');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_settings', 'quiet_hours_days')) {
                $table->dropColumn('quiet_hours_days');
            }
        });
    }
};
