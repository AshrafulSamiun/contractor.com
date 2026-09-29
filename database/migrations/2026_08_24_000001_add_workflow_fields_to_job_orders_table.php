<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->time('job_order_time')->nullable()->after('issue_date');
            $table->string('priority', 30)->default('Normal')->after('status');
            $table->boolean('customer_approved')->default(false)->after('customer_id');
            $table->string('customer_type', 40)->nullable()->after('customer_approved');
            $table->string('site_floor_level', 80)->nullable()->after('job_site_id');
            $table->string('site_suite_unit', 80)->nullable()->after('site_floor_level');
            $table->string('site_city', 120)->nullable()->after('site_suite_unit');
            $table->string('site_province', 120)->nullable()->after('site_city');
            $table->string('site_postal_code', 30)->nullable()->after('site_province');
            $table->text('site_access_details')->nullable()->after('site_postal_code');
            $table->date('request_date')->nullable(); $table->time('request_time')->nullable();
            $table->string('requested_by')->nullable(); $table->string('request_method', 50)->nullable();
            $table->string('reference_no', 120)->nullable(); $table->string('account_no', 120)->nullable();
            $table->date('alternate_date')->nullable(); $table->time('alternate_start_time')->nullable();
            $table->time('alternate_end_time')->nullable(); $table->string('alternate_duration', 80)->nullable();
            $table->text('service_notes')->nullable(); $table->text('access_requirements')->nullable();
            $table->string('key_fob_required', 10)->nullable(); $table->string('key_provided_by')->nullable();
            $table->text('equipment_required')->nullable(); $table->string('permits_required', 10)->nullable();
            $table->text('permit_details')->nullable(); $table->text('safety_requirements')->nullable();
            $table->string('insurance_provided', 10)->nullable(); $table->date('insurance_expiry_date')->nullable();
            $table->string('wcb_no', 120)->nullable(); $table->text('customer_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropColumn(['job_order_time','priority','customer_approved','customer_type','site_floor_level','site_suite_unit','site_city','site_province','site_postal_code','site_access_details','request_date','request_time','requested_by','request_method','reference_no','account_no','alternate_date','alternate_start_time','alternate_end_time','alternate_duration','service_notes','access_requirements','key_fob_required','key_provided_by','equipment_required','permits_required','permit_details','safety_requirements','insurance_provided','insurance_expiry_date','wcb_no','customer_notes']);
        });
    }
};
