<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ProjectScoped;

class JobSite extends Model
{
    use HasFactory, ProjectScoped;

    protected $table = 'job_sites';

    protected $fillable = [
        'project_id',
        'job_site_no',
        'job_site_name',
        'contact_no',
        'start_date',
        'end_date',
        'description',
        'customer_no',
        'customer_name',
        'address',
        'contact_person',
        'phone',
        'email',
        'status',
        'note',
        'inserted_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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
