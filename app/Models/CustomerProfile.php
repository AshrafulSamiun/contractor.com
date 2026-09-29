<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/** Customer profiles are account holders whose account type is Customer (1). */
class CustomerProfile extends AccountHolder
{
    protected $table = 'account_holders';

    protected static function booted(): void
    {
        static::addGlobalScope('customer_profiles', fn (Builder $query) => $query->where('account_type', 1));
        static::creating(fn (self $customer) => $customer->account_type = 1);
    }
}
