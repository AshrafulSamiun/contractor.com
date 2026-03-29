<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use App\Services\EmailSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class EmailSettingsController extends Controller
{
    public function show(Request $request, EmailSettingsService $service)
    {
        $settings = EmailSetting::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'use_global_settings' => true,
                'from_name' => null,
                'from_email' => null,
                'reply_to' => null,
                'default_cc' => [],
                'default_bcc' => [],
                'signature_html' => null,
                'signature_text' => null,
                'imap_enabled' => false,
                'imap_host' => null,
                'imap_port' => null,
                'imap_encryption' => null,
                'imap_username' => null,
                'imap_password' => null,
                'imap_folder' => 'INBOX',
                'imap_last_uid' => null,
                'imap_last_sync_at' => null,
                'imap_last_error' => null,
            ]
        );

        $defaults = $service->loadDefaults();
        $data = $service->extractSettings($settings);
        if (!empty($data['imap_password'])) {
            try {
                $data['imap_password'] = Crypt::decryptString($data['imap_password']);
            } catch (\Throwable $e) {
                $data['imap_password'] = null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                ...$data,
                'use_global_settings' => (bool) $settings->use_global_settings,
                'defaults' => $defaults,
            ],
        ]);
    }

    public function update(Request $request, EmailSettingsService $service)
    {
        $validated = $request->validate([
            'use_global_settings' => ['nullable', 'boolean'],
            'from_name' => ['nullable', 'string', 'max:120'],
            'from_email' => ['nullable', 'email'],
            'reply_to' => ['nullable', 'email'],
            'default_cc' => ['nullable', 'array'],
            'default_cc.*' => ['email'],
            'default_bcc' => ['nullable', 'array'],
            'default_bcc.*' => ['email'],
            'signature_html' => ['nullable', 'string'],
            'signature_text' => ['nullable', 'string'],
            'imap_enabled' => ['nullable', 'boolean'],
            'imap_host' => ['nullable', 'string', 'max:255'],
            'imap_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'imap_encryption' => ['nullable', 'in:ssl,tls,none'],
            'imap_username' => ['nullable', 'string', 'max:255'],
            'imap_password' => ['nullable', 'string', 'max:255'],
            'imap_folder' => ['nullable', 'string', 'max:120'],
        ]);

        $settings = EmailSetting::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'use_global_settings' => true,
                'from_name' => null,
                'from_email' => null,
                'reply_to' => null,
                'default_cc' => [],
                'default_bcc' => [],
                'signature_html' => null,
                'signature_text' => null,
                'imap_enabled' => false,
                'imap_host' => null,
                'imap_port' => null,
                'imap_encryption' => null,
                'imap_username' => null,
                'imap_password' => null,
                'imap_folder' => 'INBOX',
                'imap_last_uid' => null,
                'imap_last_sync_at' => null,
                'imap_last_error' => null,
            ]
        );

        if (array_key_exists('imap_password', $validated) && $validated['imap_password']) {
            $validated['imap_password'] = Crypt::encryptString($validated['imap_password']);
        } else {
            unset($validated['imap_password']);
        }

        $scope = $request->string('scope')->toString();
        if ($scope === 'global' && $request->user()->role === 'admin') {
            $defaults = array_merge($service->defaultPayload(), $validated);
            $service->saveDefaults($defaults);
        } else {
            $settings->update($validated);
        }

        return response()->json([
            'success' => true,
            'data' => [
                ...$service->extractSettings($settings),
                'use_global_settings' => (bool) $settings->use_global_settings,
            ],
        ]);
    }
}
