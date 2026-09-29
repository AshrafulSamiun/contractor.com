<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountGroup extends Model
{
    protected $fillable = ['project_id', 'main_group', 'sub_group_code', 'sub_group', 'statement_type', 'account_type', 'cash_flow_group', 'retained_earnings', 'inserted_by', 'updated_by', 'status_active', 'is_deleted'];
    protected $casts = ['status_active' => 'boolean', 'is_deleted' => 'boolean'];
}
