<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('user_id');
            $table->index('project_id', 'sellers_project_id_index');
        });

        DB::statement(
            'UPDATE sellers
             INNER JOIN account_setups ON account_setups.user_id = sellers.user_id
             SET sellers.project_id = account_setups.id
             WHERE sellers.project_id IS NULL'
        );

        DB::statement(
            'UPDATE users
             INNER JOIN account_setups ON account_setups.user_id = users.id
             SET users.project_id = account_setups.id'
        );

        Schema::table('sellers', function (Blueprint $table) {
            $table->unique(['project_id', 'seller_name'], 'sellers_project_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropUnique('sellers_project_name_unique');
            $table->dropIndex('sellers_project_id_index');
            $table->dropColumn('project_id');
        });
    }
};
