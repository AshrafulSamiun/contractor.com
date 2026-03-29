<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lockers', function (Blueprint $table) {
            $table->string('locker_name', 120)->nullable()->after('code');
            $table->string('locker_type', 40)->default('facility_owned')->after('locker_name');
            $table->index(['user_id', 'locker_type', 'status'], 'lockers_user_type_status_idx');
        });

        DB::table('lockers')
            ->whereNull('locker_name')
            ->update(['locker_name' => DB::raw('code')]);
    }

    public function down(): void
    {
        Schema::table('lockers', function (Blueprint $table) {
            $table->dropIndex('lockers_user_type_status_idx');
            $table->dropColumn(['locker_name', 'locker_type']);
        });
    }
};
