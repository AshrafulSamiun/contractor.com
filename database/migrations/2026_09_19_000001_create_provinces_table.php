<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->unsignedBigInteger('country_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index('country_id');
            $table->unique(['country_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
