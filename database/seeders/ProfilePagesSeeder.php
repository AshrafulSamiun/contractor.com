<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\InvoiceTerm;
use App\Models\JobSite;
use App\Models\PaymentMethod;
use App\Models\SalesTax;
use App\Models\ServiceItem;
use App\Models\FixedAsset;
use App\Models\InsuranceCompany;
use Illuminate\Database\Seeder;

class ProfilePagesSeeder extends Seeder
{
    public function run(): void
    {
        $projectId = (int) env('SEED_PROJECT_ID', 16);
        $meta = ['project_id' => $projectId, 'inserted_by' => 0, 'updated_by' => 0, 'is_deleted' => 0];
        $countryId = 236;

        foreach ([['INS-P-001', 'Guardian Insurance Co.', 'General', 'Liability'], ['INS-P-002', 'Builders Mutual Insurance', 'Commercial', 'Property']] as [$number, $name, $type, $insuranceType]) {
            InsuranceCompany::withTrashed()->updateOrCreate(['project_id' => $projectId, 'company_no' => $number], ['company_no' => $number, 'company_name' => $name, 'company_type' => $type, 'insurance_type' => $insuranceType, 'status_active' => true, 'agent_broker_name' => 'Alex Broker', 'agent_broker_phone' => '(555) 410-2100', 'agent_broker_email' => 'broker@example.com', 'primary_contact_name' => 'Claims Desk', 'primary_contact_phone' => '(555) 410-2101', 'primary_contact_email' => 'claims@example.com', 'street_address' => '200 Coverage Avenue', 'city' => 'Toronto', 'state_province' => 'ON', 'postal_code' => 'M5V 2T4', 'country_id' => $countryId, 'notes' => 'Seeded from ProfilePagesSeeder.', 'created_by' => 0, 'updated_by' => 0])->restore();
        }

        foreach ([['FA-P-001', 'Service Van', 'Vehicles', 'Truck', 42000], ['FA-P-002', 'Concrete Mixer', 'Equipment', 'Machinery', 18500]] as [$number, $name, $group, $type, $cost]) {
            FixedAsset::withTrashed()->updateOrCreate(['project_id' => $projectId, 'asset_no' => $number], ['asset_no' => $number, 'asset_name' => $name, 'asset_group' => $group, 'asset_type' => $type, 'location' => 'Main Yard', 'status' => 'Active', 'purchase_date' => '2026-02-15', 'purchase_cost' => $cost, 'book_value' => $cost * 0.85, 'last_valuation_date' => '2026-09-01', 'notes' => 'Profile sample asset.', 'created_by' => 0, 'updated_by' => 0])->restore();
        }

        foreach ([['PM-001', 'Cash', 1], ['PM-002', 'Cheque', 2], ['PM-003', 'Bank Transfer', 3], ['PM-004', 'Credit Card', 4]] as [$number, $name, $type]) {
            PaymentMethod::updateOrCreate(['project_id' => $projectId, 'system_no' => $number], $meta + ['system_prefix' => 'PM', 'system_no' => $number, 'name' => $name, 'payment_type' => $type, 'status_active' => true, 'notes' => "{$name} payment method"]);
        }
        foreach ([['IT-001', 'Cash'], ['IT-002', 'Due on Receipt'], ['IT-003', 'Net 7'], ['IT-004', 'Net 15'], ['IT-005', 'Net 30'], ['IT-006', 'Net 45'], ['IT-007', 'Net 60'], ['IT-008', 'Net 90'], ['IT-009', '2/10 Net 30'], ['IT-010', 'EOM']] as [$number, $name]) {
            InvoiceTerm::updateOrCreate(['project_id' => $projectId, 'term_id' => $number], $meta + ['term_id' => $number, 'term_name' => $name, 'description' => "{$name} invoice term", 'note' => null, 'status' => 1]);
        }
        foreach ([['ST-001', 'California Sales Tax', 7.2500], ['ST-002', 'Canada HST (Ontario)', 13.0000], ['ST-003', 'UK VAT (Standard)', 20.0000], ['ST-004', 'Australia GST', 10.0000], ['ST-005', 'Quebec QST', 9.9750]] as [$number, $name, $rate]) {
            SalesTax::updateOrCreate(['project_id' => $projectId, 'system_no' => $number], $meta + ['system_prefix' => 'ST', 'system_no' => $number, 'tax_name' => $name, 'tax_type' => 1, 'tax_rate' => $rate, 'application_reason' => 1, 'status_active' => true, 'notes' => null]);
        }
        foreach ([['INV-P-001', 'Dell 24 Monitor - P2422H', 'Nos', 17500], ['INV-P-002', 'HP LaserJet Pro M404dn', 'Nos', 28500], ['INV-P-003', 'Logitech MK270 Wireless Combo', 'Set', 2250]] as [$number, $name, $unit, $price]) {
            InventoryItem::updateOrCreate(['project_id' => $projectId, 'item_no' => $number], $meta + ['item_no' => $number, 'item_name' => $name, 'unit_of_measure' => $unit, 'price' => $price, 'sales_tax_applicable' => true, 'status' => 1, 'note' => null]);
        }
        foreach ([['SER-P-001', 'Website Maintenance Service', 'Monthly', 500], ['SER-P-002', 'SEO Optimization Service', 'Monthly', 1200], ['SER-P-003', 'Logo Design Service', 'Project', 800]] as [$number, $name, $unit, $price]) {
            ServiceItem::updateOrCreate(['project_id' => $projectId, 'item_no' => $number], $meta + ['item_no' => $number, 'item_name' => $name, 'unit_of_measure' => $unit, 'price' => $price, 'sales_tax_applicable' => true, 'status' => 1, 'note' => null]);
        }
        JobSite::updateOrCreate(['project_id' => $projectId, 'job_site_no' => 'JS-P-001'], $meta + ['job_site_no' => 'JS-P-001', 'job_site_name' => 'Downtown Office Renovation', 'contact_no' => 'CUST-001', 'start_date' => '2026-05-01', 'end_date' => '2026-12-31', 'description' => 'Downtown office renovation project.', 'customer_no' => 'CUST-001', 'customer_name' => 'ABC Builders Ltd.', 'address' => '123 Main Street, Toronto', 'contact_person' => 'John Smith', 'phone' => '(555) 987-6543', 'email' => 'john.smith@example.com', 'status' => 1, 'note' => null]);

        $this->command?->info('Seeded profile sidebar sample data.');
    }

}
