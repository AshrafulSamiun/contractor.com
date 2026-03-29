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
            if (!Schema::hasColumn('calendar_events', 'event_no')) {
                $table->string('event_no', 32)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('calendar_events', 'location_map')) {
                $table->string('location_map', 255)->nullable()->after('location');
            }
            if (!Schema::hasColumn('calendar_events', 'priority')) {
                $table->string('priority', 20)->default('medium')->after('visibility');
            }
        });

        DB::table('calendar_events')
            ->whereNull('priority')
            ->update(['priority' => 'medium']);

        $events = DB::table('calendar_events')
            ->select('id', 'created_at')
            ->whereNull('event_no')
            ->orderBy('id')
            ->get();

        foreach ($events as $event) {
            $year = is_string($event->created_at) ? substr($event->created_at, 0, 4) : now()->format('Y');
            if (!preg_match('/^\d{4}$/', $year)) {
                $year = now()->format('Y');
            }
            $eventNo = sprintf('EVT-%s-%04d', $year, (int) $event->id);

            DB::table('calendar_events')
                ->where('id', $event->id)
                ->update(['event_no' => $eventNo]);
        }
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            if (Schema::hasColumn('calendar_events', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('calendar_events', 'location_map')) {
                $table->dropColumn('location_map');
            }
            if (Schema::hasColumn('calendar_events', 'event_no')) {
                $table->dropColumn('event_no');
            }
        });
    }
};
