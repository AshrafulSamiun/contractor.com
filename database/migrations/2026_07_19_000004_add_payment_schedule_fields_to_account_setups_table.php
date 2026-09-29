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

        Schema::table('account_setups', function (Blueprint $table) {
            if (! Schema::hasColumn('account_setups', 'payment_schedule_start_date')) {
                $table->date('payment_schedule_start_date')->nullable()->after('billing_cycle');
            }

            if (! Schema::hasColumn('account_setups', 'payment_schedule_ack')) {
                $table->boolean('payment_schedule_ack')->default(false)->after('payment_schedule_start_date');
            }

            if (! Schema::hasColumn('account_setups', 'payment_schedule_acknowledged_at')) {
                $table->timestamp('payment_schedule_acknowledged_at')->nullable()->after('payment_schedule_ack');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('account_setups')) {
            return;
        }

        $dropColumns = array_filter([
            Schema::hasColumn('account_setups', 'payment_schedule_start_date') ? 'payment_schedule_start_date' : null,
            Schema::hasColumn('account_setups', 'payment_schedule_ack') ? 'payment_schedule_ack' : null,
            Schema::hasColumn('account_setups', 'payment_schedule_acknowledged_at') ? 'payment_schedule_acknowledged_at' : null,
        ]);

        if ($dropColumns === []) {
            return;
        }

        Schema::table('account_setups', function (Blueprint $table) use ($dropColumns) {
            $table->dropColumn($dropColumns);
        });
    }
};
