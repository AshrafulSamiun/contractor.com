<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE `account_holders`
            SET `account_type` = CASE
                WHEN JSON_VALID(`account_type`) AND JSON_UNQUOTE(JSON_EXTRACT(`account_type`, '$[0]')) = 'Customer' THEN '1'
                WHEN JSON_VALID(`account_type`) AND JSON_UNQUOTE(JSON_EXTRACT(`account_type`, '$[0]')) = 'Seller' THEN '2'
                WHEN JSON_VALID(`account_type`) AND JSON_UNQUOTE(JSON_EXTRACT(`account_type`, '$[0]')) = 'Service Provider' THEN '3'
                WHEN JSON_VALID(`account_type`) AND JSON_UNQUOTE(JSON_EXTRACT(`account_type`, '$[0]')) = 'Employee' THEN '4'
                WHEN JSON_VALID(`account_type`) AND JSON_UNQUOTE(JSON_EXTRACT(`account_type`, '$[0]')) = 'Bank' THEN '5'
                WHEN JSON_VALID(`account_type`) AND JSON_UNQUOTE(JSON_EXTRACT(`account_type`, '$[0]')) = 'Credit Card' THEN '6'
                WHEN JSON_VALID(`account_type`) AND JSON_UNQUOTE(JSON_EXTRACT(`account_type`, '$[0]')) = 'Government' THEN '7'
                WHEN JSON_VALID(`account_type`) AND JSON_UNQUOTE(JSON_EXTRACT(`account_type`, '$[0]')) = 'Tax Office' THEN '8'
                WHEN JSON_VALID(`account_type`) AND JSON_UNQUOTE(JSON_EXTRACT(`account_type`, '$[0]')) = 'Shareholder' THEN '9'
                WHEN `account_type` = 'Customer' THEN '1'
                WHEN `account_type` = 'Seller' THEN '2'
                WHEN `account_type` = 'Service Provider' THEN '3'
                WHEN `account_type` = 'Employee' THEN '4'
                WHEN `account_type` = 'Bank' THEN '5'
                WHEN `account_type` = 'Credit Card' THEN '6'
                WHEN `account_type` = 'Government' THEN '7'
                WHEN `account_type` = 'Tax Office' THEN '8'
                WHEN `account_type` = 'Shareholder' THEN '9'
                ELSE `account_type`
            END
        ");

        DB::statement("
            UPDATE `account_holders`
            SET `prefer_contact_method` = CASE
                WHEN JSON_VALID(`prefer_contact_method`) AND JSON_UNQUOTE(JSON_EXTRACT(`prefer_contact_method`, '$[0]')) = 'Phone' THEN '1'
                WHEN JSON_VALID(`prefer_contact_method`) AND JSON_UNQUOTE(JSON_EXTRACT(`prefer_contact_method`, '$[0]')) = 'Email' THEN '2'
                WHEN JSON_VALID(`prefer_contact_method`) AND JSON_UNQUOTE(JSON_EXTRACT(`prefer_contact_method`, '$[0]')) = 'Other' THEN '3'
                WHEN `prefer_contact_method` = 'Phone' THEN '1'
                WHEN `prefer_contact_method` = 'Email' THEN '2'
                WHEN `prefer_contact_method` = 'Other' THEN '3'
                ELSE `prefer_contact_method`
            END
        ");

        DB::statement("ALTER TABLE `account_holders` MODIFY `account_type` INT NULL");
        DB::statement("ALTER TABLE `account_holders` MODIFY `prefer_contact_method` TINYINT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `account_holders` MODIFY `account_type` TEXT NULL");
        DB::statement("ALTER TABLE `account_holders` MODIFY `prefer_contact_method` TEXT NULL");
    }
};
