<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locker_compartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('locker_id')->constrained('lockers')->cascadeOnDelete();
            $table->string('size_category', 20);
            $table->string('compartment_code', 40);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('activation_status', 20)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['locker_id', 'size_category'], 'locker_compartment_locker_size_idx');
            $table->index(['user_id', 'size_category'], 'locker_compartment_user_size_idx');
            $table->unique(['locker_id', 'size_category', 'compartment_code'], 'locker_compartment_unique_code_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locker_compartments');
    }
};
