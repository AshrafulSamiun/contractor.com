<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\AccountSetup;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $projects = AccountSetup::query()->get(['id', 'user_id']);

        if ($projects->isEmpty()) {
            $this->command?->warn('Payment methods were not seeded because no account setup projects were found.');
            return;
        }

        $paymentMethods = [
            // Payment Type 1: Cash
            ['name' => 'Cash - Office', 'payment_type' => 1, 'linked_account' => '', 'notes' => 'Cash payment at office for in-person transactions', 'status_active' => true],
            ['name' => 'Cash - Event', 'payment_type' => 1, 'linked_account' => '', 'notes' => 'Cash payment for event registrations', 'status_active' => true],

            // Payment Type 2: Cheque / Check
            ['name' => 'Company Cheque', 'payment_type' => 2, 'linked_account' => 'Chase Bank ****1234', 'notes' => 'Company cheque account', 'status_active' => true],
            ['name' => 'Personal Cheque', 'payment_type' => 2, 'linked_account' => '', 'notes' => 'Personal cheque payments', 'status_active' => true],

            // Payment Type 3: Debit Card
            ['name' => 'Debit Card - Terminal', 'payment_type' => 3, 'linked_account' => 'POS Terminal ****5678', 'notes' => 'Point of sale debit card', 'status_active' => true],
            ['name' => 'Debit Card - Online', 'payment_type' => 3, 'linked_account' => 'Online Debit ****9012', 'notes' => 'Online debit card payments', 'status_active' => true],

            // Payment Type 4: Credit Card
            ['name' => 'Visa Card', 'payment_type' => 4, 'linked_account' => 'Visa ****9012', 'notes' => 'Primary Visa merchant account', 'status_active' => true],
            ['name' => 'Mastercard', 'payment_type' => 4, 'linked_account' => 'Mastercard ****3456', 'notes' => 'Mastercard merchant account', 'status_active' => true],
            ['name' => 'American Express', 'payment_type' => 4, 'linked_account' => 'Amex ****7890', 'notes' => 'American Express merchant account', 'status_active' => true],
            ['name' => 'Discover', 'payment_type' => 4, 'linked_account' => 'Discover ****2345', 'notes' => 'Discover network account', 'status_active' => true],

            // Payment Type 5: Bank Transfer
            ['name' => 'ACH Transfer', 'payment_type' => 5, 'linked_account' => 'Bank of America ****4567', 'notes' => 'ACH direct deposit', 'status_active' => true],
            ['name' => 'Wire Transfer', 'payment_type' => 5, 'linked_account' => 'Wells Fargo ****8901', 'notes' => 'Wire transfer account', 'status_active' => true],
            ['name' => 'Direct Bank Transfer', 'payment_type' => 5, 'linked_account' => 'Citibank ****1234', 'notes' => 'Direct bank to bank transfer', 'status_active' => true],
            ['name' => 'Interac e-Transfer', 'payment_type' => 5, 'linked_account' => 'TD Bank ****5678', 'notes' => 'Canadian Interac e-transfer', 'status_active' => true],

            // Payment Type 6: Email Transfer
            ['name' => 'PayPal', 'payment_type' => 6, 'linked_account' => 'payments@company.com', 'notes' => 'PayPal email transfer', 'status_active' => true],
            ['name' => 'Venmo', 'payment_type' => 6, 'linked_account' => '@company-venmo', 'notes' => 'Venmo email transfer', 'status_active' => true],

            // Payment Type 7: Mobile Payment
            ['name' => 'Apple Pay', 'payment_type' => 7, 'linked_account' => 'Apple Pay ****9012', 'notes' => 'Apple Pay merchant', 'status_active' => true],
            ['name' => 'Google Pay', 'payment_type' => 7, 'linked_account' => 'Google Pay ****3456', 'notes' => 'Google Pay merchant', 'status_active' => true],
            ['name' => 'Samsung Pay', 'payment_type' => 7, 'linked_account' => 'Samsung Pay ****7890', 'notes' => 'Samsung Pay merchant', 'status_active' => true],

            // Payment Type 8: Other
            ['name' => 'PayPal Business', 'payment_type' => 8, 'linked_account' => 'PayPal ****@business.com', 'notes' => 'PayPal business account', 'status_active' => true],
            ['name' => 'Cryptocurrency', 'payment_type' => 8, 'linked_account' => 'Bitcoin wallet', 'notes' => 'Cryptocurrency payment option', 'status_active' => false],
            ['name' => 'Gift Card', 'payment_type' => 8, 'linked_account' => '', 'notes' => 'Gift card payment', 'status_active' => false],
        ];

        $currentYear = date('Y');
        $prefix = 'PM';

        foreach ($projects as $project) {
            foreach ($paymentMethods as $index => $data) {
                $systemNo = sprintf('%s-%d-%03d', $prefix, $currentYear, $index + 1);

                PaymentMethod::updateOrCreate(
                    ['project_id' => $project->id, 'system_no' => $systemNo],
                    array_merge($data, [
                        'system_prefix' => $prefix,
                        'inserted_by' => $project->user_id,
                        'updated_by' => $project->user_id,
                        'is_deleted' => 0,
                    ])
                );
            }
        }

        $this->command?->info("Seeded payment methods for {$projects->count()} account setup project(s).");
    }
}
