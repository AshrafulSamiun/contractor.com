<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('todo_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('task_no')->unique();
            $table->text('details');
            $table->string('employee_name');
            $table->timestamp('due_at');
            $table->string('reminder_1')->nullable();
            $table->string('reminder_2')->nullable();
            $table->string('reminder_3')->nullable();
            $table->string('action')->nullable();
            $table->string('action_date')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('todo_tasks');
    }
};
