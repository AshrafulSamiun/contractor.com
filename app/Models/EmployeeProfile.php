<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
class EmployeeProfile extends AccountHolder { protected $table='account_holders'; protected static function booted(): void { static::addGlobalScope('employees',fn(Builder $q)=>$q->where('account_type',4)); static::creating(fn(self $m)=>$m->account_type=4); } }
