<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class ActivityLogService
{
    public function log(
        ?User $actor,
        string $module,
        string $action,
        ?User $target = null,
        ?string $description = null,
        array $meta = []
    ): void {
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        ActivityLog::query()->create([
            'user_id' => $actor?->id,
            'target_user_id' => $target?->id,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'meta_json' => $meta ?: null,
        ]);
    }
}
