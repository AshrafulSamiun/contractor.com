<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurance_policies', function (Blueprint $table) {
            $table->string('insurance_type', 100)->nullable()->after('coverage_type');
            $table->string('agent_name', 150)->nullable()->after('company_address');
            $table->string('agent_phone', 50)->nullable()->after('agent_name');
            $table->string('agent_email', 150)->nullable()->after('agent_phone');
            $table->decimal('insured_value', 15, 2)->nullable()->after('coverage_amount');
            $table->decimal('sales_tax', 15, 2)->nullable()->after('premium_amount');
            $table->decimal('total_paid', 15, 2)->nullable()->after('sales_tax');
            $table->string('payment_method', 80)->nullable()->after('payment_frequency');
            $table->date('charging_date')->nullable()->after('payment_method');
            $table->json('reminders')->nullable()->after('policy_data');
            $table->json('claim_history')->nullable()->after('reminders');
            $table->json('payment_history')->nullable()->after('claim_history');
            $table->string('document_name', 255)->nullable()->after('payment_history');
            $table->string('document_path', 500)->nullable()->after('document_name');
            $table->index(['status', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::table('insurance_policies', function (Blueprint $table) {
            $table->dropIndex(['status', 'expiry_date']);
            $table->dropColumn([
                'insurance_type', 'agent_name', 'agent_phone', 'agent_email',
                'insured_value', 'sales_tax', 'total_paid', 'payment_method',
                'charging_date', 'reminders', 'claim_history', 'payment_history',
                'document_name', 'document_path',
            ]);
        });
    }
};
