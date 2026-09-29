<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurance_companies', function (Blueprint $table) {
            $table->string('policy_no', 80)->nullable()->after('insurance_type');
            $table->string('policy_status', 30)->default('Active')->after('policy_no');
            $table->decimal('balance', 15, 2)->default(0)->after('policy_status');
            $table->date('expiry_date')->nullable()->after('balance');
            $table->index(['project_id', 'policy_status']);
            $table->index(['project_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::table('insurance_companies', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'policy_status']);
            $table->dropIndex(['project_id', 'expiry_date']);
            $table->dropColumn(['policy_no', 'policy_status', 'balance', 'expiry_date']);
        });
    }
};
