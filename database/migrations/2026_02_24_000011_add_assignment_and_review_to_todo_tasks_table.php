<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('todo_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('todo_tasks', 'assignee_user_id')) {
                $table->foreignId('assignee_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('todo_tasks', 'reviewed_by_user_id')) {
                $table->foreignId('reviewed_by_user_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('todo_tasks', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by_user_id');
            }
            if (!Schema::hasColumn('todo_tasks', 'review_note')) {
                $table->string('review_note', 500)->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('todo_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('todo_tasks', 'review_note')) {
                $table->dropColumn('review_note');
            }
            if (Schema::hasColumn('todo_tasks', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
            if (Schema::hasColumn('todo_tasks', 'reviewed_by_user_id')) {
                $table->dropConstrainedForeignId('reviewed_by_user_id');
            }
            if (Schema::hasColumn('todo_tasks', 'assignee_user_id')) {
                $table->dropConstrainedForeignId('assignee_user_id');
            }
        });
    }
};
