<?php

namespace Tests\Feature;

use App\Models\AccountSetup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthEmailVerificationLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_verification_for_unverified_user(): void
    {
        config(['captcha.disable' => true]);

        $user = User::factory()->unverified()->create([
            'email' => 'unverified@example.com',
            'password' => 'SecretPass123!',
            'verify_via' => 'email',
        ]);

        $this->postJson('/api/v1/login', [
            'login' => $user->email,
            'password' => 'SecretPass123!',
            'captcha_key' => 'dummy',
            'captcha_value' => 'dummy',
            'verifyVia' => 'email',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('verification_required', true)
            ->assertJsonMissingPath('data.token');
    }

    public function test_verify_email_link_marks_user_verified_and_syncs_step_fourteen(): void
    {
        $user = User::factory()->unverified()->create();
        AccountSetup::query()->create([
            'user_id' => $user->id,
            'current_step' => 8,
            'email_verify_status' => 'pending',
            'step_14_done' => false,
        ]);

        $url = URL::temporarySignedRoute(
            'api.verify.email',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->get($url)->assertRedirect(url('/login?email_verified=success'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        $setup = AccountSetup::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('verified', $setup->email_verify_status);
        $this->assertTrue((bool) $setup->step_14_done);
    }

    public function test_verify_email_link_rejects_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'api.verify.email',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('not-the-user-email')]
        );

        $this->get($url)->assertRedirect(url('/login?email_verified=invalid'));

        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    public function test_verify_email_link_rejects_invalid_signature(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'api.verify.email',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        $invalidUrl = $url . '&tampered=1';

        $this->get($invalidUrl)->assertRedirect(url('/login?email_verified=invalid'));

        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    public function test_authenticated_user_can_resend_email_verification(): void
    {
        $user = User::factory()->unverified()->create([
            'verify_via' => 'sms',
            'email_otp' => null,
            'otp_expires_at' => null,
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/verify/email/resend')
            ->assertOk()
            ->assertJsonPath('success', true);

        $user->refresh();
        $this->assertSame('email', $user->verify_via);
        $this->assertNotNull($user->email_otp);
        $this->assertNotNull($user->otp_expires_at);
    }
}
