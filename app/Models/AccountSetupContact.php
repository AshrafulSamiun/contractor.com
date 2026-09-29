<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountSetupContact extends Model
{
    protected $fillable = [
        'account_setup_id',
        'department_name',
        'contact_person',
        'phone_number',
        'email',
        'position_title',
        'sort_order',
    ];

    public function accountSetup()
    {
        return $this->belongsTo(AccountSetup::class);
    }
}
