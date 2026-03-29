<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesChatSession extends Model
{
    protected $table = 'sales_chat_sessions';

    protected $fillable = [
        'chat_no',
        'chat_datetime',
        'first_name',
        'last_name',
        'company_name',
        'work_email',
        'business_phone',
        'country_id',
        'country',
        'city',
        'call_time',
        'inquiry',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'country_id' => 'integer',
        'chat_datetime' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(SalesChatMessage::class, 'sales_chat_session_id');
    }

    public function countryRef()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
