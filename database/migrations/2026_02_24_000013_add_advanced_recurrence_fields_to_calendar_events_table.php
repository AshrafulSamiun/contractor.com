<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            if (!Schema::hasColumn('calendar_events', 'event_type')) {
                $table->string('event_type', 50)->nullable();
            }
            if (!Schema::hasColumn('calendar_events', 'required_action')) {
                $table->string('required_action', 255)->nullable();
            }
            if (!Schema::hasColumn('calendar_events', 'recurrence_days_json')) {
                $table->json('recurrence_days_json')->nullable();
            }
            if (!Schema::hasColumn('calendar_events', 'recurrence_mode')) {
                $table->string('recurrence_mode', 20)->nullable();
            }
            if (!Schema::hasColumn('calendar_events', 'recurrence_end_after')) {
                $table->unsignedInteger('recurrence_end_after')->nullable();
            }
        });

        DB::table('calendar_events')
            ->whereNull('event_type')
            ->update(['event_type' => 'general']);

        DB::table('calendar_events')
            ->whereNull('recurrence_mode')
            ->update(['recurrence_mode' => 'none']);
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            if (Schema::hasColumn('calendar_events', 'recurrence_end_after')) {
                $table->dropColumn('recurrence_end_after');
            }
            if (Schema::hasColumn('calendar_events', 'recurrence_mode')) {
                $table->dropColumn('recurrence_mode');
            }
            if (Schema::hasColumn('calendar_events', 'recurrence_days_json')) {
                $table->dropColumn('recurrence_days_json');
            }
            if (Schema::hasColumn('calendar_events', 'required_action')) {
                $table->dropColumn('required_action');
            }
            if (Schema::hasColumn('calendar_events', 'event_type')) {
                $table->dropColumn('event_type');
            }
        });
    }
};
