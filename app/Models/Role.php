<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'type',
        'is_protected',
        'company_id',
        'description', // CHỈ thêm dòng này nếu bảng roles đã có cột description thật
    ];

    protected $casts = [
        'is_protected' => 'boolean',
    ];

    public function isSystem(): bool
    {
        return $this->type === 'system';
    }

    public function isUserCreated(): bool
    {
        return $this->type === 'user';
    }

    // KHÔNG tự viết lại users()/permissions() ở đây nữa.
    // SpatieRole (class cha) đã có sẵn:
    //   - permissions() -> quan hệ đúng chuẩn tới Spatie\Permission\Models\Permission
    //     qua bảng role_has_permissions
    //   - Quan hệ tới User thông qua model_has_roles được Spatie xử lý nội bộ,
    //     dùng User::role('TenVaiTro')->get() để lấy user theo role thay vì
    //     tự viết belongsToMany.
}
