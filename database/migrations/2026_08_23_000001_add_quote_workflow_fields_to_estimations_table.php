<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            $table->string('customer_type', 40)->nullable()->after('customer_id');
            $table->string('site_floor_level', 80)->nullable()->after('job_site_id');
            $table->string('site_suite_unit', 80)->nullable()->after('site_floor_level');
            $table->string('site_city', 120)->nullable()->after('site_suite_unit');
            $table->string('site_province', 120)->nullable()->after('site_city');
            $table->string('site_postal_code', 30)->nullable()->after('site_province');
            $table->text('site_access_details')->nullable()->after('site_postal_code');
            $table->decimal('tax_rate', 5, 2)->nullable()->after('tax');
            $table->string('tax_registration_no', 120)->nullable()->after('tax_rate');
            $table->json('payment_methods')->nullable()->after('payment_method');
            $table->decimal('deposit_percentage', 5, 2)->nullable()->after('deposit_required');
            $table->decimal('deposit_amount', 12, 2)->nullable()->after('deposit_percentage');
            $table->text('notes_to_customer')->nullable()->after('note');
            $table->string('invoice_to', 120)->nullable()->after('convert_to_job_order');
            $table->string('invoice_title', 255)->nullable()->after('invoice_to');
            $table->string('invoice_prefix', 30)->nullable()->after('invoice_title');
            $table->unsignedInteger('invoice_next_number')->nullable()->after('invoice_prefix');
            $table->boolean('create_invoice_after_approval')->default(false)->after('invoice_next_number');
        });
    }

    public function down(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            $table->dropColumn(['customer_type','site_floor_level','site_suite_unit','site_city','site_province','site_postal_code','site_access_details','tax_rate','tax_registration_no','payment_methods','deposit_percentage','deposit_amount','notes_to_customer','invoice_to','invoice_title','invoice_prefix','invoice_next_number','create_invoice_after_approval']);
        });
    }
};
