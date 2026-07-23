<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public const ACTION_ALIASES = [
        'create' => ['create', 'them', 'POST'],
        'update' => ['update', 'sua', 'PUT', 'PATCH'],
        'approve' => ['approve', 'duyet'],
        'reject' => ['reject', 'tu_choi'],
        'cancel' => ['cancel', 'huy'],
        'delete' => ['delete', 'xoa', 'DELETE'],
        'lock' => ['lock', 'khoa'],
        'unlock' => ['unlock', 'mo_khoa'],
    ];

    public const ACTION_LABELS = [
        'create' => 'Thêm mới',
        'update' => 'Cập nhật',
        'approve' => 'Duyệt',
        'reject' => 'Từ chối',
        'cancel' => 'Hủy',
        'delete' => 'Xóa',
        'lock' => 'Khóa',
        'unlock' => 'Mở khóa',
    ];

    public const MODEL_LABELS = [
        'User' => 'Nhân sự',
        'Role' => 'Vai trò',
        'Permission' => 'Quyền hạn',
        'Company' => 'Công ty',
        'Warehouse' => 'Kho hàng',
        'WarehouseSlip' => 'Phiếu kho',
        'WarehouseTransfer' => 'Phiếu chuyển kho',
        'Product' => 'Sản phẩm',
        'Category' => 'Danh mục',
        'Unit' => 'Đơn vị tính',
        'Supplier' => 'Nhà cung cấp',
        'PurchaseOrder' => 'Đơn mua hàng',
        'Customer' => 'Khách hàng',
        'SalesOrder' => 'Đơn bán hàng',
        'Currency' => 'Tiền tệ',
        'Bank' => 'Ngân hàng',
        'Account' => 'Tài khoản/quỹ',
        'TransactionCategory' => 'Loại giao dịch',
        'Transaction' => 'Giao dịch',
        'Department' => 'Phòng ban',
        'Position' => 'Chức vụ',
    ];

    protected $fillable = [
        'company_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (ActivityLog $log) {
            if ($log->company_id) return;

            $log->company_id = auth()->user()?->company_id
                ?? User::query()->whereKey($log->user_id)->value('company_id');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeTrackable($query)
    {
        return $query
            ->whereIn('action', collect(self::ACTION_ALIASES)->flatten()->all())
            ->where('model_id', '>', 0);
    }

    public static function canonicalAction(?string $action): ?string
    {
        foreach (self::ACTION_ALIASES as $canonical => $aliases) {
            if (in_array($action, $aliases, true)) {
                return $canonical;
            }
        }

        return null;
    }

    public static function aliasesFor(string $action): array
    {
        return self::ACTION_ALIASES[$action] ?? [];
    }

    public static function actionLabel(?string $action): string
    {
        $canonical = self::canonicalAction($action) ?? $action;

        return self::ACTION_LABELS[$canonical] ?? 'Hành động khác';
    }

    public static function modelLabel(?string $modelType): string
    {
        $shortName = class_basename($modelType ?: '');

        return self::MODEL_LABELS[$shortName] ?? ($shortName ?: 'Dữ liệu hệ thống');
    }
}
