<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('facilities') && !Schema::hasColumn('facilities', 'country_id')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->foreignId('country_id')
                    ->nullable()
                    ->after('postal_code')
                    ->constrained('countries')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('account_setups') && !Schema::hasColumn('account_setups', 'company_country_id')) {
            Schema::table('account_setups', function (Blueprint $table) {
                $table->foreignId('company_country_id')
                    ->nullable()
                    ->after('company_zip')
                    ->constrained('countries')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('account_setups') && !Schema::hasColumn('account_setups', 'facility_country_id')) {
            Schema::table('account_setups', function (Blueprint $table) {
                $table->foreignId('facility_country_id')
                    ->nullable()
                    ->after('facility_zip')
                    ->constrained('countries')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('account_setup_facilities') && !Schema::hasColumn('account_setup_facilities', 'country_id')) {
            Schema::table('account_setup_facilities', function (Blueprint $table) {
                $table->foreignId('country_id')
                    ->nullable()
                    ->after('city')
                    ->constrained('countries')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('sales_chat_sessions') && !Schema::hasColumn('sales_chat_sessions', 'country_id')) {
            Schema::table('sales_chat_sessions', function (Blueprint $table) {
                $table->foreignId('country_id')
                    ->nullable()
                    ->after('business_phone')
                    ->constrained('countries')
                    ->nullOnDelete();
            });
        }

        $countryIdByName = DB::table('countries')
            ->select(['id', 'country_name'])
            ->get()
            ->mapWithKeys(function ($country) {
                return [strtolower(trim((string) $country->country_name)) => (int) $country->id];
            });

        DB::table('facilities')
            ->select(['id', 'country'])
            ->whereNull('country_id')
            ->whereNotNull('country')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($countryIdByName) {
                foreach ($rows as $row) {
                    $countryId = $countryIdByName[strtolower(trim((string) $row->country))] ?? null;
                    if (!$countryId) {
                        continue;
                    }

                    DB::table('facilities')
                        ->where('id', $row->id)
                        ->update(['country_id' => $countryId]);
                }
            });

        DB::table('account_setups')
            ->select(['id', 'company_country', 'facility_country'])
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($countryIdByName) {
                foreach ($rows as $row) {
                    $updates = [];
                    $companyCountryId = $countryIdByName[strtolower(trim((string) $row->company_country))] ?? null;
                    $facilityCountryId = $countryIdByName[strtolower(trim((string) $row->facility_country))] ?? null;
                    if ($companyCountryId) {
                        $updates['company_country_id'] = $companyCountryId;
                    }
                    if ($facilityCountryId) {
                        $updates['facility_country_id'] = $facilityCountryId;
                    }
                    if (empty($updates)) {
                        continue;
                    }

                    DB::table('account_setups')
                        ->where('id', $row->id)
                        ->update($updates);
                }
            });

        DB::table('account_setup_facilities')
            ->select(['id', 'country'])
            ->whereNull('country_id')
            ->whereNotNull('country')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($countryIdByName) {
                foreach ($rows as $row) {
                    $countryId = $countryIdByName[strtolower(trim((string) $row->country))] ?? null;
                    if (!$countryId) {
                        continue;
                    }

                    DB::table('account_setup_facilities')
                        ->where('id', $row->id)
                        ->update(['country_id' => $countryId]);
                }
            });

        DB::table('sales_chat_sessions')
            ->select(['id', 'country'])
            ->whereNull('country_id')
            ->whereNotNull('country')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($countryIdByName) {
                foreach ($rows as $row) {
                    $countryId = $countryIdByName[strtolower(trim((string) $row->country))] ?? null;
                    if (!$countryId) {
                        continue;
                    }

                    DB::table('sales_chat_sessions')
                        ->where('id', $row->id)
                        ->update(['country_id' => $countryId]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_chat_sessions') && Schema::hasColumn('sales_chat_sessions', 'country_id')) {
            Schema::table('sales_chat_sessions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('country_id');
            });
        }

        if (Schema::hasTable('account_setup_facilities') && Schema::hasColumn('account_setup_facilities', 'country_id')) {
            Schema::table('account_setup_facilities', function (Blueprint $table) {
                $table->dropConstrainedForeignId('country_id');
            });
        }

        if (Schema::hasTable('account_setups') && Schema::hasColumn('account_setups', 'facility_country_id')) {
            Schema::table('account_setups', function (Blueprint $table) {
                $table->dropConstrainedForeignId('facility_country_id');
            });
        }

        if (Schema::hasTable('account_setups') && Schema::hasColumn('account_setups', 'company_country_id')) {
            Schema::table('account_setups', function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_country_id');
            });
        }

        if (Schema::hasTable('facilities') && Schema::hasColumn('facilities', 'country_id')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->dropConstrainedForeignId('country_id');
            });
        }
    }
};
