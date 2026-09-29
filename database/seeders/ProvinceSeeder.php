<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $countries = [
            'CA' => ['country_name' => 'Canada', 'phone_code' => '+1'],
            'US' => ['country_name' => 'United States', 'phone_code' => '+1'],
        ];

        foreach ($countries as $isoCode => $country) {
            DB::table('countries')->updateOrInsert(
                ['iso_code' => $isoCode],
                [
                    ...$country,
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)'),
                ]
            );
        }

        $countryIds = DB::table('countries')
            ->whereIn('iso_code', array_keys($countries))
            ->pluck('id', 'iso_code');

        $regions = [
            'CA' => [
                'Alberta', 'British Columbia', 'Manitoba', 'New Brunswick',
                'Newfoundland and Labrador', 'Northwest Territories', 'Nova Scotia',
                'Nunavut', 'Ontario', 'Prince Edward Island', 'Quebec',
                'Saskatchewan', 'Yukon',
            ],
            'US' => [
                'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
                'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho',
                'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana',
                'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota',
                'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada',
                'New Hampshire', 'New Jersey', 'New Mexico', 'New York',
                'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon',
                'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota',
                'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington',
                'West Virginia', 'Wisconsin', 'Wyoming',
            ],
        ];

        foreach ($regions as $isoCode => $names) {
            foreach ($names as $name) {
                DB::table('provinces')->updateOrInsert(
                    ['country_id' => $countryIds[$isoCode], 'name' => $name],
                    ['updated_at' => $now, 'created_at' => $now, 'deleted_at' => null]
                );
            }

            $count = DB::table('provinces')
                ->where('country_id', $countryIds[$isoCode])
                ->whereNull('deleted_at')
                ->count();

            $this->command?->info("Seeded {$countries[$isoCode]['country_name']}: {$count} regions");
        }
    }
}
