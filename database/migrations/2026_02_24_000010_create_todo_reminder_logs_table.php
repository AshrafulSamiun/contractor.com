<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('todo_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('todo_task_id')->constrained('todo_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('reminder_slot');
            $table->timestamp('remind_at');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['todo_task_id', 'reminder_slot', 'remind_at'], 'todo_reminder_unique');
            $table->index(['user_id', 'sent_at'], 'todo_reminder_user_sent_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('todo_reminder_logs');
    }
};
