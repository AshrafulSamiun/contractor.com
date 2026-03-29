<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_settings', 'notify_parcel_arrival')) {
                $table->boolean('notify_parcel_arrival')->default(true)->after('holding_auto_expire');
            }
            if (!Schema::hasColumn('user_settings', 'notify_pickup_request')) {
                $table->boolean('notify_pickup_request')->default(true)->after('notify_parcel_arrival');
            }
            if (!Schema::hasColumn('user_settings', 'notify_parcel_return')) {
                $table->boolean('notify_parcel_return')->default(true)->after('notify_pickup_request');
            }
            if (!Schema::hasColumn('user_settings', 'notify_channel_email')) {
                $table->boolean('notify_channel_email')->default(true)->after('notify_parcel_return');
            }
            if (!Schema::hasColumn('user_settings', 'notify_channel_sms')) {
                $table->boolean('notify_channel_sms')->default(false)->after('notify_channel_email');
            }
            if (!Schema::hasColumn('user_settings', 'notify_channel_in_app')) {
                $table->boolean('notify_channel_in_app')->default(true)->after('notify_channel_sms');
            }
            if (!Schema::hasColumn('user_settings', 'use_global_notifications')) {
                $table->boolean('use_global_notifications')->default(true)->after('notify_channel_in_app');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_settings', 'notify_channel_in_app')) {
                $table->dropColumn('notify_channel_in_app');
            }
            if (Schema::hasColumn('user_settings', 'notify_channel_sms')) {
                $table->dropColumn('notify_channel_sms');
            }
            if (Schema::hasColumn('user_settings', 'notify_channel_email')) {
                $table->dropColumn('notify_channel_email');
            }
            if (Schema::hasColumn('user_settings', 'notify_parcel_return')) {
                $table->dropColumn('notify_parcel_return');
            }
            if (Schema::hasColumn('user_settings', 'notify_pickup_request')) {
                $table->dropColumn('notify_pickup_request');
            }
            if (Schema::hasColumn('user_settings', 'notify_parcel_arrival')) {
                $table->dropColumn('notify_parcel_arrival');
            }
        });
    }
};
