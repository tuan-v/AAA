<?php

/**
 * Bảng ánh xạ: tiền tố permission (phần trước dấu ".") -> Model tương ứng.
 *
 * Middleware App\Http\Middleware\LogPermissionAction dùng file này để:
 *  - Biết cần load model nào theo route param (lấy old_values trước khi thay đổi).
 *  - Biết cần query lại model nào sau khi hành động thành công (lấy new_values).
 *  - Ghi đúng model_type vào bảng audit_logs.
 *
 * QUY TẮC: mỗi permission bạn dùng trong middleware('permission:xxx.yyy') ở routes/api.php
 * đều nên có 1 dòng tương ứng ở đây (dùng phần "xxx" làm key), nếu không middleware
 * vẫn ghi log được nhưng model_type sẽ là "Unknown" và không lấy được old/new_values.
 *
 * GIẢ ĐỊNH: Role & Permission dùng package spatie/laravel-permission (dựa theo code
 * AppServiceProvider của bạn đang gọi $user->getAllPermissions() / getRoleNames(),
 * đây là các hàm helper của package này). Nếu bạn có Model Role/Permission tự viết
 * riêng (không dùng Spatie), đổi lại namespace 2 dòng bên dưới cho đúng.
 */

return [

    // ==================== QUẢN LÝ ====================
    'nhan_su' => \App\Models\User::class,
    'vai_tro' => \Spatie\Permission\Models\Role::class,
    'quyen' => \Spatie\Permission\Models\Permission::class,
    'nhat_ky' => \App\Models\ActivityLog::class,
    'company' => \App\Models\Company::class,

    // ==================== KHO (WAREHOUSE) ====================
    'kho' => \App\Models\Warehouse::class,
    'san_pham_kho' => \App\Models\Product::class,
    'danh_muc_kho' => \App\Models\Category::class,
    'don_vi_kho' => \App\Models\Unit::class,
    'phieu_kho' => \App\Models\WarehouseSlip::class,
    'chuyen_kho' => \App\Models\WarehouseTransfer::class,

    // ==================== MUA HÀNG (PURCHASE) ====================
    'nha_cung_cap' => \App\Models\Supplier::class,
    'danh_muc_mua_hang' => \App\Models\Category::class,
    'don_vi_mua_hang' => \App\Models\Unit::class,
    'san_pham_mua_hang' => \App\Models\Product::class,
    'don_mua' => \App\Models\PurchaseOrder::class,

    // ==================== BÁN HÀNG (SALE) ====================
    'khach_hang' => \App\Models\Customer::class,
    'don_ban' => \App\Models\SalesOrder::class,

    // ==================== KẾ TOÁN (ACCOUNTANT) ====================
    'tien_te' => \App\Models\Currency::class,
    'ngan_hang' => \App\Models\Bank::class,
    'tai_khoan' => \App\Models\Account::class,
    'cong_no_khach_hang' => \App\Models\Customer::class,
    'cong_no_nha_cung_cap' => \App\Models\Supplier::class,
    'loai_giao_dich' => \App\Models\TransactionCategory::class,
    'giao_dich' => \App\Models\Transaction::class,

];
