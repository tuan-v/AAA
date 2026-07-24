<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class LogPermissionAction
{
    public function handle(Request $request, Closure $next)
    {
        $permission = $this->resolvePermissionFromRoute($request);

        // Nhật ký nghiệp vụ chỉ ghi hành động làm thay đổi dữ liệu, không ghi thao tác xem.
        if (! $permission || ! $request->user() || in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        [$prefix, $action] = $this->splitPermission($permission);
        $canonicalAction = ActivityLog::canonicalAction($action);
        if (! $canonicalAction) {
            return $next($request);
        }

        $modelClass = $this->resolveModelClass($request, $prefix);
        if (! $modelClass || ! is_subclass_of($modelClass, Model::class)) {
            return $next($request);
        }

        // Chụp snapshot TRƯỚC khi controller chạy (route model binding đã resolve xong ở giai đoạn này)
        $oldValues = null;
        $modelInstance = $this->resolveModel($request, $modelClass);
        $startedAt = now()->subSecond();

        if ($modelInstance && $canonicalAction !== 'create') {
            $oldValues = $modelInstance->toArray();
        }

        $response = $next($request);

        // Chỉ log nếu request thành công
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $newValues = null;
        $modelId = $modelInstance?->getKey();

        if ($canonicalAction === 'create') {
            // Các API cũ trả model ở nhiều khóa khác nhau (data, user hoặc payload gốc).
            $payload = json_decode($response->getContent(), true);
            $created = $this->resolveCreatedModelPayload($payload);
            $modelId = $created['id'] ?? null;
            $newValues = $created;
        } elseif ($modelId) {
            // update/approve/reject/lock/unlock... -> load lại để lấy giá trị mới
            $fresh = $modelClass::find($modelId);
            $newValues = $fresh?->toArray();

            if ($canonicalAction === 'lock'
                && ($oldValues['status'] ?? null) !== 'active'
                && ($newValues['status'] ?? null) === 'active') {
                $canonicalAction = 'unlock';
            }
        }

        // Route danh sách không bind một model cụ thể nên không có model_id.
        // Không được để việc ghi audit làm request nghiệp vụ thành lỗi 500.
        if (!$modelId) {
            return $response;
        }

        // Một số luồng nghiệp vụ tự ghi log chi tiết trong service/controller.
        // Không tạo thêm bản ghi trùng từ middleware cho cùng request.
        $alreadyLogged = ActivityLog::query()
            ->where('company_id', $request->user()->company_id)
            ->where('user_id', $request->user()->id)
            ->where('model_type', $modelClass)
            ->where('model_id', $modelId)
            ->whereIn('action', ActivityLog::aliasesFor($canonicalAction))
            ->where('created_at', '>=', $startedAt)
            ->exists();

        if ($alreadyLogged) {
            return $response;
        }

        ActivityLog::create([
            'company_id' => $request->user()->company_id,
            'user_id' => $request->user()->id,
            'action' => $canonicalAction,
            'model_type' => $modelClass,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $this->buildDescription($canonicalAction, $modelClass, $modelId),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $response;
    }

    private function resolveCreatedModelPayload(mixed $payload): ?array
    {
        if (! is_array($payload)) {
            return null;
        }

        if (isset($payload['id'])) {
            return $payload;
        }

        foreach (['data', 'user'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key]) && isset($payload[$key]['id'])) {
                return $payload[$key];
            }
        }

        return null;
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

    private function resolveModel(Request $request, string $modelClass): ?Model
    {
        foreach ($request->route()->parameters() as $value) {
            if ($value instanceof $modelClass) {
                return $value;
            }
        }

        foreach ($request->route()->parameters() as $value) {
            if (! is_numeric($value)) {
                continue;
            }

            $model = $modelClass::query()->find((int) $value);
            if ($model && (! isset($model->company_id) || (int) $model->company_id === (int) $request->user()->company_id)) {
                return $model;
            }
        }

        return null;
    }

    private function resolveModelClass(Request $request, string $permissionPrefix): ?string
    {
        $controller = $request->route()->getController();

        if ($controller instanceof \App\Http\Controllers\DepartmentController) {
            return \App\Models\Department::class;
        }

        if ($controller instanceof \App\Http\Controllers\PositionController) {
            return \App\Models\Position::class;
        }

        return config("audit_models.$permissionPrefix");
    }

    private function buildDescription(string $action, string $modelClass, int|string $modelId): string
    {
        $labels = [
            'create' => 'Thêm mới', 'update' => 'Cập nhật', 'delete' => 'Xóa',
            'approve' => 'Duyệt', 'reject' => 'Từ chối', 'lock' => 'Khóa',
            'unlock' => 'Mở khóa', 'cancel' => 'Hủy',
        ];

        $modelLabel = ActivityLog::modelLabel($modelClass);

        return ($labels[$action] ?? 'Thao tác').' '.mb_strtolower($modelLabel)." #{$modelId}";
    }
}
