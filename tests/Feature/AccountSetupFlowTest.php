<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AccountSetup;
use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountSetupFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Country::create([
            'country_name' => 'Canada',
            'iso_code' => 'CA',
            'phone_code' => '+1',
        ]);

        config([
            'services.recaptcha.site_key' => 'test-site-key',
            'services.recaptcha.secret_key' => 'test-secret-key',
            'services.recaptcha.verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
            'services.recaptcha.allow_local_bypass_on_network_error' => true,
        ]);
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify*' => Http::response([
                'success' => true,
            ], 200),
        ]);
    }

    public function test_store_rejects_step_out_of_range(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 99,
            'data' => [],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['step']);
    }

    public function test_complete_requires_prior_steps(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup/complete');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['steps']);
    }

    public function test_complete_requires_real_user_email_verification(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $steps = [
            1 => ['company_name' => 'DeskDrop HQ'],
            2 => [
                'company_address' => '10 King St',
                'company_city' => 'Toronto',
                'company_state' => 'Ontario',
                'company_zip' => 'M5H1A1',
                'company_country' => 'Canada',
                'company_phone' => '+14165550100',
            ],
            3 => [
                'facility_name' => 'Building A',
                'facility_part_number' => 'A-100',
                'facility_address' => '20 Front St',
                'facility_city' => 'Toronto',
                'facility_state' => 'Ontario',
                'facility_zip' => 'M5J2N8',
                'facility_country' => 'Canada',
                'facility_phone' => '+14165550101',
                'facility_email' => 'facility@example.com',
            ],
            4 => [
                'facility_usage' => [
                    [
                        'name' => 'Building A',
                        'part' => 'A-100',
                        'city' => 'Toronto',
                        'country' => 'Canada',
                        'assigned' => 1,
                        'status' => 'active',
                    ],
                ],
            ],
            5 => [
                'contact_primary_phone' => '+14165550102',
                'contact_business_email' => 'ops@example.com',
            ],
            6 => [
                'pref_timezone' => 'America/Toronto',
                'pref_language' => 'English',
                'pref_date_format' => 'YYYY-MM-DD',
            ],
            7 => [
                'notify_email' => true,
                'notify_sms' => false,
            ],
            8 => [
                'security_username' => 'owner_unverified',
                'security_password' => 'SecurePass123!',
                'security_password_confirm' => 'SecurePass123!',
                'security_pin' => '9876',
                'security_policy_ack' => true,
            ],
            9 => [
                'subscription_plan' => 'standard',
            ],
            10 => [
                'license_ack' => true,
            ],
            11 => [
                'billing_method' => 'card',
                'billing_address' => '30 Bay St',
                'billing_policy_ack' => true,
            ],
            12 => [
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_role' => 'Manager',
                'admin_phone' => '+14165550104',
                'admin_email' => 'john.doe@example.com',
                'admin_created_date' => '2026-02-20',
            ],
            13 => [
                'recovery_email' => 'recovery@example.com',
            ],
            14 => [
                'email_verify_status' => 'verified',
            ],
            15 => [
                'captcha_token' => 'captcha-token-1',
            ],
            16 => [
                'safety_ack' => true,
            ],
            17 => [
                'terms_ack' => true,
                'privacy_ack' => true,
            ],
        ];

        foreach ($steps as $step => $data) {
            $this->postJson('/api/v1/account-setup', [
                'step' => $step,
                'data' => $data,
            ])->assertOk();
        }

        $this->postJson('/api/v1/account-setup/complete')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email_verify_status']);
    }

    public function test_store_saves_step_data_to_columns_and_json_backup(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 2,
            'data' => [
                'company_address' => '123 Main Street',
                'company_city' => 'Toronto',
                'company_state' => 'Ontario',
                'company_zip' => 'M5H1A1',
                'company_country' => 'Canada',
                'company_phone' => '+14165550100',
                'custom_note' => 'after_hours_entry',
            ],
        ]);

        $response->assertOk();

        $setup = AccountSetup::where('user_id', $user->id)->first();
        $this->assertNotNull($setup);
        $this->assertSame('123 Main Street', $setup->company_address);
        $this->assertSame('Toronto', $setup->company_city);
        $this->assertTrue((bool) $setup->step_2_done);

        $data = $setup->data;
        $this->assertIsArray($data);
        $this->assertSame('123 Main Street', $data['setup_columns']['company_address'] ?? null);
        $this->assertArrayNotHasKey('custom_note', $data['setup_form'] ?? []);
        $this->assertArrayNotHasKey('custom_note', $data['step_snapshots']['2']['payload'] ?? []);
        $this->assertSame(2, $data['last_saved_step'] ?? null);
    }

    public function test_step_four_rejects_more_than_two_active_facilities_for_license_assignment(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 4,
            'data' => [
                'facility_usage' => [
                    [
                        'name' => 'A',
                        'part' => 'A-1',
                        'city' => 'Toronto',
                        'country' => 'Canada',
                        'assigned' => 1,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'B',
                        'part' => 'B-1',
                        'city' => 'Toronto',
                        'country' => 'Canada',
                        'assigned' => 1,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'C',
                        'part' => 'C-1',
                        'city' => 'Toronto',
                        'country' => 'Canada',
                        'assigned' => 1,
                        'status' => 'active',
                    ],
                ],
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['facility_usage']);
    }

    public function test_step_four_allows_two_or_fewer_active_facility_assignments(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 4,
            'data' => [
                'facility_usage' => [
                    [
                        'name' => 'A',
                        'part' => 'A-1',
                        'city' => 'Toronto',
                        'country' => 'Canada',
                        'assigned' => 1,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'B',
                        'part' => 'B-1',
                        'city' => 'Toronto',
                        'country' => 'Canada',
                        'assigned' => 1,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'C',
                        'part' => 'C-1',
                        'city' => 'Toronto',
                        'country' => 'Canada',
                        'assigned' => 0,
                        'status' => 'inactive',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
    }

    public function test_step_four_rejects_unknown_country_name_when_country_id_cannot_be_resolved(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 4,
            'data' => [
                'facility_usage' => [
                    [
                        'name' => 'A',
                        'part' => 'A-1',
                        'city' => 'Toronto',
                        'country' => 'Unknownland',
                        'assigned' => 1,
                        'status' => 'active',
                    ],
                ],
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['facility_usage.0.country_id']);
    }

    public function test_step_fourteen_uses_pending_status_for_unverified_user_even_if_client_sends_verified(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 14,
            'data' => [
                'email_verify_status' => 'verified',
            ],
        ]);

        $response->assertOk();

        $setup = AccountSetup::where('user_id', $user->id)->first();
        $this->assertNotNull($setup);
        $this->assertSame('pending', $setup->email_verify_status);
        $this->assertTrue((bool) $setup->step_14_done);
    }

    public function test_step_fourteen_uses_verified_status_for_verified_user_even_if_client_sends_failed(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 14,
            'data' => [
                'email_verify_status' => 'failed',
            ],
        ]);

        $response->assertOk();

        $setup = AccountSetup::where('user_id', $user->id)->first();
        $this->assertNotNull($setup);
        $this->assertSame('verified', $setup->email_verify_status);
        $this->assertTrue((bool) $setup->step_14_done);
    }

    public function test_step_fifteen_requires_real_captcha_token_when_not_previously_verified(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 15,
            'data' => [
                'captcha_ack' => true,
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['captcha_token']);
    }

    public function test_step_fifteen_allows_local_bypass_when_captcha_service_is_unavailable(): void
    {
        Http::fake(function () {
            throw new \RuntimeException('network unavailable');
        });

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 15,
            'data' => [
                'captcha_token' => 'test-token',
            ],
        ]);

        $response->assertOk();

        $setup = AccountSetup::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($setup);
        $this->assertTrue((bool) $setup->captcha_ack);
        $this->assertTrue((bool) $setup->step_15_done);
    }

    public function test_step_sixteen_saves_safety_ack_audit_metadata_and_activity_log(): void
    {
        Carbon::setTestNow('2026-02-24 10:15:00');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this
            ->withServerVariables([
                'REMOTE_ADDR' => '203.0.113.10',
                'HTTP_USER_AGENT' => 'DropDeskTest/1.0',
            ])
            ->postJson('/api/v1/account-setup', [
                'step' => 16,
                'data' => [
                    'safety_ack' => true,
                ],
            ]);

        $response->assertOk();

        $setup = AccountSetup::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($setup);
        $this->assertTrue((bool) $setup->safety_ack);
        $this->assertSame('2026-02-24 10:15:00', optional($setup->safety_ack_at)->format('Y-m-d H:i:s'));
        $this->assertSame('203.0.113.10', $setup->safety_ack_ip);
        $this->assertSame('DropDeskTest/1.0', $setup->safety_ack_user_agent);
        $this->assertSame('2026-02-24', $setup->safety_policy_version);

        $log = ActivityLog::query()
            ->where('user_id', $user->id)
            ->where('module', 'account_setup')
            ->where('action', 'safety_ack')
            ->latest('id')
            ->first();
        $this->assertNotNull($log);
        $this->assertSame('Account safety rules accepted.', $log->description);
        $this->assertSame('2026-02-24', $log->meta_json['policy_version'] ?? null);
        $this->assertSame('203.0.113.10', $log->meta_json['ip'] ?? null);
        $this->assertSame('DropDeskTest/1.0', $log->meta_json['user_agent'] ?? null);

        Carbon::setTestNow();
    }

    public function test_step_sixteen_re_submit_keeps_original_safety_audit_snapshot(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Carbon::setTestNow('2026-02-24 09:00:00');
        $this
            ->withServerVariables([
                'REMOTE_ADDR' => '203.0.113.20',
                'HTTP_USER_AGENT' => 'DropDeskTest/first',
            ])
            ->postJson('/api/v1/account-setup', [
                'step' => 16,
                'data' => [
                    'safety_ack' => true,
                ],
            ])->assertOk();

        Carbon::setTestNow('2026-02-24 12:00:00');
        $this
            ->withServerVariables([
                'REMOTE_ADDR' => '198.51.100.77',
                'HTTP_USER_AGENT' => 'DropDeskTest/second',
            ])
            ->postJson('/api/v1/account-setup', [
                'step' => 16,
                'data' => [
                    'safety_ack' => true,
                ],
            ])->assertOk();

        $setup = AccountSetup::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($setup);
        $this->assertSame('2026-02-24 09:00:00', optional($setup->safety_ack_at)->format('Y-m-d H:i:s'));
        $this->assertSame('203.0.113.20', $setup->safety_ack_ip);
        $this->assertSame('DropDeskTest/first', $setup->safety_ack_user_agent);
        $this->assertSame('2026-02-24', $setup->safety_policy_version);
        $this->assertSame(1, ActivityLog::query()
            ->where('user_id', $user->id)
            ->where('module', 'account_setup')
            ->where('action', 'safety_ack')
            ->count());

        Carbon::setTestNow();
    }

    public function test_step_twelve_maps_other_role_to_custom_role_value(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 12,
            'data' => [
                'admin_first_name' => 'Jane',
                'admin_last_name' => 'Doe',
                'admin_role' => 'Other',
                'admin_role_other' => 'Regional Compliance Lead',
                'admin_phone' => '+14165550104',
                'admin_email' => 'jane.doe@example.com',
                'admin_created_date' => '2026-02-20',
            ],
        ]);

        $response->assertOk();

        $setup = AccountSetup::where('user_id', $user->id)->first();
        $this->assertNotNull($setup);
        $this->assertSame('Regional Compliance Lead', $setup->admin_role);

        $data = $setup->data;
        $this->assertSame('Regional Compliance Lead', $data['setup_columns']['admin_role'] ?? null);
        $this->assertSame('Regional Compliance Lead', $data['setup_form']['admin_role'] ?? null);
        $this->assertArrayNotHasKey('admin_role_other', $data['setup_form'] ?? []);
    }

    public function test_step_twelve_rejects_other_role_without_custom_value(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 12,
            'data' => [
                'admin_first_name' => 'Jane',
                'admin_last_name' => 'Doe',
                'admin_role' => 'Other',
                'admin_phone' => '+14165550104',
                'admin_email' => 'jane.doe@example.com',
                'admin_created_date' => '2026-02-20',
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['admin_role']);
    }

    public function test_store_only_persists_columns_for_current_step(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 9,
            'data' => [
                'subscription_plan' => 'enterprise',
                'company_name' => 'Should Not Save Here',
            ],
        ]);

        $response->assertOk();

        $setup = AccountSetup::where('user_id', $user->id)->first();
        $this->assertNotNull($setup);
        $this->assertSame('enterprise', $setup->subscription_plan);
        $this->assertNull($setup->company_name);

        $data = $setup->data;
        $this->assertIsArray($data);
        $this->assertSame('enterprise', $data['setup_columns']['subscription_plan'] ?? null);
        $this->assertArrayNotHasKey('company_name', $data['setup_columns'] ?? []);
    }

    public function test_step_eight_updates_login_credentials_and_security_hashes(): void
    {
        $user = User::factory()->create([
            'username' => 'old_username',
            'password' => 'OldPassword123!',
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account-setup', [
            'step' => 8,
            'data' => [
                'security_username' => 'new_username',
                'security_password' => 'NewPassword123!',
                'security_password_confirm' => 'NewPassword123!',
                'security_pin' => '1234',
                'security_policy_ack' => true,
            ],
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertSame('new_username', $user->username);
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));

        $setup = AccountSetup::where('user_id', $user->id)->first();
        $this->assertNotNull($setup);
        $this->assertSame('new_username', $setup->security_username);
        $this->assertTrue(Hash::check('NewPassword123!', $setup->security_password_hash));
        $this->assertTrue(Hash::check('1234', $setup->security_pin_hash));
        $this->assertTrue((bool) $setup->step_8_done);

        $data = $setup->data;
        $this->assertIsArray($data);
        $this->assertArrayNotHasKey('security_password', $data['setup_form'] ?? []);
        $this->assertArrayNotHasKey('security_pin', $data['setup_form'] ?? []);
        $this->assertTrue((bool) ($data['setup_form']['security_password_configured'] ?? false));
        $this->assertTrue((bool) ($data['setup_form']['security_pin_configured'] ?? false));
        $this->assertNotEmpty($data['setup_form']['security_password_hash'] ?? null);
        $this->assertNotEmpty($data['setup_form']['security_pin_hash'] ?? null);
    }

    public function test_full_setup_flow_persists_step_fields_in_dedicated_columns(): void
    {
        $user = User::factory()->create([
            'username' => 'owner_old',
        ]);
        Sanctum::actingAs($user);

        $steps = [
            1 => [
                'company_name' => 'DeskDrop HQ',
                'company_logo_url' => '/storage/account-logos/logo.png',
                'company_logo_path' => 'account-logos/logo.png',
            ],
            2 => [
                'company_address' => '10 King St',
                'company_city' => 'Toronto',
                'company_state' => 'Ontario',
                'company_zip' => 'M5H1A1',
                'company_country' => 'Canada',
                'company_phone' => '+14165550100',
            ],
            3 => [
                'facility_name' => 'Building A',
                'facility_part_number' => 'A-100',
                'facility_address' => '20 Front St',
                'facility_city' => 'Toronto',
                'facility_state' => 'Ontario',
                'facility_zip' => 'M5J2N8',
                'facility_country' => 'Canada',
                'facility_phone' => '+14165550101',
                'facility_email' => 'facility@example.com',
            ],
            4 => [
                'facility_usage' => [[
                    'name' => 'Building A',
                    'part' => 'A-100',
                    'city' => 'Toronto',
                    'country' => 'Canada',
                    'assigned' => 12,
                    'status' => 'active',
                ]],
            ],
            5 => [
                'contact_primary_phone' => '+14165550102',
                'contact_mobile_phone' => '+14165550103',
                'contact_business_email' => 'ops@example.com',
                'contact_alt_email' => 'alt@example.com',
                'contact_website' => 'https://example.com',
            ],
            6 => [
                'pref_timezone' => 'America/Toronto',
                'pref_language' => 'English',
                'pref_date_format' => 'YYYY-MM-DD',
            ],
            7 => [
                'notify_email' => true,
                'notify_sms' => true,
                'notify_arrival' => true,
                'notify_security' => false,
                'notify_marketing' => false,
            ],
            8 => [
                'security_username' => 'owner_new',
                'security_password' => 'SecurePass123!',
                'security_password_confirm' => 'SecurePass123!',
                'security_pin' => '9876',
                'security_2fa' => true,
                'security_policy_ack' => true,
            ],
            9 => [
                'subscription_plan' => 'standard',
            ],
            10 => [
                'license_ack' => true,
            ],
            11 => [
                'billing_method' => 'card',
                'billing_address' => '30 Bay St',
                'billing_cycle' => 'Monthly',
                'billing_auto_renew' => true,
                'billing_policy_ack' => true,
            ],
            12 => [
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_role' => 'Manager',
                'admin_is_system' => true,
                'admin_phone' => '+14165550104',
                'admin_email' => 'john.doe@example.com',
                'admin_created_date' => '2026-02-20',
            ],
            13 => [
                'recovery_email' => 'recovery@example.com',
                'recovery_phone' => '+14165550105',
            ],
            14 => [
                'email_verify_status' => 'verified',
            ],
            15 => [
                'captcha_token' => 'captcha-token-2',
            ],
            16 => [
                'safety_ack' => true,
            ],
            17 => [
                'terms_ack' => true,
                'privacy_ack' => true,
            ],
        ];

        foreach ($steps as $step => $data) {
            $this->postJson('/api/v1/account-setup', [
                'step' => $step,
                'data' => $data,
            ])->assertOk();
        }

        $this->postJson('/api/v1/account-setup/complete')->assertOk();

        $setup = AccountSetup::where('user_id', $user->id)->first();
        $this->assertNotNull($setup);

        $expectedColumns = [
            'company_name' => 'DeskDrop HQ',
            'company_logo_url' => '/storage/account-logos/logo.png',
            'company_logo_path' => 'account-logos/logo.png',
            'company_address' => '10 King St',
            'company_city' => 'Toronto',
            'company_state' => 'Ontario',
            'company_zip' => 'M5H1A1',
            'company_country' => 'Canada',
            'company_phone' => '+14165550100',
            'facility_name' => 'Building A',
            'facility_part_number' => 'A-100',
            'facility_address' => '20 Front St',
            'facility_city' => 'Toronto',
            'facility_state' => 'Ontario',
            'facility_zip' => 'M5J2N8',
            'facility_country' => 'Canada',
            'facility_phone' => '+14165550101',
            'facility_email' => 'facility@example.com',
            'contact_primary_phone' => '+14165550102',
            'contact_mobile_phone' => '+14165550103',
            'contact_business_email' => 'ops@example.com',
            'contact_alt_email' => 'alt@example.com',
            'contact_website' => 'https://example.com',
            'pref_timezone' => 'America/Toronto',
            'pref_language' => 'English',
            'pref_date_format' => 'YYYY-MM-DD',
            'notify_email' => true,
            'notify_sms' => true,
            'notify_arrival' => true,
            'notify_security' => false,
            'notify_marketing' => false,
            'security_username' => 'owner_new',
            'security_2fa' => true,
            'security_policy_ack' => true,
            'subscription_plan' => 'standard',
            'license_ack' => true,
            'billing_method' => 'card',
            'billing_address' => '30 Bay St',
            'billing_cycle' => 'Monthly',
            'billing_auto_renew' => true,
            'billing_policy_ack' => true,
            'admin_first_name' => 'John',
            'admin_last_name' => 'Doe',
            'admin_role' => 'Manager',
            'admin_is_system' => true,
            'admin_phone' => '+14165550104',
            'admin_email' => 'john.doe@example.com',
            'recovery_email' => 'recovery@example.com',
            'recovery_phone' => '+14165550105',
            'email_verify_status' => 'verified',
            'captcha_ack' => true,
            'safety_ack' => true,
            'terms_ack' => true,
            'privacy_ack' => true,
            'final_ack' => true,
        ];

        foreach ($expectedColumns as $field => $value) {
            $this->assertSame($value, $setup->{$field}, "Mismatch on {$field}");
        }

        $this->assertNotEmpty($setup->security_password_hash);
        $this->assertNotEmpty($setup->security_pin_hash);
        $this->assertSame(18, (int) $setup->current_step);
        $this->assertNotNull($setup->completed_at);
        $canadaId = Country::query()->where('country_name', 'Canada')->value('id');
        $this->assertSame($canadaId, $setup->company_country_id);
        $this->assertSame($canadaId, $setup->facility_country_id);

        for ($i = 1; $i <= 18; $i += 1) {
            $this->assertTrue((bool) $setup->{'step_' . $i . '_done'});
        }

        $facilityRows = $setup->facilityUsage()->get();
        $this->assertCount(1, $facilityRows);
        $this->assertSame('Building A', $facilityRows[0]->name);
        $this->assertSame('A-100', $facilityRows[0]->part);
        $this->assertSame('Toronto', $facilityRows[0]->city);
        $this->assertSame('Canada', $facilityRows[0]->country);
        $this->assertSame($canadaId, $facilityRows[0]->country_id);
        $this->assertSame(12, $facilityRows[0]->assigned);
        $this->assertSame('active', $facilityRows[0]->status);
    }
}

