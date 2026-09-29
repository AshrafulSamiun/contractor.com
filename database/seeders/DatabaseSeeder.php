<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            ProvinceSeeder::class,
            CurrencySeeder::class,
            BankProfileSeeder::class,
            CreditCardSeeder::class,
            InsuranceCompanySeeder::class,
            FixedAssetSeeder::class,
            ProfilePagesSeeder::class,
            CustomerProfileSeeder::class,
            EmployeeProfileSeeder::class,
            AccountHolderSuffixSeeder::class,
            AccountHolderSeeder::class,
            AccountGroupSeeder::class,
            ChartOfAccountSeeder::class,
            SellerProfileSeeder::class,
            SellerSeeder::class,
            ServiceItemSeeder::class,
            PaymentMethodSeeder::class,
            InvoiceTermSeeder::class,
            PurchaseOrderSeeder::class,
            PurchaseInvoiceSeeder::class,
            PurchaseReturnSeeder::class,
            SellerDebitNoteSeeder::class,
            SellerCreditNoteSeeder::class,
            BillPaymentSeeder::class,
            EstimationSeeder::class,
            SalesOrderSeeder::class,
            SalesInvoiceSeeder::class,
            SalesReturnSeeder::class,
            CustomerCreditNoteSeeder::class,
            InventoryItemSeeder::class,
            VehicleSeeder::class,
            DriverSeeder::class,
            InsurancePolicySeeder::class,
            RepairMaintenanceRecordSeeder::class,
            AccidentReportSeeder::class,
            ViolationTicketSeeder::class,
            DailyLogSeeder::class,
            WorkforceDailyReportSeeder::class,
            WorkforceStaffWorkflowSeeder::class,
            JobOrderSeeder::class,
            JobOrderDailyWorkLogSeeder::class,
            AccountSecuritySeeder::class,
            AccountMessageSeeder::class,
            AccountNotificationSettingSeeder::class,
            SupportTicketSeeder::class,
        ]);
    }
}
