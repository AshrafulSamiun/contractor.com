<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            if (!Schema::hasColumn('calendar_events', 'recurrence_freq')) {
                $table->string('recurrence_freq', 20)->nullable()->after('visibility');
            }
            if (!Schema::hasColumn('calendar_events', 'recurrence_interval')) {
                $table->unsignedInteger('recurrence_interval')->nullable()->after('recurrence_freq');
            }
            if (!Schema::hasColumn('calendar_events', 'recurrence_until')) {
                $table->date('recurrence_until')->nullable()->after('recurrence_interval');
            }
            if (!Schema::hasColumn('calendar_events', 'reminders_json')) {
                $table->json('reminders_json')->nullable()->after('recurrence_until');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            if (Schema::hasColumn('calendar_events', 'reminders_json')) {
                $table->dropColumn('reminders_json');
            }
            if (Schema::hasColumn('calendar_events', 'recurrence_until')) {
                $table->dropColumn('recurrence_until');
            }
            if (Schema::hasColumn('calendar_events', 'recurrence_interval')) {
                $table->dropColumn('recurrence_interval');
            }
            if (Schema::hasColumn('calendar_events', 'recurrence_freq')) {
                $table->dropColumn('recurrence_freq');
            }
        });
    }
};
