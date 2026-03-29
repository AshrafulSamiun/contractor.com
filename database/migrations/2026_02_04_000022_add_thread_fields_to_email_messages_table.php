<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('email_messages', 'thread_id')) {
                $table->string('thread_id', 64)->nullable()->after('status');
            }
            if (!Schema::hasColumn('email_messages', 'message_id')) {
                $table->string('message_id', 191)->nullable()->after('thread_id');
            }
            if (!Schema::hasColumn('email_messages', 'in_reply_to')) {
                $table->string('in_reply_to', 191)->nullable()->after('message_id');
            }
            if (!Schema::hasColumn('email_messages', 'references')) {
                $table->text('references')->nullable()->after('in_reply_to');
            }
        });

        // Add indexes if they do not already exist.
        $existingIndexes = $this->existingIndexNames();

        if (!in_array('email_messages_user_id_thread_id_index', $existingIndexes, true)) {
            Schema::table('email_messages', function (Blueprint $table) {
                $table->index(['user_id', 'thread_id']);
            });
        }

        if (!in_array('email_messages_message_id_index', $existingIndexes, true)) {
            Schema::table('email_messages', function (Blueprint $table) {
                $table->index('message_id');
            });
        }
    }

    public function down(): void
    {
        $existingIndexes = $this->existingIndexNames();

        Schema::table('email_messages', function (Blueprint $table) {
            if (in_array('email_messages_user_id_thread_id_index', $existingIndexes, true)) {
                $table->dropIndex('email_messages_user_id_thread_id_index');
            }
            if (in_array('email_messages_message_id_index', $existingIndexes, true)) {
                $table->dropIndex('email_messages_message_id_index');
            }
            if (Schema::hasColumn('email_messages', 'references')) {
                $table->dropColumn('references');
            }
            if (Schema::hasColumn('email_messages', 'in_reply_to')) {
                $table->dropColumn('in_reply_to');
            }
            if (Schema::hasColumn('email_messages', 'message_id')) {
                $table->dropColumn('message_id');
            }
            if (Schema::hasColumn('email_messages', 'thread_id')) {
                $table->dropColumn('thread_id');
            }
        });
    }

    protected function existingIndexNames(): array
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            return collect($connection->select("PRAGMA index_list('email_messages')"))
                ->pluck('name')
                ->unique()
                ->all();
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return collect($connection->select("SHOW INDEX FROM email_messages"))
                ->pluck('Key_name')
                ->unique()
                ->all();
        }

        if ($driver === 'pgsql') {
            return collect($connection->select("
                SELECT indexname
                FROM pg_indexes
                WHERE schemaname = ANY (current_schemas(false))
                  AND tablename = 'email_messages'
            "))
                ->pluck('indexname')
                ->unique()
                ->all();
        }

        return [];
    }
};
