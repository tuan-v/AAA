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
    'user' => \App\Models\User::class,
    'role' => \Spatie\Permission\Models\Role::class,
    'permission' => \Spatie\Permission\Models\Permission::class,
    'auditlog' => \App\Models\ActivityLog::class,
    'company' => \App\Models\Company::class,

    // ==================== KHO (WAREHOUSE) ====================
    'warehouse' => \App\Models\Warehouse::class,
    'warehouse_product' => \App\Models\Product::class,
    'warehouse_category' => \App\Models\Category::class,
    'warehouse_unit' => \App\Models\Unit::class,
    'warehouse_slip' => \App\Models\WarehouseSlip::class,

    // ==================== MUA HÀNG (PURCHASE) ====================
    'supplier' => \App\Models\Supplier::class,
    'purchase_category' => \App\Models\Category::class,
    'purchase_unit' => \App\Models\Unit::class,
    'product' => \App\Models\Product::class,
    'purchase_order' => \App\Models\PurchaseOrder::class,

    // ==================== BÁN HÀNG (SALE) ====================
    'sale_customer' => \App\Models\Customer::class,
    'sale_order' => \App\Models\SalesOrder::class,

    // ==================== KẾ TOÁN (ACCOUNTANT) ====================
    'currency' => \App\Models\Currency::class,
    'bank' => \App\Models\Bank::class,
    'account' => \App\Models\Account::class,
    'customer_debt' => \App\Models\Customer::class,
    'supplier_debt' => \App\Models\Supplier::class,
    'transaction_category' => \App\Models\TransactionCategory::class,
    'transaction' => \App\Models\Transaction::class,

];
