<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->string('asset_no', 60);
            $table->string('asset_name');
            $table->string('asset_group', 100);
            $table->string('asset_type', 100);
            $table->string('location', 150);
            $table->string('status', 30)->default('Active')->index();
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->decimal('book_value', 15, 2)->default(0);
            $table->date('last_valuation_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['project_id', 'asset_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
