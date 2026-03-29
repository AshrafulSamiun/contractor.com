<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_setups', function (Blueprint $table) {
            $table->string('company_name')->nullable();
            $table->string('company_logo_url')->nullable();
            $table->string('company_logo_path')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_city')->nullable();
            $table->string('company_state')->nullable();
            $table->string('company_zip')->nullable();
            $table->string('company_country')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('facility_name')->nullable();
            $table->string('facility_part_number')->nullable();
            $table->string('facility_address')->nullable();
            $table->string('facility_city')->nullable();
            $table->string('facility_state')->nullable();
            $table->string('facility_zip')->nullable();
            $table->string('facility_country')->nullable();
            $table->string('facility_phone')->nullable();
            $table->string('facility_email')->nullable();
            $table->string('contact_primary_phone')->nullable();
            $table->string('contact_mobile_phone')->nullable();
            $table->string('contact_business_email')->nullable();
            $table->string('contact_alt_email')->nullable();
            $table->string('contact_website')->nullable();
            $table->string('pref_timezone')->nullable();
            $table->string('pref_language')->nullable();
            $table->string('pref_date_format')->nullable();
            $table->boolean('notify_email')->default(false);
            $table->boolean('notify_sms')->default(false);
            $table->boolean('notify_arrival')->default(false);
            $table->boolean('notify_security')->default(false);
            $table->boolean('notify_marketing')->default(false);
            $table->string('security_username')->nullable();
            $table->string('security_password_hash')->nullable();
            $table->string('security_pin_hash')->nullable();
            $table->boolean('security_2fa')->default(false);
            $table->boolean('security_policy_ack')->default(false);
            $table->string('subscription_plan')->nullable();
            $table->boolean('license_ack')->default(false);
            $table->string('billing_method')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_cycle')->nullable();
            $table->boolean('billing_auto_renew')->default(false);
            $table->boolean('billing_policy_ack')->default(false);
            $table->string('admin_first_name')->nullable();
            $table->string('admin_last_name')->nullable();
            $table->string('admin_role')->nullable();
            $table->boolean('admin_is_system')->default(false);
            $table->string('admin_phone')->nullable();
            $table->string('admin_email')->nullable();
            $table->date('admin_created_date')->nullable();
            $table->string('recovery_email')->nullable();
            $table->string('recovery_phone')->nullable();
            $table->string('email_verify_status')->default('pending');
            $table->boolean('captcha_ack')->default(false);
            $table->boolean('safety_ack')->default(false);
            $table->boolean('terms_ack')->default(false);
            $table->boolean('privacy_ack')->default(false);
            $table->boolean('final_ack')->default(false);
            $table->boolean('step_1_done')->default(false);
            $table->boolean('step_2_done')->default(false);
            $table->boolean('step_3_done')->default(false);
            $table->boolean('step_4_done')->default(false);
            $table->boolean('step_5_done')->default(false);
            $table->boolean('step_6_done')->default(false);
            $table->boolean('step_7_done')->default(false);
            $table->boolean('step_8_done')->default(false);
            $table->boolean('step_9_done')->default(false);
            $table->boolean('step_10_done')->default(false);
            $table->boolean('step_11_done')->default(false);
            $table->boolean('step_12_done')->default(false);
            $table->boolean('step_13_done')->default(false);
            $table->boolean('step_14_done')->default(false);
            $table->boolean('step_15_done')->default(false);
            $table->boolean('step_16_done')->default(false);
            $table->boolean('step_17_done')->default(false);
            $table->boolean('step_18_done')->default(false);
        });

        if (!Schema::hasColumn('account_setups', 'data')) {
            return;
        }

        $rows = DB::table('account_setups')
            ->select('id', 'current_step', 'data')
            ->whereNotNull('data')
            ->get();

        $columns = [
            'company_name',
            'company_logo_url',
            'company_logo_path',
            'company_address',
            'company_city',
            'company_state',
            'company_zip',
            'company_country',
            'company_phone',
            'facility_name',
            'facility_part_number',
            'facility_address',
            'facility_city',
            'facility_state',
            'facility_zip',
            'facility_country',
            'facility_phone',
            'facility_email',
            'contact_primary_phone',
            'contact_mobile_phone',
            'contact_business_email',
            'contact_alt_email',
            'contact_website',
            'pref_timezone',
            'pref_language',
            'pref_date_format',
            'notify_email',
            'notify_sms',
            'notify_arrival',
            'notify_security',
            'notify_marketing',
            'security_username',
            'security_2fa',
            'security_policy_ack',
            'subscription_plan',
            'license_ack',
            'billing_method',
            'billing_address',
            'billing_cycle',
            'billing_auto_renew',
            'billing_policy_ack',
            'admin_first_name',
            'admin_last_name',
            'admin_role',
            'admin_is_system',
            'admin_phone',
            'admin_email',
            'admin_created_date',
            'recovery_email',
            'recovery_phone',
            'email_verify_status',
            'captcha_ack',
            'safety_ack',
            'terms_ack',
            'privacy_ack',
            'final_ack',
        ];

        foreach ($rows as $row) {
            $data = json_decode($row->data, true) ?? [];
            if (!is_array($data)) {
                continue;
            }

            $update = [];
            foreach ($columns as $column) {
                if (array_key_exists($column, $data)) {
                    $update[$column] = $data[$column];
                }
            }

            if (!empty($data['security_password'])) {
                $update['security_password_hash'] = Hash::make($data['security_password']);
            }
            if (!empty($data['security_pin'])) {
                $update['security_pin_hash'] = Hash::make($data['security_pin']);
            }

            $currentStep = (int) ($row->current_step ?? 0);
            if ($currentStep > 0) {
                for ($i = 1; $i <= 18; $i += 1) {
                    if ($i <= $currentStep) {
                        $update['step_' . $i . '_done'] = true;
                    }
                }
            }

            if (!empty($update)) {
                DB::table('account_setups')->where('id', $row->id)->update($update);
            }

            if (!empty($data['facility_usage']) && is_array($data['facility_usage'])) {
                foreach ($data['facility_usage'] as $facility) {
                    if (!is_array($facility)) {
                        continue;
                    }
                    DB::table('account_setup_facilities')->insert([
                        'account_setup_id' => $row->id,
                        'name' => $facility['name'] ?? null,
                        'part' => $facility['part'] ?? null,
                        'city' => $facility['city'] ?? null,
                        'country' => $facility['country'] ?? null,
                        'assigned' => (int) ($facility['assigned'] ?? 0),
                        'status' => $facility['status'] ?? 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('account_setups', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'company_logo_url',
                'company_logo_path',
                'company_address',
                'company_city',
                'company_state',
                'company_zip',
                'company_country',
                'company_phone',
                'facility_name',
                'facility_part_number',
                'facility_address',
                'facility_city',
                'facility_state',
                'facility_zip',
                'facility_country',
                'facility_phone',
                'facility_email',
                'contact_primary_phone',
                'contact_mobile_phone',
                'contact_business_email',
                'contact_alt_email',
                'contact_website',
                'pref_timezone',
                'pref_language',
                'pref_date_format',
                'notify_email',
                'notify_sms',
                'notify_arrival',
                'notify_security',
                'notify_marketing',
                'security_username',
                'security_password_hash',
                'security_pin_hash',
                'security_2fa',
                'security_policy_ack',
                'subscription_plan',
                'license_ack',
                'billing_method',
                'billing_address',
                'billing_cycle',
                'billing_auto_renew',
                'billing_policy_ack',
                'admin_first_name',
                'admin_last_name',
                'admin_role',
                'admin_is_system',
                'admin_phone',
                'admin_email',
                'admin_created_date',
                'recovery_email',
                'recovery_phone',
                'email_verify_status',
                'captcha_ack',
                'safety_ack',
                'terms_ack',
                'privacy_ack',
                'final_ack',
                'step_1_done',
                'step_2_done',
                'step_3_done',
                'step_4_done',
                'step_5_done',
                'step_6_done',
                'step_7_done',
                'step_8_done',
                'step_9_done',
                'step_10_done',
                'step_11_done',
                'step_12_done',
                'step_13_done',
                'step_14_done',
                'step_15_done',
                'step_16_done',
                'step_17_done',
                'step_18_done',
            ]);
        });
    }
};
