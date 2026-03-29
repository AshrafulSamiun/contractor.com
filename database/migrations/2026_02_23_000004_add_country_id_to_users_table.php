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
            $table->foreignId('country_id')
                ->nullable()
                ->after('phone')
                ->constrained('countries')
                ->nullOnDelete();
        });

        $countryIdByName = DB::table('countries')
            ->select(['id', 'country_name'])
            ->get()
            ->mapWithKeys(function ($country) {
                return [strtolower(trim((string) $country->country_name)) => (int) $country->id];
            });

        DB::table('users')
            ->select(['id', 'country'])
            ->whereNull('country_id')
            ->whereNotNull('country')
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($countryIdByName) {
                foreach ($users as $user) {
                    $normalized = strtolower(trim((string) $user->country));
                    $countryId = $countryIdByName[$normalized] ?? null;
                    if (!$countryId) {
                        continue;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['country_id' => $countryId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
        });
    }
};
