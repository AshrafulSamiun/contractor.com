<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->string('job_order_no')->nullable();
            $table->date('job_order_date')->nullable();
            $table->string('job_order_status')->nullable();
            $table->string('job_site_name')->nullable();
            $table->string('job_site_address')->nullable();
            $table->string('tax_registration_no')->nullable();
            $table->string('approved_by')->nullable();
            $table->text('internal_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropColumn(['job_order_no', 'job_order_date', 'job_order_status', 'job_site_name', 'job_site_address', 'tax_registration_no', 'approved_by', 'internal_notes']);
        });
    }
};
