<?php

namespace Database\Seeders;

use App\Models\AccountHolder;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class SellerProfileSeeder extends Seeder
{
    public function run(): void
    {
        $projectId = (int) env('SEED_PROJECT_ID', 16);
        $currencyId = Currency::query()->where('currency_code', 'USD')->value('id') ?? 1;
        $seller = [
            'project_id' => $projectId, 'system_prefix' => 'SLR', 'system_no' => 'SLR-0001', 'account_type' => 2,
            'account_name' => 'ABC Plumbing Supplies', 'company_name' => 'ABC Plumbing Supplies', 'legal_company_name' => 'ABC Plumbing Supplies Inc.', 'business_number' => '123456789', 'incorporation_no' => '123456789', 'year_established' => 2010, 'currency_id' => $currencyId,
            'primary_contact_name' => 'Mark Johnson', 'job_title' => 'Account Manager', 'office_phone' => '(555) 234-5678', 'cell_phone' => '(555) 234-5680', 'mobile_phone' => '(555) 234-5680', 'email' => 'mark.johnson@abcplumbing.com', 'accounts_email' => 'accounts@abcplumbing.com',
            'house_number' => '123', 'street_number' => 'Plumbing Way, Suite 100', 'city' => 'Houston', 'state' => 'Texas', 'zip_code' => '77001', 'country' => 236, 'website' => 'https://www.abcplumbingsupplies.com',
            'business_fields' => 'Plumbing, Pipe Fitting, HVAC, Water Heaters, Tools & Accessories', 'industry' => 'Plumbing Supplies', 'supplier_type' => 'Supplier', 'credit_limit' => 50000, 'current_balance' => 12450, 'total_outstanding' => 19750, 'payment_terms' => 'Net 30', 'invoice_terms' => 'Due on Receipt', 'seller_notes' => 'Preferred supplier for bulk plumbing materials. Excellent payment history.', 'status_active' => true, 'inserted_by' => 0, 'updated_by' => 0, 'is_deleted' => 0,
        ];
        AccountHolder::updateOrCreate(['project_id' => $projectId, 'system_no' => 'SLR-0001'], $seller);
    }
}
