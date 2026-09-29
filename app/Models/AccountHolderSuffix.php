<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountHolderSuffix extends Model
{
    protected $table = 'account_holder_suffixes';

    protected $fillable = ['suffix', 'prifix', 'status_active'];

    public $timestamps = false;
}
