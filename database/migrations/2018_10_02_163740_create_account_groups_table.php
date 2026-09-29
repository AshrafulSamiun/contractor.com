<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('project_id')->index();
            $table->Integer('main_group')->default(0);
            $table->string('sub_group_code',50)->nullable($value = true);
            $table->string('sub_group',100)->nullable($value=true);
            $table->Integer('statement_type')->default(0);
            $table->Integer('account_type')->default(0);
            $table->Integer('cash_flow_group')->default(0);
            $table->Integer('retained_earnings')->default(0);          
            $table->Integer('inserted_by')->default(0);
            $table->Integer('updated_by')->default(0);
            $table->tinyInteger('status_active')->default(0)->index();
            $table->tinyInteger('is_deleted')->default(0)->index();
            $table->timestamps();
            $table->unique(['project_id', 'sub_group_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_groups');
    }
}
