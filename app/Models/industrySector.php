<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class industrySector extends Model
{
    protected $table = 'industry_sectors';

    protected $fillable = ['project_id', 'sector_name', 'status_active'];

    public $timestamps = false;
}
