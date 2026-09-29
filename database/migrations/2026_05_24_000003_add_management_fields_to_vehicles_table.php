<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('vehicles', 'vehicle_type')) {
                $table->string('vehicle_type', 100)->nullable()->after('model');
            }

            if (! Schema::hasColumn('vehicles', 'vehicle_keys_tag_no')) {
                $table->string('vehicle_keys_tag_no', 100)->nullable()->after('fuel_type');
            }

            if (! Schema::hasColumn('vehicles', 'assignment_start_date')) {
                $table->date('assignment_start_date')->nullable()->after('plate_expiry_date');
            }

            if (! Schema::hasColumn('vehicles', 'in_service')) {
                $table->boolean('in_service')->default(false)->after('assigned_driver');
            }

            if (! Schema::hasColumn('vehicles', 'insurance_start_date')) {
                $table->date('insurance_start_date')->nullable()->after('policy_number');
            }

            if (! Schema::hasColumn('vehicles', 'insurance_expired')) {
                $table->boolean('insurance_expired')->default(false)->after('insurance_expiry_date');
            }

            if (! Schema::hasColumn('vehicles', 'seller_name')) {
                $table->string('seller_name', 150)->nullable()->after('insurance_expired');
            }

            if (! Schema::hasColumn('vehicles', 'seller_company_name')) {
                $table->string('seller_company_name', 150)->nullable()->after('seller_name');
            }

            if (! Schema::hasColumn('vehicles', 'seller_phone')) {
                $table->string('seller_phone', 50)->nullable()->after('seller_company_name');
            }

            if (! Schema::hasColumn('vehicles', 'seller_email')) {
                $table->string('seller_email', 150)->nullable()->after('seller_phone');
            }

            if (! Schema::hasColumn('vehicles', 'seller_website')) {
                $table->string('seller_website', 255)->nullable()->after('seller_email');
            }

            if (! Schema::hasColumn('vehicles', 'purchase_invoice_number')) {
                $table->string('purchase_invoice_number', 100)->nullable()->after('purchase_price');
            }

            if (! Schema::hasColumn('vehicles', 'invoice_date')) {
                $table->date('invoice_date')->nullable()->after('purchase_invoice_number');
            }

            if (! Schema::hasColumn('vehicles', 'sales_tax')) {
                $table->decimal('sales_tax', 15, 2)->nullable()->after('invoice_date');
            }

            if (! Schema::hasColumn('vehicles', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->nullable()->after('sales_tax');
            }

            if (! Schema::hasColumn('vehicles', 'total_paid')) {
                $table->decimal('total_paid', 15, 2)->nullable()->after('subtotal');
            }

            if (! Schema::hasColumn('vehicles', 'number_of_installments')) {
                $table->unsignedTinyInteger('number_of_installments')->nullable()->after('total_paid');
            }

            if (! Schema::hasColumn('vehicles', 'first_installment_amount')) {
                $table->decimal('first_installment_amount', 15, 2)->nullable()->after('number_of_installments');
            }

            if (! Schema::hasColumn('vehicles', 'first_installment_date')) {
                $table->date('first_installment_date')->nullable()->after('first_installment_amount');
            }

            if (! Schema::hasColumn('vehicles', 'last_installment_amount')) {
                $table->decimal('last_installment_amount', 15, 2)->nullable()->after('first_installment_date');
            }

            if (! Schema::hasColumn('vehicles', 'last_installment_date')) {
                $table->date('last_installment_date')->nullable()->after('last_installment_amount');
            }

            if (! Schema::hasColumn('vehicles', 'car_photos')) {
                $table->json('car_photos')->nullable()->after('last_installment_date');
            }

            if (! Schema::hasColumn('vehicles', 'driver_profiles')) {
                $table->json('driver_profiles')->nullable()->after('car_photos');
            }

            if (! Schema::hasColumn('vehicles', 'insurance_documents')) {
                $table->json('insurance_documents')->nullable()->after('driver_profiles');
            }

            if (! Schema::hasColumn('vehicles', 'safety_equipments')) {
                $table->json('safety_equipments')->nullable()->after('insurance_documents');
            }
        });
    }

    public function down(): void
    {
        // Intentionally left non-destructive for shared demo data.
    }
};
