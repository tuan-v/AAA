<?php

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ActivityLog::query()
            ->where('model_type', User::class)
            ->orderBy('id')
            ->chunkById(200, function ($logs) {
                foreach ($logs as $log) {
                    $values = array_merge($log->old_values ?? [], $log->new_values ?? []);
                    $code = (string) ($values['code'] ?? '');
                    $modelClass = str_starts_with($code, 'PB-')
                        ? Department::class
                        : (str_starts_with($code, 'CV-') ? Position::class : null);

                    if (! $modelClass) {
                        continue;
                    }

                    $action = ActivityLog::canonicalAction($log->action) ?? $log->action;
                    $log->update([
                        'model_type' => $modelClass,
                        'description' => ActivityLog::actionLabel($action).' '.mb_strtolower(ActivityLog::modelLabel($modelClass))." #{$log->model_id}",
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Không hoàn tác vì không thể phân biệt an toàn log User hợp lệ sau khi đã sửa.
    }
};
