<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Mews\Captcha\Facades\Captcha;
use App\Models\NotificationLog;
use App\Services\PermissionService;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->merge([
            'phoneNo' => $this->normalizePhoneValue($request->input('phoneNo')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phoneNo' => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')],
            'country' => ['nullable', 'string', 'max:100'],
            'zip' => ['required', 'string', 'max:30'],
            'plan' => ['nullable', Rule::in(['basic', 'standard', 'enterprise'])],
            'verifyVia' => ['nullable', Rule::in(['email', 'sms'])],
            'captcha_key' => ['required', 'string'],
            'captcha_value' => ['required', 'string'],
        ], [
            'phoneNo.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
        ]);

        $captchaValue = preg_replace('/\s+/', '', $validated['captcha_value'] ?? '');
        if (!config('captcha.disable')) {
            $cacheKey = 'captcha_' . md5($validated['captcha_key']);
            if (app()->environment('local')) {
                Log::info('captcha_debug_register', [
                    'cache_exists' => Cache::has($cacheKey),
                    'value_len' => strlen($captchaValue),
                ]);
            }
        }
        if (!config('captcha.disable') && !Captcha::check_api($captchaValue, $validated['captcha_key'], 'flat')) {
            return response()->json([
                'message' => 'Captcha verification failed.',
                'errors' => ['captcha_value' => ['Captcha verification failed.']],
            ], 422);
        }

        if (empty($validated['country_id']) && empty($validated['country'])) {
            return response()->json([
                'message' => 'Country is required.',
                'errors' => ['country_id' => ['Country is required.']],
            ], 422);
        }

        $countryQuery = Country::query()->select(['id', 'country_name']);
        $country = !empty($validated['country_id'])
            ? $countryQuery->find($validated['country_id'])
            : $countryQuery->where('country_name', $validated['country'])->first();

        if (!$country) {
            return response()->json([
                'message' => 'Selected country is invalid.',
                'errors' => ['country_id' => ['Selected country is invalid.']],
            ], 422);
        }

        $user = User::create([
            'name' => $validated['name'],
            'company_name' => $validated['company'],
            'username' => $validated['username'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phoneNo'],
            'country_id' => $country->id,
            'country' => $country->country_name,
            'postal_code' => $validated['zip'],
            'verify_via' => $validated['verifyVia'] ?? 'email',
            'selected_plan' => $validated['plan'] ?? 'standard',
            'role' => 'admin',
            'password' => $validated['password'],
        ]);

        if ($this->needsVerification($user)) {
            $verifySession = $this->startVerificationSession($user);
            return response()->json([
                'success' => true,
                'verification_required' => true,
                'data' => [
                    'verify_session' => $verifySession,
                    'verify_via' => $user->verify_via,
                    'user_id' => $user->id,
                ],
            ]);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $this->serializeUser($user),
            ],
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'captcha_key' => ['required', 'string'],
            'captcha_value' => ['required', 'string'],
            'verifyVia' => ['nullable', Rule::in(['email', 'sms'])],
        ]);

        $captchaValue = preg_replace('/\s+/', '', $validated['captcha_value'] ?? '');
        if (!config('captcha.disable')) {
            $cacheKey = 'captcha_' . md5($validated['captcha_key']);
            if (app()->environment('local')) {
                Log::info('captcha_debug_login', [
                    'cache_exists' => Cache::has($cacheKey),
                    'value_len' => strlen($captchaValue),
                ]);
            }
        }
        if (!config('captcha.disable') && !Captcha::check_api($captchaValue, $validated['captcha_key'], 'flat')) {
            return response()->json([
                'message' => 'Captcha verification failed.',
                'errors' => ['captcha_value' => ['Captcha verification failed.']],
            ], 422);
        }

        $user = User::where('email', $validated['login'])
            ->orWhere('username', $validated['login'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (!empty($validated['verifyVia'])) {
            $user->verify_via = $validated['verifyVia'];
            $user->save();
        }

        if ($this->needsVerification($user)) {
            $verifySession = $this->startVerificationSession($user);
            return response()->json([
                'success' => true,
                'verification_required' => true,
                'data' => [
                    'verify_session' => $verifySession,
                    'verify_via' => $user->verify_via,
                    'user_id' => $user->id,
                ],
            ]);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $this->serializeUser($user),
            ],
        ]);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'verify_session' => ['required', 'string'],
            'code' => ['required', 'string', 'min:4', 'max:6'],
        ]);

        $sessionKey = $this->verificationCacheKey($validated['verify_session']);
        $session = Cache::get($sessionKey);
        if (!$session || empty($session['user_id'])) {
            return response()->json([
                'message' => 'Verification session expired.',
            ], 422);
        }

        $user = User::find($session['user_id']);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->otp_expires_at && now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'message' => 'OTP expired. Please resend.',
                'errors' => ['code' => ['OTP expired.']],
            ], 422);
        }

        $code = preg_replace('/\s+/', '', $validated['code']);
        $via = $session['via'] ?? $user->verify_via ?? 'email';
        $expected = $via === 'sms' ? $user->phone_otp : $user->email_otp;
        if (!$expected || $expected !== $code) {
            return response()->json([
                'message' => 'Invalid verification code.',
                'errors' => ['code' => ['Invalid verification code.']],
            ], 422);
        }

        if ($via === 'sms') {
            $user->phone_verified_at = now();
            $user->phone_otp = null;
        } else {
            $user->email_verified_at = now();
            $user->email_otp = null;
            $this->syncAccountSetupVerificationState($user);
        }
        $user->otp_expires_at = null;
        $user->save();

        Cache::forget($sessionKey);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $this->serializeUser($user),
            ],
        ]);
    }

    public function resendVerification(Request $request)
    {
        $validated = $request->validate([
            'verify_session' => ['required', 'string'],
        ]);

        $sessionKey = $this->verificationCacheKey($validated['verify_session']);
        $session = Cache::get($sessionKey);
        if (!$session || empty($session['user_id'])) {
            return response()->json([
                'message' => 'Verification session expired.',
            ], 422);
        }

        $user = User::find($session['user_id']);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $this->sendOtp($user, $session['via'] ?? $user->verify_via ?? 'email');

        return response()->json([
            'success' => true,
            'message' => 'OTP resent.',
        ]);
    }

    public function forgotPasswordRequest(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'captcha_key' => ['required', 'string'],
            'captcha_value' => ['required', 'string'],
        ]);

        $captchaValue = preg_replace('/\s+/', '', $validated['captcha_value'] ?? '');
        if (!config('captcha.disable') && !Captcha::check_api($captchaValue, $validated['captcha_key'], 'flat')) {
            return response()->json([
                'message' => 'Captcha verification failed.',
                'errors' => ['captcha_value' => ['Captcha verification failed.']],
            ], 422);
        }

        $normalizedEmail = $this->normalizeEmailValue($validated['email']);
        $user = User::query()->whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();
        if ($user) {
            $this->startPasswordResetSession($user);
        }

        return response()->json([
            'success' => true,
            'message' => 'If an account exists for this email, a reset code has been sent.',
        ]);
    }

    public function forgotPasswordReset(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $normalizedEmail = $this->normalizeEmailValue($validated['email']);
        $user = User::query()->whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();
        if (!$user) {
            return response()->json([
                'message' => 'Invalid reset code or email.',
                'errors' => ['code' => ['Invalid reset code or email.']],
            ], 422);
        }

        $session = Cache::get($this->passwordResetCacheKey($normalizedEmail));
        $submittedCode = preg_replace('/\s+/', '', $validated['code']);
        if (
            !is_array($session) ||
            (int) ($session['user_id'] ?? 0) !== (int) $user->id ||
            !is_string($session['code'] ?? null) ||
            !hash_equals($session['code'], $submittedCode)
        ) {
            return response()->json([
                'message' => 'Invalid reset code or email.',
                'errors' => ['code' => ['Invalid reset code or email.']],
            ], 422);
        }

        $user->password = $validated['password'];
        $user->save();
        $user->tokens()->delete();

        Cache::forget($this->passwordResetCacheKey($normalizedEmail));

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful. Please sign in.',
        ]);
    }

    public function forgotUsernameRequest(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'captcha_key' => ['required', 'string'],
            'captcha_value' => ['required', 'string'],
        ]);

        $captchaValue = preg_replace('/\s+/', '', $validated['captcha_value'] ?? '');
        if (!config('captcha.disable') && !Captcha::check_api($captchaValue, $validated['captcha_key'], 'flat')) {
            return response()->json([
                'message' => 'Captcha verification failed.',
                'errors' => ['captcha_value' => ['Captcha verification failed.']],
            ], 422);
        }

        $normalizedEmail = $this->normalizeEmailValue($validated['email']);
        $user = User::query()->whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();
        if ($user) {
            $this->sendUsernameReminderEmail($user);
        }

        return response()->json([
            'success' => true,
            'message' => 'If an account exists for this email, username details have been sent.',
        ]);
    }

    public function resendEmailVerification(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        if ($user->email_verified_at) {
            $this->syncAccountSetupVerificationState($user);

            return response()->json([
                'success' => true,
                'message' => 'Email is already verified.',
            ]);
        }

        if ($user->verify_via !== 'email') {
            $user->verify_via = 'email';
            $user->save();
        }

        $this->sendOtp($user, 'email');

        return response()->json([
            'success' => true,
            'message' => 'Verification email resent.',
        ]);
    }

    public function verifyEmailLink(Request $request, int $id, string $hash): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return redirect($this->frontendLoginUrl([
                'email_verified' => 'invalid',
            ]));
        }

        $user = User::find($id);
        if (!$user || !hash_equals($hash, sha1($user->email ?? ''))) {
            return redirect($this->frontendLoginUrl([
                'email_verified' => 'invalid',
            ]));
        }

        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->email_otp = null;
            $user->otp_expires_at = null;
            $user->save();
        }
        $this->syncAccountSetupVerificationState($user);

        return redirect($this->frontendLoginUrl([
            'email_verified' => 'success',
        ]));
    }

    protected function needsVerification(User $user): bool
    {
        if (filter_var(env('OTP_ALWAYS', false), FILTER_VALIDATE_BOOL)) {
            return true;
        }

        if (($user->verify_via ?? 'email') === 'sms') {
            return !$user->phone_verified_at;
        }
        return !$user->email_verified_at;
    }

    protected function startVerificationSession(User $user): string
    {
        $verifySession = Str::random(40);
        Cache::put($this->verificationCacheKey($verifySession), [
            'user_id' => $user->id,
            'via' => $user->verify_via ?? 'email',
        ], now()->addMinutes(10));

        $this->sendOtp($user, $user->verify_via ?? 'email');

        return $verifySession;
    }

    protected function verificationCacheKey(string $session): string
    {
        return 'verify_session_' . $session;
    }

    protected function normalizeEmailValue(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }

    protected function passwordResetCacheKey(string $normalizedEmail): string
    {
        return 'password_reset_' . sha1($normalizedEmail);
    }

    protected function startPasswordResetSession(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $normalizedEmail = $this->normalizeEmailValue($user->email);

        Cache::put($this->passwordResetCacheKey($normalizedEmail), [
            'user_id' => $user->id,
            'code' => $code,
        ], now()->addMinutes(10));

        $this->sendPasswordResetCodeEmail($user, $code);
    }

    protected function normalizePhoneValue(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $normalized = preg_replace('/[^\d+]/', '', $trimmed);
        if (!is_string($normalized) || $normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, '00')) {
            $normalized = '+' . substr($normalized, 2);
        }

        if (str_starts_with($normalized, '+')) {
            $normalized = '+' . preg_replace('/\D/', '', substr($normalized, 1));
        } else {
            $normalized = '+' . preg_replace('/\D/', '', $normalized);
        }

        return $normalized === '+' ? null : $normalized;
    }

    protected function sendOtp(User $user, string $via): void
    {
        if ($via === 'sms' && empty($user->phone)) {
            $via = 'email';
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);

        if ($via === 'sms') {
            $user->phone_otp = $code;
        } else {
            $user->email_otp = $code;
        }
        $user->otp_expires_at = $expiresAt;
        $user->save();

        if ($via === 'sms') {
            $this->sendSmsOtp($user, $code);
        } else {
            $this->sendEmailOtp($user, $code);
        }
    }

    protected function sendEmailOtp(User $user, string $code): void
    {
        try {
            $verificationLinkExpiresMinutes = (int) config('auth.verification.expire', 60);
            if ($verificationLinkExpiresMinutes <= 0) {
                $verificationLinkExpiresMinutes = 60;
            }

            Mail::send('emails.otp', [
                'name' => $user->name,
                'code' => $code,
                'expires_minutes' => 10,
                'verification_url' => $this->buildEmailVerificationUrl($user, $verificationLinkExpiresMinutes),
                'verification_link_expires_minutes' => $verificationLinkExpiresMinutes,
            ], function ($message) use ($user) {
                $message->to($user->email)->subject('DeskDrop Verification Code');
            });
            Log::info('email_otp_sent', ['user_id' => $user->id, 'email' => $user->email]);
            NotificationLog::create([
                'user_id' => $user->id,
                'channel' => 'email',
                'status' => 'sent',
                'to' => $user->email,
                'context' => 'otp',
            ]);
        } catch (\Throwable $e) {
            Log::warning('email_otp_failed', ['error' => $e->getMessage()]);
            NotificationLog::create([
                'user_id' => $user->id,
                'channel' => 'email',
                'status' => 'failed',
                'to' => $user->email,
                'context' => 'otp',
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendSmsOtp(User $user, string $code): void
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');
        $to = $user->phone;

        if (!$sid || !$token || !$from || !$to) {
            Log::warning('sms_otp_missing_config', ['user_id' => $user->id]);
            return;
        }

        $normalizedTo = trim((string) $to);
        if ($normalizedTo !== '' && $normalizedTo[0] !== '+') {
            if (str_starts_with($normalizedTo, '01') && strlen($normalizedTo) === 11) {
                $normalizedTo = '+88' . $normalizedTo;
            } else {
                $normalizedTo = '+' . ltrim($normalizedTo, '+');
            }
        }
        if ($normalizedTo !== $to) {
            Log::info('sms_otp_normalized', ['original' => $to, 'normalized' => $normalizedTo]);
        }

        try {
            $response = Http::withBasicAuth($sid, $token)->asForm()->post(
                "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json",
                [
                    'From' => $from,
                    'To' => $normalizedTo,
                    'Body' => "Your DeskDrop verification code is: {$code}",
                ]
            );
            if ($response->successful()) {
                Log::info('sms_otp_sent', ['user_id' => $user->id, 'to' => $normalizedTo]);
                NotificationLog::create([
                    'user_id' => $user->id,
                    'channel' => 'sms',
                    'status' => 'sent',
                    'to' => $normalizedTo,
                    'context' => 'otp',
                ]);
            } else {
                Log::warning('sms_otp_failed', ['status' => $response->status(), 'body' => $response->body()]);
                NotificationLog::create([
                    'user_id' => $user->id,
                    'channel' => 'sms',
                    'status' => 'failed',
                    'to' => $normalizedTo,
                    'context' => 'otp',
                    'error' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('sms_otp_failed', ['error' => $e->getMessage()]);
            NotificationLog::create([
                'user_id' => $user->id,
                'channel' => 'sms',
                'status' => 'failed',
                'to' => $normalizedTo,
                'context' => 'otp',
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendPasswordResetCodeEmail(User $user, string $code): void
    {
        try {
            Mail::send('emails.password-reset-code', [
                'name' => $user->name,
                'code' => $code,
                'expires_minutes' => 10,
            ], function ($message) use ($user) {
                $message->to($user->email)->subject('DeskDrop Password Reset Code');
            });

            NotificationLog::create([
                'user_id' => $user->id,
                'channel' => 'email',
                'status' => 'sent',
                'to' => $user->email,
                'context' => 'password_reset',
            ]);
        } catch (\Throwable $e) {
            Log::warning('password_reset_email_failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            NotificationLog::create([
                'user_id' => $user->id,
                'channel' => 'email',
                'status' => 'failed',
                'to' => $user->email,
                'context' => 'password_reset',
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendUsernameReminderEmail(User $user): void
    {
        try {
            Mail::send('emails.username-reminder', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'login_url' => $this->frontendLoginUrl(),
            ], function ($message) use ($user) {
                $message->to($user->email)->subject('DeskDrop Username Reminder');
            });

            NotificationLog::create([
                'user_id' => $user->id,
                'channel' => 'email',
                'status' => 'sent',
                'to' => $user->email,
                'context' => 'username_reminder',
            ]);
        } catch (\Throwable $e) {
            Log::warning('username_reminder_email_failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            NotificationLog::create([
                'user_id' => $user->id,
                'channel' => 'email',
                'status' => 'failed',
                'to' => $user->email,
                'context' => 'username_reminder',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->serializeUser($request->user()),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    protected function serializeUser(User $user): array
    {
        $payload = $user->toArray();
        $payload['permissions'] = app(PermissionService::class)->matrixFor($user);

        return $payload;
    }

    protected function buildEmailVerificationUrl(User $user, int $expiresMinutes = 60): string
    {
        return URL::temporarySignedRoute(
            'api.verify.email',
            now()->addMinutes($expiresMinutes),
            [
                'id' => $user->id,
                'hash' => sha1($user->email ?? ''),
            ]
        );
    }

    protected function frontendLoginUrl(array $query = []): string
    {
        $url = url('/app/login');
        if (empty($query)) {
            return $url;
        }

        return $url . '?' . http_build_query($query);
    }

    protected function syncAccountSetupVerificationState(User $user): void
    {
        if (!$user->email_verified_at) {
            return;
        }

        $setup = AccountSetup::query()->where('user_id', $user->id)->first();
        if (!$setup) {
            return;
        }

        $dirty = false;
        if ($setup->email_verify_status !== 'verified') {
            $setup->email_verify_status = 'verified';
            $dirty = true;
        }
        if (!(bool) $setup->step_14_done) {
            $setup->step_14_done = true;
            $dirty = true;
        }

        if ($dirty) {
            $setup->save();
        }
    }
}
