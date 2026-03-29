<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            if (!Schema::hasColumn('calendar_events', 'attendees_json')) {
                $table->json('attendees_json')->nullable()->after('reminders_json');
            }
            if (!Schema::hasColumn('calendar_events', 'exceptions_json')) {
                $table->json('exceptions_json')->nullable()->after('attendees_json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            if (Schema::hasColumn('calendar_events', 'exceptions_json')) {
                $table->dropColumn('exceptions_json');
            }
            if (Schema::hasColumn('calendar_events', 'attendees_json')) {
                $table->dropColumn('attendees_json');
            }
        });
    }
};
