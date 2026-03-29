<?php

namespace Tests\Feature;

use App\Models\AccountSetup;
use App\Models\Courier;
use App\Models\Country;
use App\Models\Facility;
use App\Models\Recipient;
use App\Models\SalesChatSession;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CountryIdStandardizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_saves_country_id_and_country_name(): void
    {
        $country = Country::create([
            'country_name' => 'Canada',
            'iso_code' => 'CA',
            'phone_code' => '+1',
        ]);

        $user = User::factory()->create([
            'role' => 'manager',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/account/profile', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+1 (416) 555-0100',
            'country_id' => $country->id,
        ])->assertOk();

        $user->refresh();
        $this->assertSame($country->id, $user->country_id);
        $this->assertSame('Canada', $user->country);
        $this->assertSame('+14165550100', $user->phone);
    }

    public function test_facility_store_saves_country_id_and_country_name(): void
    {
        $country = Country::create([
            'country_name' => 'Bangladesh',
            'iso_code' => 'BD',
            'phone_code' => '+880',
        ]);

        $user = User::factory()->create([
            'role' => 'admin',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/facilities', [
            'facility_name' => 'Tower One',
            'facility_type' => 'Residential',
            'country_id' => $country->id,
            'office_phone' => '+880 1700-000000',
        ])->assertCreated();

        $facility = Facility::query()->firstOrFail();
        $this->assertSame($country->id, $facility->country_id);
        $this->assertSame('Bangladesh', $facility->country);
        $this->assertSame('+8801700000000', $facility->office_phone);
    }

    public function test_account_setup_step_two_saves_country_id_and_country_name(): void
    {
        $country = Country::create([
            'country_name' => 'United States',
            'iso_code' => 'US',
            'phone_code' => '+1',
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/account-setup', [
            'step' => 2,
            'data' => [
                'company_address' => '123 Main Street',
                'company_city' => 'Austin',
                'company_state' => 'Texas',
                'company_zip' => '78701',
                'company_country_id' => $country->id,
                'company_phone' => '+1555010000',
            ],
        ])->assertOk();

        $setup = AccountSetup::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame($country->id, $setup->company_country_id);
        $this->assertSame('United States', $setup->company_country);
    }

    public function test_sales_chat_saves_country_id_and_country_name(): void
    {
        $country = Country::create([
            'country_name' => 'United Kingdom',
            'iso_code' => 'GB',
            'phone_code' => '+44',
        ]);

        $this->postJson('/api/v1/sales-chat/reply', [
            'chat_no' => 'CHAT-20260223-1234',
            'message' => 'Pricing details please',
            'first_name' => 'Alex',
            'last_name' => 'Stone',
            'company_name' => 'DeskDrop Ltd',
            'work_email' => 'alex@deskdrop.io',
            'business_phone' => '+44 20 7946 0018',
            'country_id' => $country->id,
            'city' => 'London',
            'call_time' => now()->addDay()->format('Y-m-d H:i'),
            'inquiry' => 'Looking for enterprise setup.',
        ])->assertOk();

        $session = SalesChatSession::query()->where('chat_no', 'CHAT-20260223-1234')->firstOrFail();
        $this->assertSame($country->id, $session->country_id);
        $this->assertSame('United Kingdom', $session->country);
        $this->assertSame('+442079460018', $session->business_phone);
    }

    public function test_account_profile_rejects_invalid_phone_format(): void
    {
        $user = User::factory()->create([
            'role' => 'manager',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/account/profile', [
            'name' => 'Jane Doe',
            'email' => $user->email,
            'phone' => '123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_facility_store_rejects_invalid_phone_format(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/facilities', [
            'facility_name' => 'Tower Two',
            'office_phone' => '123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['office_phone']);
    }

    public function test_sales_chat_rejects_invalid_business_phone_format(): void
    {
        $this->postJson('/api/v1/sales-chat/reply', [
            'chat_no' => 'CHAT-20260223-2222',
            'message' => 'Need help',
            'business_phone' => '123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['business_phone']);
    }

    public function test_courier_store_normalizes_phone_number(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/couriers', [
            'company_name' => 'Fast Courier',
            'phone' => '+1 (646) 555-0100',
            'is_active' => true,
        ])->assertCreated();

        $courier = Courier::query()->firstOrFail();
        $this->assertSame('+16465550100', $courier->phone);
    }

    public function test_courier_store_rejects_invalid_phone_number(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/couriers', [
            'company_name' => 'Fast Courier',
            'phone' => '123',
            'is_active' => true,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_seller_store_normalizes_phone_number(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/sellers', [
            'seller_name' => 'North Vendor',
            'phone' => '+1 (212) 555-0199',
            'is_active' => true,
        ])->assertCreated();

        $seller = Seller::query()->firstOrFail();
        $this->assertSame('+12125550199', $seller->phone);
    }

    public function test_seller_store_rejects_invalid_phone_number(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/sellers', [
            'seller_name' => 'North Vendor',
            'phone' => '123',
            'is_active' => true,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_recipient_store_normalizes_phone_number(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/recipients', [
            'recipient_name' => 'Jane Receiver',
            'recipient_types' => ['Tenant'],
            'facility_name' => 'Tower One',
            'phone' => '+1 (718) 555-0101',
            'is_active' => true,
        ])->assertCreated();

        $recipient = Recipient::query()->firstOrFail();
        $this->assertSame('+17185550101', $recipient->phone);
    }

    public function test_recipient_store_rejects_invalid_phone_number(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/recipients', [
            'recipient_name' => 'Jane Receiver',
            'recipient_types' => ['Tenant'],
            'facility_name' => 'Tower One',
            'phone' => '123',
            'is_active' => true,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_courier_list_phone_filter_matches_non_e164_input(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        Courier::create([
            'user_id' => $user->id,
            'company_name' => 'North Fast',
            'phone' => '+16465550100',
            'is_active' => true,
        ]);
        Courier::create([
            'user_id' => $user->id,
            'company_name' => 'South Fast',
            'phone' => '+14165550100',
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/couriers?phone=(646) 555-0100')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.phone', '+16465550100');
    }

    public function test_seller_list_phone_filter_matches_digits_input(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        Seller::create([
            'user_id' => $user->id,
            'seller_name' => 'North Vendor',
            'phone' => '+12125550199',
            'is_active' => true,
        ]);
        Seller::create([
            'user_id' => $user->id,
            'seller_name' => 'West Vendor',
            'phone' => '+14165550123',
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/sellers?phone=2125550199')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.phone', '+12125550199');
    }

    public function test_recipient_list_phone_filter_matches_non_e164_input(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'selected_plan' => 'enterprise',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user);

        Recipient::create([
            'user_id' => $user->id,
            'recipient_name' => 'North Resident',
            'recipient_types' => ['Tenant'],
            'facility_name' => 'Tower One',
            'phone' => '+17185550101',
            'is_active' => true,
        ]);
        Recipient::create([
            'user_id' => $user->id,
            'recipient_name' => 'West Resident',
            'recipient_types' => ['Tenant'],
            'facility_name' => 'Tower One',
            'phone' => '+14165550123',
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/recipients?phone=(718) 555-0101')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.phone', '+17185550101');
    }
}
