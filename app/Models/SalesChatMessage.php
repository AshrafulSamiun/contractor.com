<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesChatMessage extends Model
{
    protected $table = 'sales_chat_messages';

    protected $fillable = [
        'sales_chat_session_id',
        'role',
        'message',
    ];

    protected $touches = ['session'];

    public function session()
    {
        return $this->belongsTo(SalesChatSession::class, 'sales_chat_session_id');
    }
}
