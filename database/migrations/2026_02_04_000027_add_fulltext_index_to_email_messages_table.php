<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!$this->supportsFullTextIndexes()) {
            return;
        }

        Schema::table('email_messages', function (Blueprint $table) {
            $table->fullText(['subject', 'body_text'], 'email_messages_subject_body_text_fulltext');
        });
    }

    public function down(): void
    {
        if (!$this->supportsFullTextIndexes()) {
            return;
        }

        Schema::table('email_messages', function (Blueprint $table) {
            $table->dropFullText('email_messages_subject_body_text_fulltext');
        });
    }

    protected function supportsFullTextIndexes(): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        return in_array($driver, ['mysql', 'mariadb', 'pgsql'], true);
    }
};
