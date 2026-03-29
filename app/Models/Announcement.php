<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'announcement_no',
        'occurred_at',
        'facility_id',
        'facility_name',
        'recipient_mode',
        'recipient_ids',
        'recipient_labels',
        'required_actions',
        'title',
        'body',
        'priority',
        'status',
        'audience',
        'target_roles',
        'requires_approval',
        'approval_status',
        'approved_by',
        'approved_at',
        'notified_at',
        'publish_at',
        'expires_at',
        'pinned',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'facility_id' => 'integer',
        'recipient_ids' => 'array',
        'recipient_labels' => 'array',
        'required_actions' => 'array',
        'publish_at' => 'datetime',
        'expires_at' => 'datetime',
        'pinned' => 'boolean',
        'target_roles' => 'array',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AnnouncementAttachment::class);
    }
}
