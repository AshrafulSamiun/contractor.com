<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['currency_code' => 'AED', 'currency_name' => 'UAE Dirham', 'currency_symbol' => 'AED'],
            ['currency_code' => 'AUD', 'currency_name' => 'Australian Dollar', 'currency_symbol' => 'AUD'],
            ['currency_code' => 'BDT', 'currency_name' => 'Bangladeshi Taka', 'currency_symbol' => 'BDT'],
            ['currency_code' => 'CAD', 'currency_name' => 'Canadian Dollar', 'currency_symbol' => 'CAD'],
            ['currency_code' => 'CHF', 'currency_name' => 'Swiss Franc', 'currency_symbol' => 'CHF'],
            ['currency_code' => 'CNY', 'currency_name' => 'Chinese Yuan', 'currency_symbol' => 'CNY'],
            ['currency_code' => 'DKK', 'currency_name' => 'Danish Krone', 'currency_symbol' => 'DKK'],
            ['currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => 'EUR'],
            ['currency_code' => 'GBP', 'currency_name' => 'British Pound Sterling', 'currency_symbol' => 'GBP'],
            ['currency_code' => 'HKD', 'currency_name' => 'Hong Kong Dollar', 'currency_symbol' => 'HKD'],
            ['currency_code' => 'INR', 'currency_name' => 'Indian Rupee', 'currency_symbol' => 'INR'],
            ['currency_code' => 'JPY', 'currency_name' => 'Japanese Yen', 'currency_symbol' => 'JPY'],
            ['currency_code' => 'NOK', 'currency_name' => 'Norwegian Krone', 'currency_symbol' => 'NOK'],
            ['currency_code' => 'NZD', 'currency_name' => 'New Zealand Dollar', 'currency_symbol' => 'NZD'],
            ['currency_code' => 'SAR', 'currency_name' => 'Saudi Riyal', 'currency_symbol' => 'SAR'],
            ['currency_code' => 'SEK', 'currency_name' => 'Swedish Krona', 'currency_symbol' => 'SEK'],
            ['currency_code' => 'SGD', 'currency_name' => 'Singapore Dollar', 'currency_symbol' => 'SGD'],
            ['currency_code' => 'TRY', 'currency_name' => 'Turkish Lira', 'currency_symbol' => 'TRY'],
            ['currency_code' => 'USD', 'currency_name' => 'US Dollar', 'currency_symbol' => '$'],
            ['currency_code' => 'ZAR', 'currency_name' => 'South African Rand', 'currency_symbol' => 'ZAR'],
        ];

        foreach ($currencies as $currency) {
            Currency::withTrashed()->updateOrCreate(
                ['currency_code' => $currency['currency_code']],
                [
                    'currency_name' => $currency['currency_name'],
                    'currency_symbol' => $currency['currency_symbol'],
                    'status_active' => true,
                    'inserted_by' => 0,
                    'updated_by' => 0,
                    'deleted_at' => null,
                ]
            );
        }
    }
}
