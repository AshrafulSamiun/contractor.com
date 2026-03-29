<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workforce_approvals', function (Blueprint $table) {
            if (!Schema::hasColumn('workforce_approvals', 'meta')) {
                $table->json('meta')->nullable()->after('note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workforce_approvals', function (Blueprint $table) {
            if (Schema::hasColumn('workforce_approvals', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};
