<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pickup_rule_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pickup_rule_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 40);
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_rule_audit_logs');
    }
};
