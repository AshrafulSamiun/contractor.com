<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('email_messages', 'send_status')) {
                $table->string('send_status', 20)->nullable()->after('status');
            }
            if (!Schema::hasColumn('email_messages', 'send_error')) {
                $table->text('send_error')->nullable()->after('send_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_messages', function (Blueprint $table) {
            if (Schema::hasColumn('email_messages', 'send_error')) {
                $table->dropColumn('send_error');
            }
            if (Schema::hasColumn('email_messages', 'send_status')) {
                $table->dropColumn('send_status');
            }
        });
    }
};
