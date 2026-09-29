<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'step_8_done',
            'step_9_done',
            'step_10_done',
            'step_11_done',
            'step_12_done',
            'step_13_done',
            'step_14_done',
            'step_15_done',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('account_setups', $column)) {
                continue;
            }

            Schema::table('account_setups', function (Blueprint $table) use ($column) {
                $table->boolean($column)->default(false);
            });
        }
    }

    public function down(): void
    {
        $columns = [
            'step_8_done',
            'step_9_done',
            'step_10_done',
            'step_11_done',
            'step_12_done',
            'step_13_done',
            'step_14_done',
            'step_15_done',
        ];

        foreach ($columns as $column) {
            if (! Schema::hasColumn('account_setups', $column)) {
                continue;
            }

            Schema::table('account_setups', function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }
    }
};
