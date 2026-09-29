<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_no', 50)->unique();
            $table->string('item_name', 150);
            $table->string('unit_of_measure', 50)->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('sales_tax_applicable')->default(false);
            $table->tinyInteger('status')->default(1)->comment('1 = active, 2 = inactive');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->unsignedBigInteger('inserted_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index('item_no');
            $table->index('status');
            $table->index('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_items');
    }
};
