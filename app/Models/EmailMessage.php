<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmailAttachment;
use App\Models\EmailAuditLog;

class EmailMessage extends Model
{
    use HasFactory;

    public function attachments()
    {
        return $this->hasMany(EmailAttachment::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(EmailAuditLog::class);
    }

    protected $fillable = [
        'user_id',
        'folder',
        'direction',
        'status',
        'send_status',
        'send_error',
        'thread_id',
        'spam_score',
        'spam_flag',
        'message_id',
        'in_reply_to',
        'references',
        'from_email',
        'from_name',
        'to_email',
        'reply_to',
        'cc',
        'bcc',
        'subject',
        'body_html',
        'body_text',
        'sent_at',
        'read_at',
        'deleted_at',
    ];

    protected $casts = [
        'cc' => 'array',
        'bcc' => 'array',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
