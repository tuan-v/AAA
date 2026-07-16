<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class LogPermissionAction
{
    // Các action chỉ đọc, không cần snapshot old/new (tránh log rác không cần thiết)
    private array $readOnlyActions = ['view', 'detail', 'history', 'select'];

    public function handle(Request $request, Closure $next)
    {
        $permission = $this->resolvePermissionFromRoute($request);

        // Không có permission nào gắn ở route này -> bỏ qua, không log
        if (! $permission || ! $request->user()) {
            return $next($request);
        }

        [$prefix, $action] = $this->splitPermission($permission);

        $modelClass = config("audit_models.$prefix");

        // Chụp snapshot TRƯỚC khi controller chạy (route model binding đã resolve xong ở giai đoạn này)
        $oldValues = null;
        $modelInstance = $this->resolveBoundModel($request, $modelClass);

        if ($modelInstance && ! in_array($action, ['create'])) {
            $oldValues = $modelInstance->toArray();
        }

        $response = $next($request);

        // Chỉ log nếu request thành công
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $newValues = null;
        $modelId = $modelInstance?->getKey();

        if (in_array($action, ['create', 'store'])) {
            // Với create, lấy id/data từ response trả về (giả định controller trả {data: model})
            $payload = json_decode($response->getContent(), true);
            $created = $payload['data'] ?? $payload;
            $modelId = is_array($created) ? ($created['id'] ?? null) : null;
            $newValues = is_array($created) ? $created : null;
        } elseif (! in_array($action, $this->readOnlyActions) && $modelClass && $modelId) {
            // update/approve/reject/lock/unlock... -> load lại để lấy giá trị mới
            $fresh = $modelClass::find($modelId);
            $newValues = $fresh?->toArray();
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'model_type' => $modelClass ?? 'Unknown',
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $this->buildDescription($action, $prefix),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $response;
    }

    private function resolvePermissionFromRoute(Request $request): ?string
    {
        foreach ($request->route()->gatherMiddleware() as $middleware) {
            if (str_starts_with($middleware, 'permission:')) {
                return substr($middleware, strlen('permission:'));
            }
        }
        return null;
    }

    private function splitPermission(string $permission): array
    {
        $pos = strrpos($permission, '.');
        if ($pos === false) {
            return [$permission, 'unknown'];
        }
        return [substr($permission, 0, $pos), substr($permission, $pos + 1)];
    }

    private function resolveBoundModel(Request $request, ?string $modelClass)
    {
        if (! $modelClass) return null;

        foreach ($request->route()->parameters() as $value) {
            if ($value instanceof $modelClass) {
                return $value;
            }
        }
        return null;
    }

    private function buildDescription(string $action, string $prefix): string
    {
        $labels = [
            'view' => 'Xem',
            'detail' => 'Xem chi tiết',
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            'approve' => 'Duyệt',
            'reject' => 'Từ chối',
            'lock' => 'Khóa',
            'unlock' => 'Mở khóa',
            'history' => 'Xem lịch sử',
        ];

        return ($labels[$action] ?? ucfirst($action)) . ' ' . str_replace('_', ' ', $prefix);
    }
}
