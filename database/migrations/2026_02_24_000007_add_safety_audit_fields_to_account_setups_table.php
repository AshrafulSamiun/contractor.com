<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('account_setups')) {
            return;
        }

        if (!Schema::hasColumn('account_setups', 'safety_ack_at')) {
            Schema::table('account_setups', function (Blueprint $table) {
                $table->timestamp('safety_ack_at')->nullable()->after('safety_ack');
            });
        }

        if (!Schema::hasColumn('account_setups', 'safety_ack_ip')) {
            Schema::table('account_setups', function (Blueprint $table) {
                $table->string('safety_ack_ip', 45)->nullable()->after('safety_ack_at');
            });
        }

        if (!Schema::hasColumn('account_setups', 'safety_ack_user_agent')) {
            Schema::table('account_setups', function (Blueprint $table) {
                $table->string('safety_ack_user_agent', 1024)->nullable()->after('safety_ack_ip');
            });
        }

        if (!Schema::hasColumn('account_setups', 'safety_policy_version')) {
            Schema::table('account_setups', function (Blueprint $table) {
                $table->string('safety_policy_version', 40)->nullable()->after('safety_ack_user_agent');
            });
        }

        DB::table('account_setups')
            ->where('safety_ack', true)
            ->whereNull('safety_policy_version')
            ->update([
                'safety_policy_version' => 'legacy',
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('account_setups')) {
            return;
        }

        $dropColumns = [];
        foreach (['safety_ack_at', 'safety_ack_ip', 'safety_ack_user_agent', 'safety_policy_version'] as $column) {
            if (Schema::hasColumn('account_setups', $column)) {
                $dropColumns[] = $column;
            }
        }

        if (!empty($dropColumns)) {
            Schema::table('account_setups', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
    }
};
