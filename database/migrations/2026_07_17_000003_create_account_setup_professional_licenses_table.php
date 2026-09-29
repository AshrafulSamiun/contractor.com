<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_setup_professional_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_setup_id')
                ->constrained('account_setups')
                ->cascadeOnDelete();
            $table->string('license_name', 150)->nullable();
            $table->string('license_number', 150)->nullable();
            $table->string('legal_business_name')->nullable();
            $table->string('license_type', 120)->nullable();
            $table->foreignId('issuing_country_id')->nullable()->constrained('countries');
            $table->string('issuing_country', 120)->nullable();
            $table->string('issuing_authority')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('license_status', 60)->nullable();
            $table->string('license_website')->nullable();
            $table->string('verification_url')->nullable();
            $table->text('description_scope')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_setup_professional_licenses');
    }
};
