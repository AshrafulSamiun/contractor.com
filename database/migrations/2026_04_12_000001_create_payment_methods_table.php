<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentMethodsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->increments('id');
            $table->Integer('project_id')->default(0)->index();
            $table->string('system_prefix', 20)->nullable();
            $table->string('system_no', 50)->nullable();
            $table->string('name')->nullable();
            $table->integer('payment_type')->nullable();
            $table->string('linked_account', 255)->nullable();
            $table->tinyInteger('status_active')->default(1)->index();
            $table->text('notes')->nullable();
            $table->Integer('inserted_by')->default(0);
            $table->Integer('updated_by')->default(0);
            $table->tinyInteger('is_deleted')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
}
