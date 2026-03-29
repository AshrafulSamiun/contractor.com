<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_logs', 'context_json')) {
                $table->text('context_json')->nullable()->after('context');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            if (Schema::hasColumn('notification_logs', 'context_json')) {
                $table->dropColumn('context_json');
            }
        });
    }
};
