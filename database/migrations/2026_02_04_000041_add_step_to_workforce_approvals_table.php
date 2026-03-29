<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workforce_approvals', function (Blueprint $table) {
            if (!Schema::hasColumn('workforce_approvals', 'step')) {
                $table->string('step', 30)->default('submit')->after('status');
            }
            if (!Schema::hasColumn('workforce_approvals', 'actor_role')) {
                $table->string('actor_role', 30)->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workforce_approvals', function (Blueprint $table) {
            if (Schema::hasColumn('workforce_approvals', 'step')) {
                $table->dropColumn('step');
            }
            if (Schema::hasColumn('workforce_approvals', 'actor_role')) {
                $table->dropColumn('actor_role');
            }
        });
    }
};
