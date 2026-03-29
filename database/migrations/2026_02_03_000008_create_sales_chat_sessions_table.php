<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_chat_sessions')) {
            Schema::create('sales_chat_sessions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('chat_no')->unique();
                $table->dateTime('chat_datetime')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('company_name')->nullable();
                $table->string('work_email')->nullable();
                $table->string('business_phone')->nullable();
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->string('call_time')->nullable();
                $table->text('inquiry')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_chat_sessions');
    }
};
