<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_security_devices', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('project_id')->index(); $table->unsignedBigInteger('user_id')->index();
            $table->string('device_key', 64); $table->string('device_name', 120); $table->string('device_type', 60); $table->string('browser', 60);
            $table->text('user_agent')->nullable(); $table->string('ip_address', 45)->nullable(); $table->string('location', 120)->nullable();
            $table->timestamp('registered_at'); $table->timestamp('last_seen_at')->nullable(); $table->timestamp('revoked_at')->nullable(); $table->timestamps();
            $table->unique(['user_id', 'device_key']);
        });
        Schema::create('account_security_sessions', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('project_id')->index(); $table->unsignedBigInteger('user_id')->index(); $table->unsignedBigInteger('device_id')->index();
            $table->unsignedBigInteger('token_id')->nullable()->unique(); $table->string('ip_address', 45)->nullable(); $table->timestamp('started_at'); $table->timestamp('last_active_at')->nullable(); $table->timestamp('logged_out_at')->nullable(); $table->timestamps();
        });
        Schema::create('account_login_histories', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('project_id')->index(); $table->unsignedBigInteger('user_id')->index(); $table->unsignedBigInteger('device_id')->nullable()->index();
            $table->string('device_name', 120); $table->string('browser', 60); $table->string('ip_address', 45)->nullable(); $table->string('location', 120)->nullable(); $table->string('result', 30)->default('Successful'); $table->timestamp('logged_in_at')->index(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('account_login_histories'); Schema::dropIfExists('account_security_sessions'); Schema::dropIfExists('account_security_devices'); }
};
