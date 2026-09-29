<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_securities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->boolean('mfa_enabled')->default(true);
            $table->string('verification_method', 100)->default('Email & Phone');
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamp('pin_changed_at')->nullable();
            $table->json('registered_devices')->nullable();
            $table->json('active_sessions')->nullable();
            $table->json('login_history')->nullable();
            $table->unsignedBigInteger('inserted_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_securities');
    }
};
