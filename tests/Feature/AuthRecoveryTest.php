<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_username_request_sends_reminder_for_existing_user(): void
    {
        config(['captcha.disable' => true]);

        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'username' => 'owner_admin',
        ]);

        $this->postJson('/api/v1/forgot-username/request', [
            'email' => 'owner@example.com',
            'captcha_key' => 'dummy',
            'captcha_value' => 'dummy',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $user->id,
            'channel' => 'email',
            'status' => 'sent',
            'context' => 'username_reminder',
            'to' => 'owner@example.com',
        ]);
    }

    public function test_forgot_username_request_returns_success_for_unknown_email(): void
    {
        config(['captcha.disable' => true]);

        $this->postJson('/api/v1/forgot-username/request', [
            'email' => 'unknown@example.com',
            'captcha_key' => 'dummy',
            'captcha_value' => 'dummy',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_forgot_password_request_stores_reset_code_and_logs_notification(): void
    {
        config(['captcha.disable' => true]);

        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        $this->postJson('/api/v1/forgot-password/request', [
            'email' => 'reset@example.com',
            'captcha_key' => 'dummy',
            'captcha_value' => 'dummy',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $cacheKey = 'password_reset_' . sha1('reset@example.com');
        $session = Cache::get($cacheKey);
        $this->assertIsArray($session);
        $this->assertSame($user->id, $session['user_id'] ?? null);
        $this->assertSame(6, strlen((string) ($session['code'] ?? '')));

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $user->id,
            'channel' => 'email',
            'status' => 'sent',
            'context' => 'password_reset',
            'to' => 'reset@example.com',
        ]);
    }

    public function test_forgot_password_reset_updates_password_with_valid_code(): void
    {
        config(['captcha.disable' => true]);

        $user = User::factory()->create([
            'email' => 'recover@example.com',
            'password' => 'OldPassword123!',
        ]);

        $this->postJson('/api/v1/forgot-password/request', [
            'email' => 'recover@example.com',
            'captcha_key' => 'dummy',
            'captcha_value' => 'dummy',
        ])->assertOk();

        $cacheKey = 'password_reset_' . sha1('recover@example.com');
        $session = Cache::get($cacheKey);
        $this->assertIsArray($session);

        $this->postJson('/api/v1/forgot-password/reset', [
            'email' => 'recover@example.com',
            'code' => $session['code'],
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
        $this->assertNull(Cache::get($cacheKey));
    }

    public function test_forgot_password_reset_rejects_invalid_code(): void
    {
        config(['captcha.disable' => true]);

        User::factory()->create([
            'email' => 'bad-code@example.com',
        ]);

        $this->postJson('/api/v1/forgot-password/request', [
            'email' => 'bad-code@example.com',
            'captcha_key' => 'dummy',
            'captcha_value' => 'dummy',
        ])->assertOk();

        $this->postJson('/api/v1/forgot-password/reset', [
            'email' => 'bad-code@example.com',
            'code' => '000000',
            'password' => 'BrandNew123!',
            'password_confirmation' => 'BrandNew123!',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }
}
