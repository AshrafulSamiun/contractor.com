<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_event_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('minutes_before');
            $table->timestamp('remind_at');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['calendar_event_id', 'user_id', 'minutes_before', 'remind_at'], 'calendar_reminder_unique');
            $table->index(['user_id', 'remind_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_reminder_logs');
    }
};
