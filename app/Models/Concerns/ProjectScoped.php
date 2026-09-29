<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ProjectScoped
{
    protected static function bootProjectScoped(): void
    {
        static::addGlobalScope('authenticated_project', function (Builder $query): void {
            if (auth()->check()) {
                $query->where($query->getModel()->getTable().'.project_id', auth()->user()->project_id);
            }
        });

        static::creating(function ($model): void {
            if (auth()->check()) {
                $model->project_id = auth()->user()->project_id;
                $model->inserted_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model): void {
            if (auth()->check()) {
                $model->project_id = auth()->user()->project_id;
                $model->updated_by = auth()->id();
            }
        });
    }
}
