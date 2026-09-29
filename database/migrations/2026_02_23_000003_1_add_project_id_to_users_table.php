<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('id')->index();
        });

        DB::table('users')->whereNull('project_id')->update([
            'project_id' => DB::raw('id'),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};
