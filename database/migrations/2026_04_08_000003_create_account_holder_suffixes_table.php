<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_holder_suffixes', function (Blueprint $table) {
            $table->id();
            $table->string('suffix', 100)->unique();
            $table->string('prifix', 20)->unique();
            $table->tinyInteger('status_active')->default(1)->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_holder_suffixes');
    }
};
