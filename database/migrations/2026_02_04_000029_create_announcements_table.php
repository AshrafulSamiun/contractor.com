<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('body');
            $table->string('priority', 20)->default('normal');
            $table->string('status', 20)->default('draft');
            $table->string('audience', 30)->default('all');
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('pinned')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'publish_at']);
            $table->index(['audience', 'publish_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
