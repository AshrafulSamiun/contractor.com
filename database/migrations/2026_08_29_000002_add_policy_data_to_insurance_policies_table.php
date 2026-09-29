<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurance_policies', function (Blueprint $table) {
            if (! Schema::hasColumn('insurance_policies', 'policy_data')) {
                $table->json('policy_data')->nullable()->after('payment_frequency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('insurance_policies', function (Blueprint $table) {
            $table->dropColumn('policy_data');
        });
    }
};
