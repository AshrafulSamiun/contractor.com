<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_incident_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->dateTime('occurred_at');
            $table->string('location')->nullable();
            $table->string('severity', 20)->default('medium');
            $table->text('description')->nullable();
            $table->text('actions_taken')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamps();

            $table->index(['user_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workforce_incident_reports');
    }
};
