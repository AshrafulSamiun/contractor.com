<?php

namespace Database\Seeders;

use App\Models\CreditCard;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CreditCardSeeder extends Seeder
{
    public function run(): void
    {
        $projectId = 2;
        $currencies = Currency::query()->pluck('id', 'currency_code');

        $cards = [
            ['CC-001', 'Operations Visa', '4111111111111111', 'Operations Manager', 'Visa', '12/28', 20000, 1850, 'Business', 'USD', 'Operations'],
            ['CC-002', 'Purchasing Mastercard', '5555555555554444', 'Purchasing Manager', 'Mastercard', '09/29', 30000, 4725.50, 'Corporate', 'USD', 'Purchasing'],
            ['CC-003', 'Travel Amex', '378282246310005', 'Travel Coordinator', 'American Express', '06/30', 15000, 2310.75, 'Corporate', 'USD', 'Administration'],
            ['CC-004', 'Field Services Visa', '4012888888881881', 'Field Services Manager', 'Visa', '03/29', 12000, 980, 'Business', 'CAD', 'Field Services'],
            ['CC-005', 'Equipment Mastercard', '5105105105105100', 'Fleet Manager', 'Mastercard', '11/30', 25000, 8250, 'Corporate', 'CAD', 'Fleet'],
            ['CC-006', 'Executive Visa', '4222222222222', 'Managing Director', 'Visa', '08/28', 40000, 3650.25, 'Business', 'USD', 'Executive'],
            ['CC-007', 'Marketing Discover', '6011111111111117', 'Marketing Manager', 'Discover', '05/29', 10000, 1275, 'Business', 'USD', 'Marketing'],
            ['CC-008', 'Emergency Card', '4000000000000002', 'Finance Manager', 'Visa', '01/30', 5000, 0, 'Corporate', 'USD', 'Finance'],
        ];

        foreach ($cards as $index => [$cardNo, $nickname, $number, $holder, $type, $expiry, $limit, $balance, $category, $currency, $department]) {
            $card = CreditCard::withTrashed()->updateOrCreate(
                ['project_id' => $projectId, 'card_no' => $cardNo],
                [
                    'card_nickname' => $nickname,
                    'card_number' => $number,
                    'card_last_four' => substr($number, -4),
                    'cardholder_name' => $holder,
                    'card_type' => $type,
                    'card_network' => $type,
                    'expiry_date' => $expiry,
                    'credit_limit' => $limit,
                    'current_balance' => $balance,
                    'billing_day' => 1,
                    'due_day' => 21,
                    'pay_day' => 20,
                    'status_active' => $index !== 7,
                    'card_category' => $category,
                    'currency_id' => $currencies->get($currency),
                    'bank_profile_id' => null,
                    'account_holder_id' => null,
                    'annual_fee' => $type === 'American Express' ? 250 : 99,
                    'payment_due_day' => 21,
                    'grace_period_days' => 20,
                    'minimum_payment_percent' => 2.5,
                    'interest_rate' => 19.99,
                    'billing_address' => '100 Contractor Road',
                    'city' => 'Toronto',
                    'state' => 'ON',
                    'postal_code' => 'M5V 2T4',
                    'country_id' => null,
                    'phone_number' => '(555) 410-2200',
                    'email' => strtolower(str_replace(' ', '.', $department)).'@example.com',
                    'department' => $department,
                    'assigned_to' => null,
                    'notes' => 'Sample credit card for the '.$department.' department.',
                    'is_default' => $index === 0,
                    'created_by' => null,
                    'updated_by' => null,
                    'deleted_at' => null,
                ]
            );

            if ($card->trashed()) {
                $card->restore();
            }
        }

        $this->command?->info('Seeded credit card profile data.');
    }
}
