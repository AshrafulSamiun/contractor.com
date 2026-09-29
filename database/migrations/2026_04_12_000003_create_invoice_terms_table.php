<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTermsTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_terms', function (Blueprint $table) {
            $table->increments('id');
            $table->Integer('project_id')->default(0)->index();
            $table->string('term_id', 50)->nullable()->index();
            $table->string('term_name', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('status')->default(1)->index();
            $table->Integer('inserted_by')->default(0);
            $table->Integer('updated_by')->default(0);
            $table->tinyInteger('is_deleted')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_terms');
    }
}
