<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmailMessage;

class EmailAttachment extends Model
{
    use HasFactory;

    public function emailMessage()
    {
        return $this->belongsTo(EmailMessage::class);
    }

    protected $fillable = [
        'email_message_id',
        'original_name',
        'path',
        'mime',
        'size',
    ];
}
