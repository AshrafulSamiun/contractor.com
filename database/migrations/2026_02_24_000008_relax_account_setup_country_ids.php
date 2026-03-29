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

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $this->relaxRequiredCountryForeign('account_setups', 'company_country_id');
        $this->relaxRequiredCountryForeign('account_setups', 'facility_country_id');
    }

    public function down(): void
    {
        if (!Schema::hasTable('account_setups')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $this->enforceRequiredCountryForeign('account_setups', 'company_country_id');
        $this->enforceRequiredCountryForeign('account_setups', 'facility_country_id');
    }

    protected function enforceRequiredCountryForeign(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $this->dropForeignIfExists($table, $column);
        $this->alterColumnRequiredState($table, $column, false);

        try {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($column) {
                $tableBlueprint->foreign($column)
                    ->references('id')
                    ->on('countries')
                    ->restrictOnDelete();
            });
        } catch (\Throwable) {
            // Some engines (e.g. MyISAM) do not support foreign keys.
        }
    }

    protected function relaxRequiredCountryForeign(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $this->dropForeignIfExists($table, $column);
        $this->alterColumnRequiredState($table, $column, true);

        try {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($column) {
                $tableBlueprint->foreign($column)
                    ->references('id')
                    ->on('countries')
                    ->nullOnDelete();
            });
        } catch (\Throwable) {
            // Some engines (e.g. MyISAM) do not support foreign keys.
        }
    }

    protected function dropForeignIfExists(string $table, string $column): void
    {
        try {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($column) {
                $tableBlueprint->dropForeign([$column]);
            });
        } catch (\Throwable) {
            // Foreign key may not exist in all environments.
        }
    }

    protected function alterColumnRequiredState(string $table, string $column, bool $nullable): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $grammar = $connection->getQueryGrammar();

        $wrappedTable = $grammar->wrapTable($table);
        $wrappedColumn = $grammar->wrap($column);
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE {$wrappedTable} MODIFY {$wrappedColumn} BIGINT UNSIGNED {$nullSql}");
            return;
        }

        if ($driver === 'pgsql') {
            $action = $nullable ? 'DROP NOT NULL' : 'SET NOT NULL';
            DB::statement("ALTER TABLE {$wrappedTable} ALTER COLUMN {$wrappedColumn} {$action}");
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE {$wrappedTable} ALTER COLUMN {$wrappedColumn} BIGINT {$nullSql}");
        }
    }
};
