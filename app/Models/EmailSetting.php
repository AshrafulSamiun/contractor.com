<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    protected $fillable = [
        'user_id',
        'use_global_settings',
        'from_name',
        'from_email',
        'reply_to',
        'default_cc',
        'default_bcc',
        'signature_html',
        'signature_text',
        'imap_enabled',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_username',
        'imap_password',
        'imap_folder',
        'imap_last_uid',
        'imap_last_sync_at',
        'imap_last_error',
    ];

    protected $casts = [
        'default_cc' => 'array',
        'default_bcc' => 'array',
        'use_global_settings' => 'boolean',
        'imap_enabled' => 'boolean',
        'imap_last_sync_at' => 'datetime',
    ];
}
