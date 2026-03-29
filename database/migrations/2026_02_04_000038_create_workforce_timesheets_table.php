<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_timesheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('week_start');
            $table->decimal('total_hours', 6, 2)->default(0);
            $table->json('entries_json')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->index(['user_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workforce_timesheets');
    }
};
