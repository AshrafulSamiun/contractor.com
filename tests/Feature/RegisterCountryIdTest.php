<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterCountryIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_requires_phone_number(): void
    {
        config(['captcha.disable' => true]);

        $country = Country::create([
            'country_name' => 'Bangladesh',
            'iso_code' => 'BD',
            'phone_code' => '+880',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'company' => 'Test Company',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country_id' => $country->id,
            'zip' => '1207',
            'verifyVia' => 'email',
            'captcha_key' => 'dummy',
            'captcha_value' => 'dummy',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phoneNo']);
    }

    public function test_register_accepts_country_id_and_persists_country_fields(): void
    {
        config(['captcha.disable' => true]);

        $country = Country::create([
            'country_name' => 'Bangladesh',
            'iso_code' => 'BD',
            'phone_code' => '+880',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'company' => 'Test Company',
            'username' => 'testuservalid',
            'email' => 'test@example.com',
            'phoneNo' => '+8801700000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country_id' => $country->id,
            'zip' => '1207',
            'verifyVia' => 'email',
            'captcha_key' => 'dummy',
            'captcha_value' => 'dummy',
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();
        $this->assertSame($country->id, $user->country_id);
        $this->assertSame('Bangladesh', $user->country);
    }

    public function test_register_rejects_non_e164_phone_number(): void
    {
        config(['captcha.disable' => true]);

        $country = Country::create([
            'country_name' => 'Bangladesh',
            'iso_code' => 'BD',
            'phone_code' => '+880',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'company' => 'Test Company',
            'username' => 'testuserinvalid',
            'email' => 'invalid-phone@example.com',
            'phoneNo' => '01700000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country_id' => $country->id,
            'zip' => '1207',
            'verifyVia' => 'email',
            'captcha_key' => 'dummy',
            'captcha_value' => 'dummy',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phoneNo']);
    }
}
