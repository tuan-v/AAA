<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class LogPermissionAction
{
    // Các action chỉ đọc, không cần snapshot old/new (tránh log rác không cần thiết)
    private array $readOnlyActions = ['xem', 'xem_chi_tiet', 'xem_lich_su', 'chon'];

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

        if ($modelInstance && $action !== 'them') {
            $oldValues = $modelInstance->toArray();
        }

        $response = $next($request);

        // Chỉ log nếu request thành công
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $newValues = null;
        $modelId = $modelInstance?->getKey();

        if ($action === 'them') {
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

        // Route danh sách không bind một model cụ thể nên không có model_id.
        // Không được để việc ghi audit làm request nghiệp vụ thành lỗi 500.
        if (!$modelId) {
            return $response;
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
            'xem' => 'Xem', 'xem_chi_tiet' => 'Xem chi tiết', 'them' => 'Thêm',
            'sua' => 'Cập nhật', 'xoa' => 'Xóa', 'duyet' => 'Duyệt',
            'tu_choi' => 'Từ chối', 'khoa' => 'Khóa', 'mo_khoa' => 'Mở khóa',
            'huy' => 'Hủy', 'xem_lich_su' => 'Xem lịch sử',
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

        return ($labels[$action] ?? ucfirst($action)).' '.($modules[$prefix] ?? str_replace('_', ' ', $prefix));
    }
}
