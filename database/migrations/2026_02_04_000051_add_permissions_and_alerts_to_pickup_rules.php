<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pickup_rules', function (Blueprint $table) {
            $table->json('allowed_roles')->nullable()->after('type');
            $table->unsignedSmallInteger('reminder_frequency_hours')->default(24)->after('reminder_enabled');
            $table->timestamp('last_reminder_at')->nullable()->after('reminder_frequency_hours');
            $table->timestamp('sla_breach_notified_at')->nullable()->after('last_reminder_at');
        });
    }

    public function down(): void
    {
        Schema::table('pickup_rules', function (Blueprint $table) {
            $table->dropColumn([
                'allowed_roles',
                'reminder_frequency_hours',
                'last_reminder_at',
                'sla_breach_notified_at',
            ]);
        });
    }
};
