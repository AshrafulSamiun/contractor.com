<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('account_setups')) {
            return;
        }

        $columns = [
            'company_id_number' => fn (Blueprint $table) => $table->string('company_id_number', 40)->nullable(),
            'business_number' => fn (Blueprint $table) => $table->string('business_number', 120)->nullable(),
            'incorporation_date' => fn (Blueprint $table) => $table->date('incorporation_date')->nullable(),
            'company_status' => fn (Blueprint $table) => $table->string('company_status', 60)->nullable(),
            'business_type' => fn (Blueprint $table) => $table->string('business_type', 120)->nullable(),
            'currency_code' => fn (Blueprint $table) => $table->string('currency_code', 10)->nullable(),
            'business_location' => fn (Blueprint $table) => $table->string('business_location')->nullable(),
            'fax_number' => fn (Blueprint $table) => $table->string('fax_number', 30)->nullable(),
            'facebook_profile' => fn (Blueprint $table) => $table->string('facebook_profile')->nullable(),
            'instagram_profile' => fn (Blueprint $table) => $table->string('instagram_profile')->nullable(),
            'industry_type' => fn (Blueprint $table) => $table->string('industry_type', 120)->nullable(),
            'years_in_business' => fn (Blueprint $table) => $table->string('years_in_business', 60)->nullable(),
            'number_of_employees' => fn (Blueprint $table) => $table->string('number_of_employees', 60)->nullable(),
            'annual_revenue_range' => fn (Blueprint $table) => $table->string('annual_revenue_range', 80)->nullable(),
            'hear_about_source' => fn (Blueprint $table) => $table->string('hear_about_source', 150)->nullable(),
            'owner_name' => fn (Blueprint $table) => $table->string('owner_name', 150)->nullable(),
            'director_name' => fn (Blueprint $table) => $table->string('director_name', 150)->nullable(),
            'authorized_contact_name' => fn (Blueprint $table) => $table->string('authorized_contact_name', 150)->nullable(),
            'designation_title' => fn (Blueprint $table) => $table->string('designation_title', 120)->nullable(),
            'authorized_contact_phone' => fn (Blueprint $table) => $table->string('authorized_contact_phone', 30)->nullable(),
            'authorized_contact_email' => fn (Blueprint $table) => $table->string('authorized_contact_email', 255)->nullable(),
        ];

        foreach ($columns as $column => $definition) {
            if (Schema::hasColumn('account_setups', $column)) {
                continue;
            }

            Schema::table('account_setups', function (Blueprint $table) use ($definition) {
                $definition($table);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('account_setups')) {
            return;
        }

        $dropColumns = [
            'company_id_number',
            'business_number',
            'incorporation_date',
            'company_status',
            'business_type',
            'currency_code',
            'business_location',
            'fax_number',
            'facebook_profile',
            'instagram_profile',
            'industry_type',
            'years_in_business',
            'number_of_employees',
            'annual_revenue_range',
            'hear_about_source',
            'owner_name',
            'director_name',
            'authorized_contact_name',
            'designation_title',
            'authorized_contact_phone',
            'authorized_contact_email',
        ];

        $existing = array_values(array_filter(
            $dropColumns,
            fn (string $column) => Schema::hasColumn('account_setups', $column),
        ));

        if ($existing === []) {
            return;
        }

        Schema::table('account_setups', function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }
};
