<?php

namespace Database\Seeders;

use App\Models\BankProfile;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class BankProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Bank profiles are scoped to the account setup that acts as this user's project.
        $projectId = (int) env('SEED_PROJECT_ID', 16);
        $currencies = Currency::pluck('id', 'currency_code')->all();

        $banks = [
            ['bank_no' => 'BK-001', 'bank_name' => 'Chase Bank', 'currency' => 'USD', 'account_name' => 'Operating Account', 'account_number' => '1000000001', 'opening_balance' => 25000.00, 'contact_person' => 'Michael Davis', 'phone_number' => '+1 212 555 0101', 'email' => 'business@chase.example', 'website' => 'https://www.chase.com'],
            ['bank_no' => 'BK-002', 'bank_name' => 'Bank of America', 'currency' => 'USD', 'account_name' => 'Payroll Account', 'account_number' => '1000000002', 'opening_balance' => 18000.00, 'contact_person' => 'Sarah Wilson', 'phone_number' => '+1 704 555 0102', 'email' => 'payroll@bofa.example', 'website' => 'https://www.bankofamerica.com'],
            ['bank_no' => 'BK-003', 'bank_name' => 'Wells Fargo', 'currency' => 'USD', 'account_name' => 'Reserve Account', 'account_number' => '1000000003', 'opening_balance' => 12500.00, 'contact_person' => 'Daniel Moore', 'phone_number' => '+1 415 555 0103', 'email' => 'reserve@wellsfargo.example', 'website' => 'https://www.wellsfargo.com'],
            ['bank_no' => 'BK-004', 'bank_name' => 'TD Bank', 'currency' => 'CAD', 'account_name' => 'Canadian Operations', 'account_number' => '1000000004', 'opening_balance' => 15000.00, 'contact_person' => 'Olivia Martin', 'phone_number' => '+1 416 555 0104', 'email' => 'operations@td.example', 'website' => 'https://www.td.com'],
            ['bank_no' => 'BK-005', 'bank_name' => 'Royal Bank of Canada', 'currency' => 'CAD', 'account_name' => 'Equipment Fund', 'account_number' => '1000000005', 'opening_balance' => 9800.00, 'contact_person' => 'Liam Brown', 'phone_number' => '+1 416 555 0105', 'email' => 'equipment@rbc.example', 'website' => 'https://www.rbcroyalbank.com'],
            ['bank_no' => 'BK-006', 'bank_name' => 'HSBC', 'currency' => 'GBP', 'account_name' => 'UK Operations', 'account_number' => '1000000006', 'opening_balance' => 11000.00, 'contact_person' => 'Emma Taylor', 'phone_number' => '+44 20 5555 0106', 'email' => 'uk@hsbc.example', 'website' => 'https://www.hsbc.com'],
            ['bank_no' => 'BK-007', 'bank_name' => 'Barclays', 'currency' => 'GBP', 'account_name' => 'Project Account', 'account_number' => '1000000007', 'opening_balance' => 7200.00, 'contact_person' => 'Noah Clark', 'phone_number' => '+44 20 5555 0107', 'email' => 'projects@barclays.example', 'website' => 'https://www.barclays.co.uk'],
            ['bank_no' => 'BK-008', 'bank_name' => 'Deutsche Bank', 'currency' => 'EUR', 'account_name' => 'European Operations', 'account_number' => '1000000008', 'opening_balance' => 13500.00, 'contact_person' => 'Sofia Weber', 'phone_number' => '+49 30 5555 0108', 'email' => 'europe@db.example', 'website' => 'https://www.db.com'],
            ['bank_no' => 'BK-009', 'bank_name' => 'UBS', 'currency' => 'CHF', 'account_name' => 'Savings Account', 'account_number' => '1000000009', 'opening_balance' => 8900.00, 'contact_person' => 'Lucas Meier', 'phone_number' => '+41 44 555 0109', 'email' => 'savings@ubs.example', 'website' => 'https://www.ubs.com'],
            ['bank_no' => 'BK-010', 'bank_name' => 'DBS Bank', 'currency' => 'SGD', 'account_name' => 'Asia Operations', 'account_number' => '1000000010', 'opening_balance' => 10500.00, 'contact_person' => 'Aisha Rahman', 'phone_number' => '+65 6555 0110', 'email' => 'asia@dbs.example', 'website' => 'https://www.dbs.com'],
            ['bank_no' => 'BK-011', 'bank_name' => 'Standard Chartered', 'currency' => 'BDT', 'account_name' => 'Bangladesh Operations', 'account_number' => '1000000011', 'opening_balance' => 650000.00, 'contact_person' => 'Rahim Ahmed', 'phone_number' => '+880 2 5555 0111', 'email' => 'bd@sc.example', 'website' => 'https://www.sc.com'],
            ['bank_no' => 'BK-012', 'bank_name' => 'National Australia Bank', 'currency' => 'AUD', 'account_name' => 'Australia Operations', 'account_number' => '1000000012', 'opening_balance' => 16200.00, 'contact_person' => 'Grace Lee', 'phone_number' => '+61 2 5555 0112', 'email' => 'au@nab.example', 'website' => 'https://www.nab.com.au'],
        ];

        foreach ($banks as $bank) {
            BankProfile::withTrashed()->updateOrCreate(
                ['project_id' => $projectId, 'bank_no' => $bank['bank_no']],
                [
                    'bank_name' => $bank['bank_name'],
                    'status_active' => true,
                    'currency_id' => $currencies[$bank['currency']] ?? null,
                    'linked_gl_account_id' => null,
                    'account_name' => $bank['account_name'],
                    'account_number' => $bank['account_number'],
                    'opening_balance' => $bank['opening_balance'],
                    'opening_balance_date' => now()->startOfYear()->toDateString(),
                    'contact_person' => $bank['contact_person'],
                    'phone_number' => $bank['phone_number'],
                    'email' => $bank['email'],
                    'website' => $bank['website'],
                    'created_by' => 0,
                    'updated_by' => 0,
                    'deleted_at' => null,
                ]
            );
        }
    }
}
