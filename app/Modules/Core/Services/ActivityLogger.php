<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public function log(
        string $module,
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $changes = null,
        ?int $userId = null,
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'changes' => $changes,
            'ip_address' => request()?->ip(),
        ]);
    }

    /** Keep only meaningful attribute changes and hide noisy/system fields. */
    public function changedFields(Model $model): array
    {
        $changes = collect($model->getChanges())
            ->except(['updated_at', 'created_at', 'password', 'remember_token'])
            ->map(fn ($value, $key) => [
                'from' => $model->getOriginal($key),
                'to' => $value,
            ])
            ->all();

        return $changes;
    }
}
