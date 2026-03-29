<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'company_name')) {
                $table->string('company_name', 191)->nullable()->after('user_id');
                $table->index(['company_name', 'status'], 'announcements_company_status_idx');
            }
        });

        if (!Schema::hasColumn('announcements', 'company_name')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                UPDATE announcements a
                INNER JOIN users u ON u.id = a.user_id
                SET a.company_name = u.company_name
                WHERE a.company_name IS NULL
            ");
            return;
        }

        DB::table('announcements')
            ->select(['id', 'user_id'])
            ->whereNull('company_name')
            ->orderBy('id')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    $companyName = DB::table('users')
                        ->where('id', $row->user_id)
                        ->value('company_name');

                    DB::table('announcements')
                        ->where('id', $row->id)
                        ->update(['company_name' => $companyName]);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('announcements', 'company_name')) {
            return;
        }

        Schema::table('announcements', function (Blueprint $table) {
            try {
                $table->dropIndex('announcements_company_status_idx');
            } catch (\Throwable $e) {
                // Ignore when index is missing.
            }

            $table->dropColumn('company_name');
        });
    }
};
