<?php

namespace App\Services;

class NotificationTemplateRenderer
{
    public static function render(?string $template, array $context = []): string
    {
        if (!$template) {
            return '';
        }

        $replacements = [];
        foreach ($context as $key => $value) {
            $replacements['{' . $key . '}'] = $value ?? '';
        }

        return strtr($template, $replacements);
    }
}
