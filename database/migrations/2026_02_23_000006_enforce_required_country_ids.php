<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('countries')) {
            return;
        }

        $fallbackCountryId = $this->ensureFallbackCountryId();
        $countryIdByName = $this->countryIdByNormalizedName();

        $this->backfillAccountSetups($countryIdByName, $fallbackCountryId);
        $this->backfillAccountSetupFacilities($countryIdByName, $fallbackCountryId);

        // SQLite test databases cannot reliably alter constrained columns in-place.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $this->enforceRequiredCountryForeign('account_setups', 'company_country_id');
        $this->enforceRequiredCountryForeign('account_setups', 'facility_country_id');
        $this->enforceRequiredCountryForeign('account_setup_facilities', 'country_id');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $this->relaxRequiredCountryForeign('account_setups', 'company_country_id');
        $this->relaxRequiredCountryForeign('account_setups', 'facility_country_id');
        $this->relaxRequiredCountryForeign('account_setup_facilities', 'country_id');
    }

    protected function ensureFallbackCountryId(): int
    {
        $fallback = DB::table('countries')
            ->select(['id'])
            ->whereRaw('LOWER(TRIM(country_name)) = ?', ['unknown'])
            ->first();

        if ($fallback) {
            return (int) $fallback->id;
        }

        return (int) DB::table('countries')->insertGetId([
            'country_name' => 'Unknown',
            'iso_code' => 'ZZ',
            'phone_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function countryIdByNormalizedName(): array
    {
        return DB::table('countries')
            ->select(['id', 'country_name'])
            ->get()
            ->mapWithKeys(function ($country) {
                $key = strtolower(trim((string) $country->country_name));
                return [$key => (int) $country->id];
            })
            ->all();
    }

    protected function backfillAccountSetups(array $countryIdByName, int $fallbackCountryId): void
    {
        if (!Schema::hasTable('account_setups')) {
            return;
        }

        DB::table('account_setups')
            ->select(['id', 'company_country_id', 'facility_country_id', 'company_country', 'facility_country'])
            ->where(function ($query) {
                $query->whereNull('company_country_id')
                    ->orWhereNull('facility_country_id');
            })
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($countryIdByName, $fallbackCountryId) {
                foreach ($rows as $row) {
                    $updates = [];

                    if (is_null($row->company_country_id)) {
                        $normalized = strtolower(trim((string) $row->company_country));
                        $resolved = $countryIdByName[$normalized] ?? $fallbackCountryId;
                        $updates['company_country_id'] = $resolved;
                        if ($normalized === '' || !isset($countryIdByName[$normalized])) {
                            $updates['company_country'] = 'Unknown';
                        }
                    }

                    if (is_null($row->facility_country_id)) {
                        $normalized = strtolower(trim((string) $row->facility_country));
                        $resolved = $countryIdByName[$normalized] ?? $fallbackCountryId;
                        $updates['facility_country_id'] = $resolved;
                        if ($normalized === '' || !isset($countryIdByName[$normalized])) {
                            $updates['facility_country'] = 'Unknown';
                        }
                    }

                    if (!empty($updates)) {
                        DB::table('account_setups')
                            ->where('id', $row->id)
                            ->update($updates);
                    }
                }
            });
    }

    protected function backfillAccountSetupFacilities(array $countryIdByName, int $fallbackCountryId): void
    {
        if (!Schema::hasTable('account_setup_facilities')) {
            return;
        }

        DB::table('account_setup_facilities')
            ->select(['id', 'country_id', 'country'])
            ->whereNull('country_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($countryIdByName, $fallbackCountryId) {
                foreach ($rows as $row) {
                    $normalized = strtolower(trim((string) $row->country));
                    $resolved = $countryIdByName[$normalized] ?? $fallbackCountryId;

                    $updates = [
                        'country_id' => $resolved,
                    ];

                    if ($normalized === '' || !isset($countryIdByName[$normalized])) {
                        $updates['country'] = 'Unknown';
                    }

                    DB::table('account_setup_facilities')
                        ->where('id', $row->id)
                        ->update($updates);
                }
            });
    }

    protected function enforceRequiredCountryForeign(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $this->dropForeignIfExists($table, $column);
        $this->alterColumnRequiredState($table, $column, false);

        Schema::table($table, function (Blueprint $tableBlueprint) use ($column) {
            $tableBlueprint->foreign($column)
                ->references('id')
                ->on('countries')
                ->restrictOnDelete();
        });
    }

    protected function relaxRequiredCountryForeign(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $this->dropForeignIfExists($table, $column);
        $this->alterColumnRequiredState($table, $column, true);

        Schema::table($table, function (Blueprint $tableBlueprint) use ($column) {
            $tableBlueprint->foreign($column)
                ->references('id')
                ->on('countries')
                ->nullOnDelete();
        });
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

