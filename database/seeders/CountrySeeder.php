<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/countries.json');
        if (!file_exists($path)) {
            throw new \RuntimeException('Missing seed data file: ' . $path);
        }

        $items = json_decode(file_get_contents($path), true) ?? [];
        foreach ($items as $item) {
            $iso = $item['iso_code'] ?? null;
            $name = $item['country_name'] ?? null;

            if (!$iso || !$name) {
                continue;
            }

            Country::updateOrCreate(
                ['iso_code' => $iso],
                [
                    'country_name' => $name,
                    'phone_code' => $item['phone_code'] ?? null,
                ]
            );
        }
    }
}
