<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pickup_rules', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);
            $table->string('name', 120);
            $table->string('verification_method', 120)->nullable();
            $table->unsignedSmallInteger('window_hours')->default(24);
            $table->unsignedSmallInteger('sla_hours')->default(24);
            $table->boolean('reminder_enabled')->default(true);
            $table->json('reminder_channels')->nullable();
            $table->boolean('notify_recipient')->default(true);
            $table->boolean('notify_staff')->default(false);
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_rules');
    }
};
