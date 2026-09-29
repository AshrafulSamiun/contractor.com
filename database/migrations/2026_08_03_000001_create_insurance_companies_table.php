<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->string('company_no', 60);
            $table->string('company_name');
            $table->string('company_type', 100);
            $table->string('insurance_type', 100);
            $table->boolean('status_active')->default(true)->index();
            $table->string('agent_broker_name')->nullable();
            $table->string('agent_broker_phone', 50)->nullable();
            $table->string('agent_broker_email')->nullable();
            $table->string('primary_contact_name');
            $table->string('primary_contact_phone', 50);
            $table->string('primary_contact_email')->nullable();
            $table->string('street_address');
            $table->string('city');
            $table->string('state_province', 100)->nullable();
            $table->string('postal_code', 30);
            $table->foreignId('country_id')->constrained('countries')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['project_id', 'company_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_companies');
    }
};
