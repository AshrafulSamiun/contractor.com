<?php

namespace Database\Seeders;

use App\Models\AccountHolderSuffix;
use Illuminate\Database\Seeder;

class AccountHolderSuffixSeeder extends Seeder
{
    public function run(): void
    {
        $suffixes = [
            ['suffix' => 'Customer', 'prifix' => 'C'],
            ['suffix' => 'Seller', 'prifix' => 'S'],
            ['suffix' => 'Service Provider', 'prifix' => 'SP'],
            ['suffix' => 'Employee', 'prifix' => 'E'],
            ['suffix' => 'Bank', 'prifix' => 'B'],
            ['suffix' => 'Credit Card', 'prifix' => 'CC'],
            ['suffix' => 'Government', 'prifix' => 'G'],
            ['suffix' => 'Tax Office', 'prifix' => 'T'],
            ['suffix' => 'Shareholder', 'prifix' => 'SH'],
        ];

        foreach ($suffixes as $suffix) {
            AccountHolderSuffix::updateOrCreate(
                ['suffix' => $suffix['suffix']],
                [
                    'prifix' => $suffix['prifix'],
                    'status_active' => 1,
                ]
            );
        }
    }
}
