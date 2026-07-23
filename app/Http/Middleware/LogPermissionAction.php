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

        $modelClass = config("audit_models.$prefix");
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
            // Với create, lấy id/data từ response trả về (giả định controller trả {data: model})
            $payload = json_decode($response->getContent(), true);
            $created = $payload['data'] ?? $payload;
            $modelId = is_array($created) ? ($created['id'] ?? null) : null;
            $newValues = is_array($created) ? $created : null;
        } elseif ($modelId) {
            // update/approve/reject/lock/unlock... -> load lại để lấy giá trị mới
            $fresh = $modelClass::find($modelId);
            $newValues = $fresh?->toArray();

            if ($canonicalAction === 'lock' && ($oldValues['status'] ?? null) === 'inactive' && ($newValues['status'] ?? null) === 'active') {
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
            'description' => $this->buildDescription($canonicalAction, $prefix, $modelId),
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

    private function buildDescription(string $action, string $prefix, int|string $modelId): string
    {
        $labels = [
            'create' => 'Thêm mới', 'update' => 'Cập nhật', 'delete' => 'Xóa',
            'approve' => 'Duyệt', 'reject' => 'Từ chối', 'lock' => 'Khóa',
            'unlock' => 'Mở khóa', 'cancel' => 'Hủy',
        ];

        $modules = [
            'nhan_su' => 'nhân sự', 'vai_tro' => 'vai trò', 'quyen' => 'quyền',
            'nhat_ky' => 'nhật ký hoạt động', 'kho' => 'kho', 'san_pham_kho' => 'sản phẩm kho',
            'danh_muc_kho' => 'danh mục kho', 'don_vi_kho' => 'đơn vị kho', 'phieu_kho' => 'phiếu kho',
            'chuyen_kho' => 'chuyển kho', 'nha_cung_cap' => 'nhà cung cấp',
            'danh_muc_mua_hang' => 'danh mục mua hàng', 'don_vi_mua_hang' => 'đơn vị mua hàng',
            'san_pham_mua_hang' => 'sản phẩm mua hàng', 'don_mua' => 'đơn mua',
            'khach_hang' => 'khách hàng', 'don_ban' => 'đơn bán', 'tien_te' => 'tiền tệ',
            'ngan_hang' => 'ngân hàng', 'tai_khoan' => 'tài khoản',
            'cong_no_khach_hang' => 'công nợ khách hàng',
            'cong_no_nha_cung_cap' => 'công nợ nhà cung cấp',
            'loai_giao_dich' => 'loại giao dịch', 'giao_dich' => 'giao dịch',
        ];

        return ($labels[$action] ?? 'Thao tác').' '.($modules[$prefix] ?? str_replace('_', ' ', $prefix))." #{$modelId}";
    }
}
