<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `account_holders` MODIFY `system_prefix` VARCHAR(20) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `account_holders` MODIFY `system_prefix` INT NOT NULL DEFAULT 0");
    }
};
