<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'announcement_no')) {
                $table->string('announcement_no', 60)->nullable()->after('company_name');
            }

            if (!Schema::hasColumn('announcements', 'occurred_at')) {
                $table->timestamp('occurred_at')->nullable()->after('announcement_no');
            }

            if (!Schema::hasColumn('announcements', 'facility_id')) {
                $table->unsignedBigInteger('facility_id')->nullable()->after('occurred_at');
            }

            if (!Schema::hasColumn('announcements', 'facility_name')) {
                $table->string('facility_name', 255)->nullable()->after('facility_id');
            }

            if (!Schema::hasColumn('announcements', 'recipient_mode')) {
                $table->string('recipient_mode', 20)->default('all')->after('facility_name');
            }

            if (!Schema::hasColumn('announcements', 'recipient_ids')) {
                $table->json('recipient_ids')->nullable()->after('recipient_mode');
            }

            if (!Schema::hasColumn('announcements', 'recipient_labels')) {
                $table->json('recipient_labels')->nullable()->after('recipient_ids');
            }

            if (!Schema::hasColumn('announcements', 'required_actions')) {
                $table->json('required_actions')->nullable()->after('recipient_labels');
            }
        });

        if (Schema::hasColumn('announcements', 'occurred_at')) {
            DB::statement('UPDATE announcements SET occurred_at = COALESCE(occurred_at, publish_at, created_at)');
        }

        if (Schema::hasColumn('announcements', 'recipient_mode')) {
            DB::statement("UPDATE announcements SET recipient_mode = 'all' WHERE recipient_mode IS NULL OR recipient_mode = ''");
        }

        if (Schema::hasColumn('announcements', 'recipient_ids')) {
            DB::table('announcements')
                ->whereNull('recipient_ids')
                ->update(['recipient_ids' => json_encode([])]);
        }

        if (Schema::hasColumn('announcements', 'recipient_labels')) {
            DB::table('announcements')
                ->whereNull('recipient_labels')
                ->update(['recipient_labels' => json_encode([])]);
        }

        if (Schema::hasColumn('announcements', 'required_actions')) {
            DB::table('announcements')
                ->whereNull('required_actions')
                ->update(['required_actions' => json_encode([])]);
        }

        if (Schema::hasColumn('announcements', 'announcement_no')) {
            DB::table('announcements')
                ->select(['id', 'created_at', 'announcement_no'])
                ->orderBy('id')
                ->chunk(200, function ($rows): void {
                    foreach ($rows as $row) {
                        $existing = trim((string) ($row->announcement_no ?? ''));
                        if ($existing !== '') {
                            continue;
                        }

                        $year = Carbon::parse($row->created_at ?? now())->format('Y');
                        $announcementNo = sprintf('ANN-%s-%06d', $year, (int) $row->id);

                        DB::table('announcements')
                            ->where('id', $row->id)
                            ->update(['announcement_no' => $announcementNo]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $columns = [
                'required_actions',
                'recipient_labels',
                'recipient_ids',
                'recipient_mode',
                'facility_name',
                'facility_id',
                'occurred_at',
                'announcement_no',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('announcements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
