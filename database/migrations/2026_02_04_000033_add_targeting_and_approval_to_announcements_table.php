<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'target_roles')) {
                $table->json('target_roles')->nullable()->after('audience');
            }
            if (!Schema::hasColumn('announcements', 'requires_approval')) {
                $table->boolean('requires_approval')->default(false)->after('target_roles');
            }
            if (!Schema::hasColumn('announcements', 'approval_status')) {
                $table->string('approval_status', 20)->nullable()->after('requires_approval');
            }
            if (!Schema::hasColumn('announcements', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('announcements', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('announcements', 'notified_at')) {
                $table->timestamp('notified_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'notified_at')) {
                $table->dropColumn('notified_at');
            }
            if (Schema::hasColumn('announcements', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('announcements', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('announcements', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
            if (Schema::hasColumn('announcements', 'requires_approval')) {
                $table->dropColumn('requires_approval');
            }
            if (Schema::hasColumn('announcements', 'target_roles')) {
                $table->dropColumn('target_roles');
            }
        });
    }
};
