<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DailyReportSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(WorkforceDailyReportSeeder::class);
    }
}
