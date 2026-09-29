<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditCard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'card_no', 'card_nickname', 'card_number', 'card_last_four', 'cardholder_name',
        'card_type', 'card_network', 'expiry_date', 'credit_limit', 'current_balance', 'billing_day',
        'due_day', 'pay_day', 'status_active', 'card_category', 'currency_id', 'bank_profile_id',
        'account_holder_id', 'annual_fee', 'payment_due_day', 'grace_period_days',
        'minimum_payment_percent', 'interest_rate', 'billing_address', 'city', 'state', 'postal_code',
        'country_id', 'phone_number', 'email', 'department', 'assigned_to', 'notes', 'is_default',
        'created_by', 'updated_by',
    ];

    protected $hidden = ['card_number'];
    protected $appends = ['masked_card_number', 'available_credit'];
    protected $casts = [
        'card_number' => 'encrypted', 'status_active' => 'boolean', 'is_default' => 'boolean',
        'credit_limit' => 'decimal:2', 'current_balance' => 'decimal:2', 'annual_fee' => 'decimal:2',
        'minimum_payment_percent' => 'decimal:2', 'interest_rate' => 'decimal:2',
    ];

    public function getMaskedCardNumberAttribute(): string
    {
        return '**** **** **** '.$this->card_last_four;
    }

    public function getAvailableCreditAttribute(): string
    {
        return number_format(max(0, (float) $this->credit_limit - (float) $this->current_balance), 2, '.', '');
    }
}
