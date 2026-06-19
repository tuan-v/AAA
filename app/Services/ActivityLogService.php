<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public static function log($model, $action, $description, $old = null, $new = null)
    {
        return ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
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
