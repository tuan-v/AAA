<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public static function log($model, $action, $description, $old = null, $new = null)
    {
        $canonicalAction = ActivityLog::canonicalAction($action);
        if (! $canonicalAction) {
            return null;
        }

        return ActivityLog::create([
            'company_id' => auth()->user()?->company_id ?? $model->company_id ?? null,
            'user_id' => auth()->id(),
            'action' => $canonicalAction,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $old,
            'new_values' => $new,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
