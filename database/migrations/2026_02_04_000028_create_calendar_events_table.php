<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('timezone', 64)->nullable();
            $table->string('location')->nullable();
            $table->string('color', 20)->nullable();
            $table->string('status', 30)->default('scheduled');
            $table->string('visibility', 30)->default('private');
            $table->timestamps();

            $table->index(['user_id', 'start_at']);
            $table->index(['user_id', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
