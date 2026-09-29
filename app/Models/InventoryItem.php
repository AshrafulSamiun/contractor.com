<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ProjectScoped;

class InventoryItem extends Model
{
    use HasFactory, ProjectScoped;

    protected $table = 'inventory_items';

    protected $fillable = [
        'project_id',
        'item_no',
        'item_name',
        'unit_of_measure',
        'price',
        'sales_tax_applicable',
        'status',
        'note',
        'inserted_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sales_tax_applicable' => 'boolean',
        'status' => 'integer',
        'is_deleted' => 'boolean',
        'inserted_by' => 'integer',
        'updated_by' => 'integer',
        'project_id' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 2);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function inserter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inserted_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
