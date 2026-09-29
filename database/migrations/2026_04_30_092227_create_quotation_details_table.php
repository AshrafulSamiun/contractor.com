<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->string('item_name', 150);
            $table->string('item_description', 500)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('sale_tax_percentage', 5, 2)->nullable();
            $table->decimal('sale_tax_amount', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
            $table->index('quotation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_details');
    }
};
