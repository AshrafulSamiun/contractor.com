<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_settings', 'notify_delivery_completed')) {
                $table->boolean('notify_delivery_completed')->default(true)->after('notify_parcel_return');
            }
            if (!Schema::hasColumn('user_settings', 'notify_rejected_parcel')) {
                $table->boolean('notify_rejected_parcel')->default(true)->after('notify_delivery_completed');
            }
            if (!Schema::hasColumn('user_settings', 'notify_lost_damaged')) {
                $table->boolean('notify_lost_damaged')->default(true)->after('notify_rejected_parcel');
            }
            if (!Schema::hasColumn('user_settings', 'notify_expiry_reminder')) {
                $table->boolean('notify_expiry_reminder')->default(true)->after('notify_lost_damaged');
            }

            if (!Schema::hasColumn('user_settings', 'notify_channel_whatsapp')) {
                $table->boolean('notify_channel_whatsapp')->default(false)->after('notify_channel_in_app');
            }
            if (!Schema::hasColumn('user_settings', 'notify_channel_push')) {
                $table->boolean('notify_channel_push')->default(true)->after('notify_channel_whatsapp');
            }

            if (!Schema::hasColumn('user_settings', 'quiet_hours_enabled')) {
                $table->boolean('quiet_hours_enabled')->default(false)->after('notify_channel_push');
            }
            if (!Schema::hasColumn('user_settings', 'quiet_hours_start')) {
                $table->time('quiet_hours_start')->nullable()->after('quiet_hours_enabled');
            }
            if (!Schema::hasColumn('user_settings', 'quiet_hours_end')) {
                $table->time('quiet_hours_end')->nullable()->after('quiet_hours_start');
            }

            if (!Schema::hasColumn('user_settings', 'template_email_subject')) {
                $table->string('template_email_subject', 120)->default('Parcel update')->after('quiet_hours_end');
            }
            if (!Schema::hasColumn('user_settings', 'template_email_body')) {
                $table->text('template_email_body')->nullable()->after('template_email_subject');
            }
            if (!Schema::hasColumn('user_settings', 'template_sms_body')) {
                $table->string('template_sms_body', 160)->nullable()->after('template_email_body');
            }
            if (!Schema::hasColumn('user_settings', 'template_in_app_body')) {
                $table->string('template_in_app_body', 160)->nullable()->after('template_sms_body');
            }
            if (!Schema::hasColumn('user_settings', 'template_whatsapp_body')) {
                $table->string('template_whatsapp_body', 300)->nullable()->after('template_in_app_body');
            }
            if (!Schema::hasColumn('user_settings', 'template_push_title')) {
                $table->string('template_push_title', 80)->nullable()->after('template_whatsapp_body');
            }
            if (!Schema::hasColumn('user_settings', 'template_push_body')) {
                $table->string('template_push_body', 160)->nullable()->after('template_push_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_settings', 'template_push_body')) {
                $table->dropColumn('template_push_body');
            }
            if (Schema::hasColumn('user_settings', 'template_push_title')) {
                $table->dropColumn('template_push_title');
            }
            if (Schema::hasColumn('user_settings', 'template_whatsapp_body')) {
                $table->dropColumn('template_whatsapp_body');
            }
            if (Schema::hasColumn('user_settings', 'template_in_app_body')) {
                $table->dropColumn('template_in_app_body');
            }
            if (Schema::hasColumn('user_settings', 'template_sms_body')) {
                $table->dropColumn('template_sms_body');
            }
            if (Schema::hasColumn('user_settings', 'template_email_body')) {
                $table->dropColumn('template_email_body');
            }
            if (Schema::hasColumn('user_settings', 'template_email_subject')) {
                $table->dropColumn('template_email_subject');
            }
            if (Schema::hasColumn('user_settings', 'quiet_hours_end')) {
                $table->dropColumn('quiet_hours_end');
            }
            if (Schema::hasColumn('user_settings', 'quiet_hours_start')) {
                $table->dropColumn('quiet_hours_start');
            }
            if (Schema::hasColumn('user_settings', 'quiet_hours_enabled')) {
                $table->dropColumn('quiet_hours_enabled');
            }
            if (Schema::hasColumn('user_settings', 'notify_channel_push')) {
                $table->dropColumn('notify_channel_push');
            }
            if (Schema::hasColumn('user_settings', 'notify_channel_whatsapp')) {
                $table->dropColumn('notify_channel_whatsapp');
            }
            if (Schema::hasColumn('user_settings', 'notify_expiry_reminder')) {
                $table->dropColumn('notify_expiry_reminder');
            }
            if (Schema::hasColumn('user_settings', 'notify_lost_damaged')) {
                $table->dropColumn('notify_lost_damaged');
            }
            if (Schema::hasColumn('user_settings', 'notify_rejected_parcel')) {
                $table->dropColumn('notify_rejected_parcel');
            }
            if (Schema::hasColumn('user_settings', 'notify_delivery_completed')) {
                $table->dropColumn('notify_delivery_completed');
            }
        });
    }
};
