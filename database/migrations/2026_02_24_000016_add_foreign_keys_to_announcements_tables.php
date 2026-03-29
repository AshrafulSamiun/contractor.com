<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('announcements')->whereNotIn('user_id', function ($query) {
            $query->select('id')->from('users');
        })->delete();

        DB::table('announcement_reads')->whereNotIn('announcement_id', function ($query) {
            $query->select('id')->from('announcements');
        })->delete();
        DB::table('announcement_reads')->whereNotIn('user_id', function ($query) {
            $query->select('id')->from('users');
        })->delete();

        DB::table('announcement_attachments')->whereNotIn('announcement_id', function ($query) {
            $query->select('id')->from('announcements');
        })->delete();

        if (!$this->foreignExists('announcements', 'user_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('announcements', 'approved_by') && !$this->foreignExists('announcements', 'approved_by')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->foreign('approved_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (!$this->foreignExists('announcement_reads', 'announcement_id')) {
            Schema::table('announcement_reads', function (Blueprint $table) {
                $table->foreign('announcement_id')
                    ->references('id')
                    ->on('announcements')
                    ->cascadeOnDelete();
            });
        }

        if (!$this->foreignExists('announcement_reads', 'user_id')) {
            Schema::table('announcement_reads', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }

        if (!$this->foreignExists('announcement_attachments', 'announcement_id')) {
            Schema::table('announcement_attachments', function (Blueprint $table) {
                $table->foreign('announcement_id')
                    ->references('id')
                    ->on('announcements')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('announcement_attachments', function (Blueprint $table) {
            try {
                $table->dropForeign(['announcement_id']);
            } catch (\Throwable $e) {
                // Ignore when foreign key does not exist.
            }
        });

        Schema::table('announcement_reads', function (Blueprint $table) {
            try {
                $table->dropForeign(['announcement_id']);
            } catch (\Throwable $e) {
                // Ignore when foreign key does not exist.
            }
            try {
                $table->dropForeign(['user_id']);
            } catch (\Throwable $e) {
                // Ignore when foreign key does not exist.
            }
        });

        Schema::table('announcements', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (\Throwable $e) {
                // Ignore when foreign key does not exist.
            }
            if (Schema::hasColumn('announcements', 'approved_by')) {
                try {
                    $table->dropForeign(['approved_by']);
                } catch (\Throwable $e) {
                    // Ignore when foreign key does not exist.
                }
            }
        });
    }

    private function foreignExists(string $tableName, string $columnName): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $databaseName = DB::getDatabaseName();
        $result = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1',
            [$databaseName, $tableName, $columnName]
        );

        return $result !== null;
    }
};
