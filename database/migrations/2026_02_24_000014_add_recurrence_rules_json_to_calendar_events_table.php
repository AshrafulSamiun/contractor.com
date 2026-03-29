<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            if (!Schema::hasColumn('calendar_events', 'recurrence_rules_json')) {
                $table->json('recurrence_rules_json')->nullable()->after('recurrence_days_json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            if (Schema::hasColumn('calendar_events', 'recurrence_rules_json')) {
                $table->dropColumn('recurrence_rules_json');
            }
        });
    }
};
