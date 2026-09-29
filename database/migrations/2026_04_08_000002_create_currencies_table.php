<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 10);
            $table->string('currency_name', 120);
            $table->string('currency_symbol', 20)->nullable();
            $table->integer('inserted_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->tinyInteger('status_active')->default(1)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('currency_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
