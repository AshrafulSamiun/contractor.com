<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountSetup;
use App\Models\Country;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\PermissionService;
use App\Services\AccountSecurityActivityTracker;
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
            'account_number' => ['nullable', 'string', 'max:120'],
            'position' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'plan' => ['nullable', Rule::in(['basic', 'standard', 'enterprise'])],
            'verifyVia' => ['nullable', Rule::in(['email', 'sms'])],
            'verification_code' => ['nullable', 'string', 'regex:/^\d{4,6}$/'],
            'captcha_key' => ['required', 'string'],
            'captcha_value' => ['required', 'string'],
        ], [
            'phoneNo.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
            'verification_code.regex' => 'Verification code must be 4 to 6 digits.',
        ]);

        $captchaValue = preg_replace('/\s+/', '', $validated['captcha_value'] ?? '');
        if (! config('captcha.disable')) {
            $cacheKey = 'captcha_'.md5($validated['captcha_key']);
            if (app()->environment('local')) {
                Log::info('captcha_debug_register', [
                    'cache_exists' => Cache::has($cacheKey),
                    'value_len' => strlen($captchaValue),
                ]);
            }
        }
        if (! config('captcha.disable') && ! Captcha::check_api($captchaValue, $validated['captcha_key'], 'flat')) {
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
        $country = ! empty($validated['country_id'])
            ? $countryQuery->find($validated['country_id'])
            : $countryQuery->where('country_name', $validated['country'])->first();

        if (! $country) {
            return response()->json([
                'message' => 'Selected country is invalid.',
                'errors' => ['country_id' => ['Selected country is invalid.']],
            ], 422);
        }

        $registerVia = $validated['verifyVia'] ?? 'email';
        $registerTarget = $registerVia === 'sms'
            ? ($validated['phoneNo'] ?? null)
            : ($validated['email'] ?? null);

        if (! $this->verifyRegisterPreviewCode(
            $registerVia,
            $registerTarget,
            $validated['verification_code'] ?? null,
        )) {
            $verificationLabel = $registerVia === 'sms'
                ? 'phone verification code'
                : 'email verification code';
            $verificationMessage = $registerVia === 'sms'
                ? 'Your phone verification code is invalid, expired, or was replaced. Please click Send Code again.'
                : 'Your email verification code is invalid, expired, or was replaced. Please click Send Code again.';

            return response()->json([
                'message' => $verificationMessage,
                'errors' => [
                    'verification_code' => ["Please enter a valid {$verificationLabel}."],
                ],
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
            'verify_via' => $registerVia,
            'selected_plan' => $validated['plan'] ?? 'pro',
            'role' => 'admin',
            'password' => $validated['password'],
            'email_verified_at' => $registerVia === 'email' ? now() : null,
            'phone_verified_at' => $registerVia === 'sms' ? now() : null,
        ]);

        $user->project_id = $user->id;

        if ($registerVia === 'email') {
            $user->email_otp = null;
        } else {
            $user->phone_otp = null;
        }
        $user->otp_expires_at = null;
        $user->save();

        $this->initializeAccountSetup($user, $country, $validated);
        $this->forgetRegisterPreviewCode($registerVia, $registerTarget);
        if ($registerVia === 'email') {
            $this->syncAccountSetupVerificationState($user);
        }

        $token = $this->issueToken($user, $request);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $this->serializeUser($user),
            ],
        ]);
    }

    public function requestRegisterVerificationCode(Request $request)
    {
        $request->merge([
            'phoneNo' => $this->normalizePhoneValue($request->input('phoneNo')),
        ]);

        $validated = $request->validate([
            'verifyVia' => ['required', Rule::in(['email', 'sms'])],
            'email' => ['nullable', 'email', 'max:255'],
            'phoneNo' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
        ], [
            'phoneNo.regex' => 'Phone number must be in E.164 format (e.g. +14165550100).',
        ]);

        $via = $validated['verifyVia'];
        if ($via === 'email' && empty($validated['email'])) {
            return response()->json([
                'message' => 'Email is required.',
                'errors' => ['email' => ['Email is required.']],
            ], 422);
        }

        if ($via === 'sms' && empty($validated['phoneNo'])) {
            return response()->json([
                'message' => 'Phone number is required.',
                'errors' => ['phoneNo' => ['Phone number is required.']],
            ], 422);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $deliveryVia = $via;
        if ($via === 'sms') {
            $smsSent = $this->sendRegistrationSmsOtp($validated['phoneNo'], $code);
            if (! $smsSent && ! empty($validated['email'])) {
                $emailSent = $this->sendRegistrationEmailOtp($validated['email'], $code);
                if ($emailSent) {
                    $deliveryVia = 'email';
                } else {
                    return response()->json([
                        'message' => 'Unable to send the verification code right now. Please try again.',
                    ], 500);
                }
            } elseif (! $smsSent) {
                return response()->json([
                    'message' => 'Unable to send the verification code right now. Please try again.',
                ], 500);
            }
        } else {
            if (! $this->sendRegistrationEmailOtp($validated['email'], $code)) {
                return response()->json([
                    'message' => 'Unable to send the verification email right now. Please try again.',
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $deliveryVia === 'sms'
                ? 'Verification code sent to your phone.'
                : ($via === 'sms'
                    ? 'SMS is unavailable right now. We sent the verification code to your email instead.'
                    : 'Verification code sent to your email.'),
            'data' => [
                'verify_via' => $deliveryVia,
            ],
        ]);
    }

    public function requestLoginVerificationCode(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'security_pin' => ['nullable', 'string', 'min:4', 'max:20'],
            'verifyVia' => ['required', Rule::in(['email', 'sms'])],
        ]);

        $user = User::where('email', $validated['login'])
            ->orWhere('username', $validated['login'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            \App\Models\ExternalAccessAttempt::create(['ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 1000), 'result' => 'Failed', 'threat_level' => 'medium']);
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $setupPinHash = AccountSetup::query()
            ->where('user_id', $user->id)
            ->value('security_pin_hash');
        if ($setupPinHash && (empty($validated['security_pin']) || ! Hash::check($validated['security_pin'], $setupPinHash))) {
            return response()->json([
                'message' => 'The security PIN is incorrect.',
                'errors' => ['security_pin' => ['Enter the security PIN created during account setup.']],
            ], 422);
        }

        $user->verify_via = $validated['verifyVia'];
        $user->save();

        try {
            $verifySession = $this->startVerificationSession($user);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => 'Unable to send the verification code right now. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $user->verify_via === 'sms'
                ? 'Verification code sent to your phone.'
                : 'Verification code sent to your email.',
            'data' => [
                'verify_session' => $verifySession,
                'verify_via' => $user->verify_via,
            ],
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'security_pin' => ['nullable', 'string', 'min:4', 'max:20'],
            'captcha_key' => ['required', 'string'],
            'captcha_value' => ['required', 'string'],
            'verifyVia' => ['nullable', Rule::in(['email', 'sms'])],
        ]);

        $captchaValue = preg_replace('/\s+/', '', $validated['captcha_value'] ?? '');
        if (! config('captcha.disable')) {
            $cacheKey = 'captcha_'.md5($validated['captcha_key']);
            if (app()->environment('local')) {
                Log::info('captcha_debug_login', [
                    'cache_exists' => Cache::has($cacheKey),
                    'value_len' => strlen($captchaValue),
                ]);
            }
        }
        if (! config('captcha.disable') && ! Captcha::check_api($captchaValue, $validated['captcha_key'], 'flat')) {
            return response()->json([
                'message' => 'Captcha verification failed.',
                'errors' => ['captcha_value' => ['Captcha verification failed.']],
            ], 422);
        }

        $user = User::where('email', $validated['login'])
            ->orWhere('username', $validated['login'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $setupPinHash = AccountSetup::query()
            ->where('user_id', $user->id)
            ->value('security_pin_hash');
        if ($setupPinHash) {
            if (empty($validated['security_pin']) || ! Hash::check($validated['security_pin'], $setupPinHash)) {
                return response()->json([
                    'message' => 'The security PIN is incorrect.',
                    'errors' => ['security_pin' => ['Enter the security PIN created during account setup.']],
                ], 422);
            }
        }

        if (! empty($validated['verifyVia'])) {
            $user->verify_via = $validated['verifyVia'];
            $user->save();
        }

        if ($this->needsVerification($user)) {
            try {
                $verifySession = $this->startVerificationSession($user);
            } catch (\RuntimeException $e) {
                return response()->json([
                    'message' => 'Unable to send the verification code right now. Please try again.',
                ], 500);
            }

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

        $token = $this->issueToken($user, $request);

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
            'code' => ['required', 'string', 'regex:/^\d{4,6}$/'],
            'captcha_key' => ['nullable', 'string'],
            'captcha_value' => ['nullable', 'string'],
        ]);

        // The dedicated verification screen can be reached from an already
        // authenticated login flow. When the code is submitted on the login
        // form itself, require its captcha as an additional anti-bot check.
        if ($request->filled('captcha_key') || $request->filled('captcha_value')) {
            if (! $request->filled('captcha_key') || ! $request->filled('captcha_value')
                || (! config('captcha.disable') && ! Captcha::check_api(
                    preg_replace('/\s+/', '', $validated['captcha_value']),
                    $validated['captcha_key'],
                    'flat',
                ))) {
                return response()->json([
                    'message' => 'Captcha verification failed.',
                    'errors' => ['captcha_value' => ['Captcha verification failed.']],
                ], 422);
            }
        }

        $sessionKey = $this->verificationCacheKey($validated['verify_session']);
        $session = Cache::get($sessionKey);
        if (! $session || empty($session['user_id'])) {
            return response()->json([
                'message' => 'Verification session expired.',
            ], 422);
        }

        $user = User::find($session['user_id']);
        if (! $user) {
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
        if (! $expected || $expected !== $code) {
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

        $token = $this->issueToken($user, $request);

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
        if (! $session || empty($session['user_id'])) {
            return response()->json([
                'message' => 'Verification session expired.',
            ], 422);
        }

        $user = User::find($session['user_id']);
        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $deliveryVia = $this->sendOtp($user, $session['via'] ?? $user->verify_via ?? 'email');
        if (! $deliveryVia) {
            return response()->json([
                'message' => 'Unable to resend the verification code right now. Please try again.',
            ], 500);
        }

        Cache::put($sessionKey, [
            'user_id' => $user->id,
            'via' => $deliveryVia,
        ], now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'message' => $deliveryVia === 'sms'
                ? 'OTP resent to your phone.'
                : 'SMS is unavailable right now. We sent the verification code to your email instead.',
            'data' => [
                'verify_via' => $deliveryVia,
            ],
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
        if (! config('captcha.disable') && ! Captcha::check_api($captchaValue, $validated['captcha_key'], 'flat')) {
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
        if (! $user) {
            return response()->json([
                'message' => 'Invalid reset code or email.',
                'errors' => ['code' => ['Invalid reset code or email.']],
            ], 422);
        }

        $session = Cache::get($this->passwordResetCacheKey($normalizedEmail));
        $submittedCode = preg_replace('/\s+/', '', $validated['code']);
        if (
            ! is_array($session) ||
            (int) ($session['user_id'] ?? 0) !== (int) $user->id ||
            ! is_string($session['code'] ?? null) ||
            ! hash_equals($session['code'], $submittedCode)
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
        if (! config('captcha.disable') && ! Captcha::check_api($captchaValue, $validated['captcha_key'], 'flat')) {
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
        if (! $user) {
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

        if (! $this->sendOtp($user, 'email')) {
            return response()->json([
                'message' => 'Unable to resend the verification email right now. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verification email resent.',
        ]);
    }

    public function verifyEmailLink(Request $request, int $id, string $hash): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect($this->frontendLoginUrl([
                'email_verified' => 'invalid',
            ]));
        }

        $user = User::find($id);
        if (! $user || ! hash_equals($hash, sha1($user->email ?? ''))) {
            return redirect($this->frontendLoginUrl([
                'email_verified' => 'invalid',
            ]));
        }

        if (! $user->email_verified_at) {
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
            return ! $user->phone_verified_at;
        }

        return ! $user->email_verified_at;
    }

    protected function startVerificationSession(User $user): string
    {
        $verifySession = Str::random(40);

        $deliveryVia = $this->sendOtp($user, $user->verify_via ?? 'email');
        if (! $deliveryVia) {
            throw new \RuntimeException('Unable to send the verification code.');
        }

        if (($user->verify_via ?? 'email') !== $deliveryVia) {
            $user->verify_via = $deliveryVia;
            $user->save();
        }

        Cache::put($this->verificationCacheKey($verifySession), [
            'user_id' => $user->id,
            'via' => $deliveryVia,
        ], now()->addMinutes(10));

        return $verifySession;
    }

    protected function issueToken(User $user, Request $request): string
    {
        $token = $user->createToken('auth');
        app(AccountSecurityActivityTracker::class)->recordLogin($user, $request, $token->accessToken->id);
        return $token->plainTextToken;
    }

    protected function verificationCacheKey(string $session): string
    {
        return 'verify_session_'.$session;
    }

    protected function normalizeEmailValue(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }

    protected function passwordResetCacheKey(string $normalizedEmail): string
    {
        return 'password_reset_'.sha1($normalizedEmail);
    }

    protected function registerPreviewVerificationCacheKey(string $via, string $target): string
    {
        return 'register_preview_verification_'.sha1($via.'|'.$target);
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
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $normalized = preg_replace('/[^\d+]/', '', $trimmed);
        if (! is_string($normalized) || $normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, '00')) {
            $normalized = '+'.substr($normalized, 2);
        }

        if (str_starts_with($normalized, '+')) {
            $normalized = '+'.preg_replace('/\D/', '', substr($normalized, 1));
        } else {
            $normalized = '+'.preg_replace('/\D/', '', $normalized);
        }

        return $normalized === '+' ? null : $normalized;
    }

    protected function sendOtp(User $user, string $via): string|false
    {
        if ($via === 'sms' && empty($user->phone)) {
            $via = 'email';
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);

        if ($via === 'sms') {
            $user->phone_otp = $code;
            $user->email_otp = null;
            $user->otp_expires_at = $expiresAt;
            $user->save();

            if ($this->sendSmsOtp($user, $code)) {
                return 'sms';
            }

            if (! empty($user->email)) {
                $user->phone_otp = null;
                $user->email_otp = $code;
                $user->otp_expires_at = $expiresAt;
                $user->save();

                if ($this->sendEmailOtp($user, $code)) {
                    Log::info('sms_otp_fallback_email', ['user_id' => $user->id, 'email' => $user->email]);
                    return 'email';
                }
            }

            return false;
        }

        $user->email_otp = $code;
        $user->phone_otp = null;
        $user->otp_expires_at = $expiresAt;
        $user->save();

        return $this->sendEmailOtp($user, $code) ? 'email' : false;
    }

    protected function sendEmailOtp(User $user, string $code): bool
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
                $message->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($user->email)->subject('Contractor.com Verification Code');
            });
            Log::info('email_otp_sent', ['user_id' => $user->id, 'email' => $user->email]);
            NotificationLog::create([
                'user_id' => $user->id,
                'channel' => 'email',
                'status' => 'sent',
                'to' => $user->email,
                'context' => 'otp',
            ]);
            return true;
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
            return false;
        }
    }

    protected function sendSmsOtp(User $user, string $code): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');
        $to = $user->phone;

        if (! $sid || ! $token || ! $from || ! $to) {
            Log::warning('sms_otp_missing_config', ['user_id' => $user->id]);

            return false;
        }

        $normalizedTo = trim((string) $to);
        if ($normalizedTo !== '' && $normalizedTo[0] !== '+') {
            if (str_starts_with($normalizedTo, '01') && strlen($normalizedTo) === 11) {
                $normalizedTo = '+88'.$normalizedTo;
            } else {
                $normalizedTo = '+'.ltrim($normalizedTo, '+');
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
                    'Body' => "Your Contractor.com verification code is: {$code}",
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
                return true;
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
                return false;
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
            return false;
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
                $message->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($user->email)->subject('Contractor.com Password Reset Code');
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

    protected function sendRegistrationEmailOtp(string $email, string $code): bool
    {
        try {
            Mail::send('emails.otp', [
                'name' => 'there',
                'code' => $code,
                'expires_minutes' => 10,
            ], function ($message) use ($email) {
                $message->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($email)->subject('Contractor.com Verification Code');
            });

            Cache::put(
                $this->registerPreviewVerificationCacheKey('email', $this->normalizeEmailValue($email)),
                $code,
                now()->addMinutes(10)
            );

            NotificationLog::create([
                'user_id' => null,
                'channel' => 'email',
                'status' => 'sent',
                'to' => $email,
                'context' => 'registration_otp_preview',
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::warning('registration_email_otp_failed', ['error' => $e->getMessage(), 'email' => $email]);
            NotificationLog::create([
                'user_id' => null,
                'channel' => 'email',
                'status' => 'failed',
                'to' => $email,
                'context' => 'registration_otp_preview',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected function sendRegistrationSmsOtp(string $phone, string $code): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (! $sid || ! $token || ! $from || ! $phone) {
            Log::warning('registration_sms_otp_missing_config', ['phone' => $phone]);

            return false;
        }

        try {
            $response = Http::withBasicAuth($sid, $token)->asForm()->post(
                "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json",
                [
                    'From' => $from,
                    'To' => $phone,
                    'Body' => "Your Contractor.com verification code is: {$code}",
                ]
            );

            if ($response->successful()) {
                Cache::put(
                    $this->registerPreviewVerificationCacheKey('sms', $phone),
                    $code,
                    now()->addMinutes(10)
                );

                NotificationLog::create([
                    'user_id' => null,
                    'channel' => 'sms',
                    'status' => 'sent',
                    'to' => $phone,
                    'context' => 'registration_otp_preview',
                ]);
                return true;
            } else {
                Log::warning('registration_sms_otp_failed', ['status' => $response->status(), 'body' => $response->body()]);
                NotificationLog::create([
                    'user_id' => null,
                    'channel' => 'sms',
                    'status' => 'failed',
                    'to' => $phone,
                    'context' => 'registration_otp_preview',
                    'error' => $response->body(),
                ]);
                return false;
            }
        } catch (\Throwable $e) {
            Log::warning('registration_sms_otp_failed', ['error' => $e->getMessage(), 'phone' => $phone]);
            NotificationLog::create([
                'user_id' => null,
                'channel' => 'sms',
                'status' => 'failed',
                'to' => $phone,
                'context' => 'registration_otp_preview',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected function verifyRegisterPreviewCode(string $via, ?string $target, ?string $submittedCode): bool
    {
        $submittedCode = preg_replace('/\s+/', '', (string) $submittedCode);
        if ($submittedCode === '' || $target === null || $target === '') {
            return false;
        }

        if ($via === 'email') {
            $target = $this->normalizeEmailValue($target);
        }

        $expectedCode = Cache::get($this->registerPreviewVerificationCacheKey($via, $target));

        return is_string($expectedCode) && hash_equals($expectedCode, $submittedCode);
    }

    protected function forgetRegisterPreviewCode(string $via, ?string $target): void
    {
        if ($target === null || $target === '') {
            return;
        }

        if ($via === 'email') {
            $target = $this->normalizeEmailValue($target);
        }

        Cache::forget($this->registerPreviewVerificationCacheKey($via, $target));
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
                $message->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($user->email)->subject('Contractor.com Username Reminder');
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
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if ($user && $token) {
            app(AccountSecurityActivityTracker::class)->endToken($user, $token->id);
            $token->delete();
        }

        return response()->json([
            'success' => true,
        ]);
    }

    protected function serializeUser(User $user): array
    {
        $payload = $user->toArray();
        $payload['is_super_admin'] = strtolower(trim((string) $user->role)) === 'super_admin';
        $activation = AccountSetup::query()
            ->where('user_id', $user->id)
            ->first(['activation_status', 'activation_completed_at']);
        $activationStatus = $activation?->activation_status;

        // A fully completed setup or an administrator-activated account can
        // enter the application. All other accounts must resume setup.
        $payload['activation_status'] = $activationStatus;
        $payload['account_access_ready'] = $payload['is_super_admin']
            || (bool) $user->account_setup_completed_at
            || $activationStatus === 'active'
            || (bool) $activation?->activation_completed_at;
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
        $url = url('/login');
        if (empty($query)) {
            return $url;
        }

        return $url.'?'.http_build_query($query);
    }

    protected function syncAccountSetupVerificationState(User $user): void
    {
        if (! $user->email_verified_at) {
            return;
        }

        $setup = AccountSetup::query()->where('user_id', $user->id)->first();
        if (! $setup) {
            return;
        }

        $dirty = false;
        if ($setup->email_verify_status !== 'verified') {
            $setup->email_verify_status = 'verified';
            $dirty = true;
        }

        if ($dirty) {
            $setup->save();
        }
    }

    protected function initializeAccountSetup(User $user, Country $country, array $validated): void
    {
        $setup = AccountSetup::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['current_step' => 1]
        );

        $columns = array_filter([
            'company_name' => $validated['company'] ?? null,
            'company_address' => $validated['address'] ?? null,
            'company_city' => $validated['city'] ?? null,
            'company_state' => $validated['state'] ?? null,
            'company_zip' => $validated['zip'] ?? null,
            'company_country_id' => $country->id ?? null,
            'company_country' => $country->country_name ?? null,
            'company_phone' => $validated['phoneNo'] ?? null,
            'contact_mobile_phone' => $validated['phoneNo'] ?? null,
            'contact_business_email' => $validated['email'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        if (! empty($columns)) {
            $setup->fill($columns);
        }

        $data = $setup->data;
        if (! is_array($data)) {
            $data = [];
        }

        $setupForm = $data['setup_form'] ?? [];
        if (! is_array($setupForm)) {
            $setupForm = [];
        }

        $setupColumns = $data['setup_columns'] ?? [];
        if (! is_array($setupColumns)) {
            $setupColumns = [];
        }

        $stepSnapshots = $data['step_snapshots'] ?? [];
        if (! is_array($stepSnapshots)) {
            $stepSnapshots = [];
        }

        $formValues = array_filter([
            'full_name' => $validated['name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'position' => $validated['position'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        $columnSnapshot = array_merge($columns, [
            'current_step' => max(1, (int) ($setup->current_step ?: 1)),
        ]);

        $setupForm = array_replace($setupForm, $formValues);
        $setupColumns = array_replace($setupColumns, $columnSnapshot);
        $stepSnapshots['register'] = [
            'saved_at' => now()->toIso8601String(),
            'payload' => array_merge($formValues, $columns),
            'columns' => $columnSnapshot,
        ];

        $data['setup_form'] = $setupForm;
        $data['setup_columns'] = $setupColumns;
        $data['step_snapshots'] = $stepSnapshots;
        $data['last_saved_step'] = 'register';

        $setup->data = $data;
        $setup->save();

        if ((int) $user->project_id !== (int) $setup->id) {
            $user->forceFill(['project_id' => $setup->id])->save();
        }
    }
}
