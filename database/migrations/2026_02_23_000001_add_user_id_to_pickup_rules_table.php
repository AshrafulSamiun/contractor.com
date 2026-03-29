<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pickup_rules', 'user_id')) {
            Schema::table('pickup_rules', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        DB::table('pickup_rules')
            ->whereNull('user_id')
            ->whereNotNull('created_by')
            ->update(['user_id' => DB::raw('created_by')]);

        DB::table('pickup_rules')
            ->whereNull('user_id')
            ->whereNotNull('updated_by')
            ->update(['user_id' => DB::raw('updated_by')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('pickup_rules', 'user_id')) {
            Schema::table('pickup_rules', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};
