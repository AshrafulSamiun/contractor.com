<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_daily_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('report_date');
            $table->string('shift', 40)->nullable();
            $table->text('summary')->nullable();
            $table->json('metrics_json')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->index(['user_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workforce_daily_reports');
    }
};
