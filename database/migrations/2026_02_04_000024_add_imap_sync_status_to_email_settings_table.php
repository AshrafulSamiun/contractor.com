<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $afterColumn = null;
        if (Schema::hasColumn('email_settings', 'imap_last_uid')) {
            $afterColumn = 'imap_last_uid';
        } elseif (Schema::hasColumn('email_settings', 'imap_enabled')) {
            $afterColumn = 'imap_enabled';
        }

        Schema::table('email_settings', function (Blueprint $table) use ($afterColumn) {
            if (!Schema::hasColumn('email_settings', 'imap_last_sync_at')) {
                if ($afterColumn) {
                    $table->timestamp('imap_last_sync_at')->nullable()->after($afterColumn);
                } else {
                    $table->timestamp('imap_last_sync_at')->nullable();
                }
            }
            if (!Schema::hasColumn('email_settings', 'imap_last_error')) {
                $table->string('imap_last_error')->nullable()->after('imap_last_sync_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            if (Schema::hasColumn('email_settings', 'imap_last_error')) {
                $table->dropColumn('imap_last_error');
            }
            if (Schema::hasColumn('email_settings', 'imap_last_sync_at')) {
                $table->dropColumn('imap_last_sync_at');
            }
        });
    }
};
