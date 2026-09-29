<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountHoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_holders', function (Blueprint $table) {
            $table->increments('id');
            $table->Integer('project_id')->default(0)->index();
            $table->string('system_prefix', 20)->nullable($value = true);
            $table->string('system_no', 50)->nullable($value = true);
            $table->integer('account_type')->nullable($value = true);
            $table->string('account_name')->nullable($value = true);
            $table->string('company_name')->nullable($value = true);
            $table->string('business_number')->nullable($value = true);
            $table->string('tax_id_no')->nullable($value = true);
            $table->Integer('currency_id')->default(0);
            $table->string('house_number',50)->nullable($value = true);
            $table->string('street_number',50)->nullable($value = true);
            $table->string('city',100)->nullable($value = true);
            $table->string('state',100)->nullable($value = true);
            $table->Integer('country')->default(0);
            $table->string('zip_code',50)->nullable($value = true);
            $table->string('office_phone',50)->nullable($value = true);
            $table->string('email')->nullable($value = true);
            $table->string('cell_phone',20)->nullable($value = true);
            $table->string('website',120)->nullable($value = true);
            $table->tinyInteger('prefer_contact_method')->nullable($value = true);
            $table->text('linked_transaction_sales')->nullable($value = true);
            $table->text('linked_transaction_purchase')->nullable($value = true);
             
            $table->Integer('inserted_by')->default(0);
            $table->Integer('updated_by')->default(0);
            $table->tinyInteger('status_active')->default(1)->index();
            $table->tinyInteger('is_deleted')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_holders');
    }
}
