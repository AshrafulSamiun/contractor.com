<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'bank_no', 'bank_name', 'status_active', 'currency_id',
        'linked_gl_account_id', 'account_name', 'account_number', 'opening_balance',
        'opening_balance_date', 'contact_person', 'phone_number', 'email', 'website',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'status_active' => 'boolean',
        'opening_balance' => 'decimal:2',
        'opening_balance_date' => 'date',
    ];
}
