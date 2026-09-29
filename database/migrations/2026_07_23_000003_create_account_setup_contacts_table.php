<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_setup_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_setup_id')->constrained()->cascadeOnDelete();
            $table->string('department_name', 150);
            $table->string('contact_person', 150);
            $table->string('phone_number', 40);
            $table->string('email', 255);
            $table->string('position_title', 150);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_setup_contacts');
    }
};
