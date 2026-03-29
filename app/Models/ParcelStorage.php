<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParcelStorage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'storage_name',
        'facility_id',
        'facility_name',
        'floor_no',
        'area_section',
        'dedicated_item',
        'is_active',
        'note',
    ];

    protected $casts = [
        'facility_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
