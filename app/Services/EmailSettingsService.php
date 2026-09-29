<?php

namespace App\Services;

use App\Models\EmailSetting;
use App\Models\SystemSetting;

class EmailSettingsService
{
    public function loadDefaults(): array
    {
        $row = SystemSetting::firstOrCreate(['key' => 'email_defaults'], [
            'value' => null,
        ]);

        if ($row->value) {
            $decoded = json_decode($row->value, true);
            if (is_array($decoded)) {
                return array_merge($this->defaultPayload(), $decoded);
            }
        }

        $defaults = $this->defaultPayload();
        $row->value = json_encode($defaults);
        $row->save();

        return $defaults;
    }

    public function saveDefaults(array $data): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'email_defaults'],
            ['value' => json_encode($data)]
        );
    }

    public function resolveForUser(int $userId): array
    {
        $defaults = $this->loadDefaults();

        $settings = EmailSetting::firstOrCreate(
            ['user_id' => $userId],
            [
                'use_global_settings' => true,
                'from_name' => null,
                'from_email' => null,
                'reply_to' => null,
                'default_cc' => [],
                'default_bcc' => [],
                'signature_html' => null,
                'signature_text' => null,
            ]
        );

        $useGlobal = (bool) $settings->use_global_settings;
        $userData = $this->extractSettings($settings);

        $merged = array_merge($defaults, $userData);
        if ($useGlobal) {
            $merged = array_merge($merged, $defaults);
        }
        $merged['use_global_settings'] = $useGlobal;

        return $merged;
    }

    public function defaultPayload(): array
    {
        return [
            'from_name' => config('mail.from.name'),
            'from_email' => config('mail.from.address'),
            'reply_to' => null,
            'default_cc' => [],
            'default_bcc' => [],
            'signature_html' => '<p>Thanks,<br>Contractor Team</p>',
            'signature_text' => "Thanks,\nContractor Team",
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
        ];
    }

    public function extractSettings(EmailSetting $settings): array
    {
        return [
            'from_name' => $settings->from_name,
            'from_email' => $settings->from_email,
            'reply_to' => $settings->reply_to,
            'default_cc' => $settings->default_cc ?: [],
            'default_bcc' => $settings->default_bcc ?: [],
            'signature_html' => $settings->signature_html,
            'signature_text' => $settings->signature_text,
            'imap_enabled' => (bool) $settings->imap_enabled,
            'imap_host' => $settings->imap_host,
            'imap_port' => $settings->imap_port,
            'imap_encryption' => $settings->imap_encryption,
            'imap_username' => $settings->imap_username,
            'imap_password' => $settings->imap_password,
            'imap_folder' => $settings->imap_folder ?: 'INBOX',
            'imap_last_uid' => $settings->imap_last_uid,
            'imap_last_sync_at' => $settings->imap_last_sync_at,
            'imap_last_error' => $settings->imap_last_error,
        ];
    }
}
