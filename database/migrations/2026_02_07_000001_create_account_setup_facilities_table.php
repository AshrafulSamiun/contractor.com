<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_setup_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_setup_id')->constrained('account_setups')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('part')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->unsignedInteger('assigned')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_setup_facilities');
    }
};
