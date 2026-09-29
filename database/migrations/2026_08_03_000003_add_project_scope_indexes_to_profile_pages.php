<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['payment_methods', 'sales_taxes', 'invoice_terms', 'inventory_items', 'service_items', 'job_sites'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->index(['project_id', 'is_deleted'], "{$table}_project_deleted_index");
            });
        }
    }

    public function down(): void
    {
        foreach (['payment_methods', 'sales_taxes', 'invoice_terms', 'inventory_items', 'service_items', 'job_sites'] as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex("{$table}_project_deleted_index"));
        }
    }
};
