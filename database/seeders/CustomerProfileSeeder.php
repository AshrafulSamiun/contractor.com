<?php

namespace Database\Seeders;

use App\Models\AccountSetup;
use App\Models\Country;
use App\Models\Currency;
use App\Models\CustomerProfile;
use Illuminate\Database\Seeder;

class CustomerProfileSeeder extends Seeder
{
    public function run(): void
    {
        $projects = AccountSetup::query()
            ->join('users', 'users.id', '=', 'account_setups.user_id')
            ->where('users.is_active', true)
            ->where('users.role', '!=', 'super_admin')
            ->get(['account_setups.id as project_id', 'account_setups.user_id as owner_user_id']);

        if ($projects->isEmpty()) {
            $this->command?->warn('Customers were not seeded because no active account setup projects were found.');
            return;
        }

        $currencyId = Currency::where('currency_code', 'CAD')->value('id')
            ?? Currency::where('currency_code', 'USD')->value('id')
            ?? Currency::value('id')
            ?? 0;
        $countryId = Country::where('country_name', 'Canada')->value('id') ?? Country::value('id') ?? 0;

        $customers = [
            ['ABC Construction Ltd.', 'Commercial', 'Michael Brown', '+1 (416) 555-0101', 'accounts@abcconstruction.example', '123', 'Construction Lane', 'Toronto', 'Ontario', 'M5V 2T6', '5215.00', 'Net 15'],
            ['BuildTech Solutions Ltd.', 'Commercial', 'John Smith', '+1 (604) 555-0102', 'john.smith@buildtech.example', '456', 'Business Avenue', 'Vancouver', 'British Columbia', 'V6B 1A1', '8450.00', 'Net 30'],
            ['Skyline Developers Inc.', 'Commercial', 'Sarah Wilson', '+1 (403) 555-0103', 'billing@skyline.example', '780', 'Centre Street', 'Calgary', 'Alberta', 'T2P 1J9', '2890.50', 'Net 30'],
            ['Metro Property Group', 'Commercial', 'Daniel Lee', '+1 (514) 555-0104', 'finance@metroproperty.example', '220', 'Rue Sainte-Catherine', 'Montreal', 'Quebec', 'H3B 1A7', '5670.00', 'Net 15'],
            ['Greenfield Builders', 'Commercial', 'Emma Taylor', '+1 (613) 555-0105', 'accounts@greenfield.example', '95', 'Elgin Street', 'Ottawa', 'Ontario', 'K1P 5E9', '1250.75', 'Net 30'],
            ['Urban Spaces Ltd.', 'Commercial', 'Noah Martin', '+1 (780) 555-0106', 'billing@urbanspaces.example', '10120', 'Jasper Avenue', 'Edmonton', 'Alberta', 'T5J 1Z4', '4980.00', 'Net 15'],
            ['Olivia Anderson', 'Residential', 'Olivia Anderson', '+1 (905) 555-0107', 'olivia.anderson@example.com', '18', 'Lakeview Road', 'Mississauga', 'Ontario', 'L5B 2C9', '960.00', 'Due on Receipt'],
            ['Liam Thompson', 'Residential', 'Liam Thompson', '+1 (289) 555-0108', 'liam.thompson@example.com', '74', 'Maple Grove', 'Hamilton', 'Ontario', 'L8P 1A1', '0.00', 'Due on Receipt'],
        ];

        foreach ($projects as $project) {
            foreach ($customers as $index => $row) {
                [$name, $type, $contact, $phone, $email, $house, $street, $city, $state, $postal, $balance, $terms] = $row;
                $systemNo = 'CUS-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT);

                CustomerProfile::withoutGlobalScopes()->updateOrCreate(
                    ['project_id' => $project->project_id, 'system_no' => $systemNo],
                    [
                        'account_type' => 1, 'system_prefix' => 'CUS', 'account_name' => $contact,
                        'company_name' => $type === 'Commercial' ? $name : null, 'customer_type' => $type,
                        'business_number' => 'BN-'.str_pad((string) ($index + 1001), 7, '0', STR_PAD_LEFT),
                        'tax_id_no' => 'TAX-CA-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                        'currency_id' => $currencyId, 'house_number' => $house, 'street_number' => $street,
                        'city' => $city, 'state' => $state, 'country' => $countryId, 'zip_code' => $postal,
                        'office_phone' => $phone, 'cell_phone' => $phone, 'email' => $email,
                        'website' => $type === 'Commercial' ? 'https://'.str($name)->slug('').'.example' : null,
                        'prefer_contact_method' => 2, 'total_invoices' => 4 + $index * 2,
                        'total_outstanding' => (float) $balance, 'current_balance' => (float) $balance,
                        'payment_terms' => $terms, 'invoice_terms' => $terms,
                        'status_active' => true, 'inserted_by' => $project->owner_user_id,
                        'updated_by' => $project->owner_user_id, 'is_deleted' => false,
                    ]
                );
            }
        }

        $this->command?->info(sprintf('Seeded %d customers for %d account setup project(s).', count($customers), $projects->count()));
    }
}
