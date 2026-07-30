# Mục lục module ERP

Tra nhanh màn hình, API, backend, dữ liệu và test của từng module. Cập nhật theo mã nguồn ngày **29/07/2026**.

> Nếu tài liệu khác code, ưu tiên [`routes/web.php`](routes/web.php), [`routes/api.php`](routes/api.php) và phần hiện thực trong mã nguồn.

## Bắt đầu nhanh

1. Chọn module trong bảng bên dưới.
2. Đi theo luồng **FE → API → Controller → Service → Model → Test**.
3. Nếu chưa quen dự án, đọc [`START_HERE.md`](START_HERE.md) và [`BUSINESS_FLOWS.md`](resources/docs/BUSINESS_FLOWS.md) trước.

`URL` → [`routes/web.php`](routes/web.php) → [`resources/js/Pages`](resources/js/Pages) → [`routes/api.php`](routes/api.php) → Controller → Service/Repository → Model → Database → Test

## Chọn module

| Module                                                        | Màn hình                              | Code chính                                                                                   |
| ------------------------------------------------------------- | ------------------------------------- | -------------------------------------------------------------------------------------------- |
| [Khung ứng dụng và Dashboard](#1-khung-ứng-dụng-và-dashboard) | `/`, `/dashboard`                     | [`DashBoard.vue`](resources/js/Pages/DashBoard.vue)                                          |
| [Công ty và hồ sơ](#2-công-ty-và-hồ-sơ-cá-nhân)               | `/company/*`, `/profile`              | [`Pages/Company`](resources/js/Pages/Company), [`Pages/Profile`](resources/js/Pages/Profile) |
| [Nhân sự và tổ chức](#3-nhân-sự-và-cơ-cấu-tổ-chức)            | `/user`, `/departments`, `/positions` | [`Pages/Manage`](resources/js/Pages/Manage)                                                  |
| [Vai trò và phân quyền](#4-vai-trò-và-phân-quyền)             | `/role`, `/permission`                | [`Role.vue`](resources/js/Pages/Manage/Role.vue)                                             |
| [Mua hàng](#5-mua-hàng)                                       | `/purchase/*`                         | [`Pages/Purchase`](resources/js/Pages/Purchase)                                              |
| [Bán hàng](#6-bán-hàng)                                       | `/sale/*`                             | [`Pages/Sale`](resources/js/Pages/Sale)                                                      |
| [Kho](#7-kho)                                                 | `/warehouse/*`                        | [`Pages/Warehouse`](resources/js/Pages/Warehouse)                                            |
| [Kế toán và công nợ](#8-kế-toán-giao-dịch-và-công-nợ)         | `/accountant/*`                       | [`Pages/Accountant`](resources/js/Pages/Accountant)                                          |
| [Nhật ký hoạt động](#9-nhật-ký-hoạt-động)                     | `/audit-logs`                         | [`Pages/AuditLog`](resources/js/Pages/AuditLog)                                              |
| [Thông báo và realtime](#10-thông-báo-và-realtime)            | Header                                | [`NotificationMenu.vue`](resources/js/components/layout/header/NotificationMenu.vue)         |
| [Xác thực](#11-xác-thực)                                      | `/login`, `/register`                 | [`routes/auth.php`](routes/auth.php)                                                         |
| [Thành phần dùng chung](#12-thành-phần-dùng-chung-và-hạ-tầng) | Toàn hệ thống                         | [`components`](resources/js/components), [`composables`](resources/js/composables)           |
| [Hướng dẫn sử dụng](#13-hướng-dẫn-sử-dụng)                    | `/guide`                              | [`Guide/Index.vue`](resources/js/Pages/Guide/Index.vue)                                      |

> Luồng nghiệp vụ nằm trong [`BUSINESS_FLOWS.md`](resources/docs/BUSINESS_FLOWS.md); các quyết định kỹ thuật nằm trong [`resources/docs/decisions/`](resources/docs/decisions/).

## 1. Khung ứng dụng và Dashboard

**Vai trò:** trang vào hệ thống, dashboard tổng hợp và khung giao diện dùng chung.

- **Điểm vào:** `/`, `/dashboard`; web và API route tại [`routes/web.php`](routes/web.php), [`routes/api.php`](routes/api.php).
- **Frontend:** [`DashBoard.vue`](resources/js/Pages/DashBoard.vue), [`AdminLayout.vue`](resources/js/Layouts/AdminLayout.vue), [`components/layout`](resources/js/components/layout); dashboard riêng nằm trong từng module.
- **Backend:** [`DashboardController`](app/Http/Controllers/DashboardController.php), [`DashboardService`](app/Services/DashboardService.php), [`DashboardRepository`](app/Repositories/DashboardRepository.php).
- **Kiểm thử:** [`ModuleDashboardTest`](tests/Feature/ModuleDashboardTest.php), [`DemoAccountPageSmokeTest`](tests/Feature/DemoAccountPageSmokeTest.php).

## 2. Công ty và hồ sơ cá nhân

**Vai trò:** tạo công ty, thiết lập ngữ cảnh công ty và quản lý hồ sơ người dùng hiện tại.

- **Điểm vào:** `/company/create`, `/company`, `/profile`; web route tại [`routes/web.php`](routes/web.php).
- **Frontend:** [`Company/Create.vue`](resources/js/Pages/Company/Create.vue), [`Profile/Edit.vue`](resources/js/Pages/Profile/Edit.vue).
- **Backend:** [`CompanyController`](app/Http/Controllers/CompanyController.php), [`ProfileController`](app/Http/Controllers/ProfileController.php), middleware `EnsureCompanyCreated`, `HandleInertiaRequests` và trait [`BelongsToCompany`](app/Traits/BelongsToCompany.php).
- **Dữ liệu:** [`Company`](app/Models/Company.php), [`User`](app/Models/User.php), [`CompanyCurrencyRate`](app/Models/CompanyCurrencyRate.php); migration tại [`database/migrations`](database/migrations).
- **Kiểm thử:** [`ProfileTest`](tests/Feature/ProfileTest.php), [`CompanyCurrencyServiceTest`](tests/Unit/CompanyCurrencyServiceTest.php), [`OpeningBalanceCurrencySnapshotTest`](tests/Feature/OpeningBalanceCurrencySnapshotTest.php).

## 3. Nhân sự và cơ cấu tổ chức

**Vai trò:** nhân viên, duyệt/từ chối tài khoản, phòng ban, chức vụ và quan hệ quản lý.

- **Điểm vào:** `/user`, `/user/{id}`, `/departments`, `/positions`; API `/api/users`, `/api/departments`, `/api/positions`.
- **Frontend:** [`User.vue`](resources/js/Pages/Manage/User.vue), [`UserForm.vue`](resources/js/Pages/Manage/UserForm.vue), [`UserDetail.vue`](resources/js/Pages/Manage/UserDetail.vue), [`Department`](resources/js/Pages/Manage/Department), [`Position`](resources/js/Pages/Manage/Position).
- **Backend:** [`UserController`](app/Http/Controllers/API/UserController.php), [`DepartmentController`](app/Http/Controllers/DepartmentController.php), [`PositionController`](app/Http/Controllers/PositionController.php).
- **Dữ liệu:** [`User`](app/Models/User.php), [`Department`](app/Models/Department.php), [`Position`](app/Models/Position.php), [`Company`](app/Models/Company.php); migration tại [`database/migrations`](database/migrations).
- **Kiểm thử:** [`DepartmentPositionFlowTest`](tests/Feature/DepartmentPositionFlowTest.php), [`DepartmentManagerAssignmentTest`](tests/Feature/DepartmentManagerAssignmentTest.php), [`UserListVisibilityTest`](tests/Feature/UserListVisibilityTest.php), [`UserActivityLogTest`](tests/Feature/UserActivityLogTest.php).

## 4. Vai trò và phân quyền

**Vai trò:** định nghĩa vai trò, permission và giới hạn hành động của từng tài khoản.

- **Điểm vào:** `/role`, `/permission`; API `/api/roles`, `/api/permissions`.
- **Frontend:** [`Role.vue`](resources/js/Pages/Manage/Role.vue), [`RoleForm.vue`](resources/js/Pages/Manage/RoleForm.vue), [`Permission.vue`](resources/js/Pages/Manage/Permission.vue), [`PermissionForm.vue`](resources/js/Pages/Manage/PermissionForm.vue), [`usePermission.js`](resources/js/composables/usePermission.js).
- **Backend:** [`RoleController`](app/Http/Controllers/RoleController.php), [`PermissionController`](app/Http/Controllers/PermissionController.php); middleware Spatie được khai báo tại [`bootstrap/app.php`](bootstrap/app.php).
- **Dữ liệu:** [`Role`](app/Models/Role.php), [`Permission`](app/Models/Permission.php), [`User`](app/Models/User.php), [`create_permission_tables`](database/migrations/2025_11_26_032146_create_permission_tables.php).
- **Kiểm thử:** [`PermissionListTest`](tests/Feature/PermissionListTest.php), [`DemoModuleRolesTest`](tests/Feature/DemoModuleRolesTest.php), [`DemoAccountPageSmokeTest`](tests/Feature/DemoAccountPageSmokeTest.php).

## 5. Mua hàng

**Vai trò:** nhà cung cấp, danh mục/sản phẩm mua, đơn mua, duyệt đơn và chuyển sang nhập kho/công nợ.

- **Điểm vào:** `/purchase/*`; web và API route tại [`routes/web.php`](routes/web.php), [`routes/api.php`](routes/api.php).
- **Frontend:** [`Purchase`](resources/js/Pages/Purchase) gồm [`Supplier`](resources/js/Pages/Purchase/Supplier), [`Product`](resources/js/Pages/Purchase/Product), [`Category`](resources/js/Pages/Purchase/Category), [`Unit`](resources/js/Pages/Purchase/Unit), [`Order`](resources/js/Pages/Purchase/Order).
- **Backend:** [`PurchaseOrderController`](app/Http/Controllers/PurchaseOrderController.php), [`SupplierController`](app/Http/Controllers/SupplierController.php), [`ProductController`](app/Http/Controllers/ProductController.php), [`CategoryController`](app/Http/Controllers/CategoryController.php), [`UnitController`](app/Http/Controllers/UnitController.php); service chính [`PurchaseOrderService`](app/Services/PurchaseOrderService.php).
- **Dữ liệu:** model tại [`app/Models`](app/Models): `Supplier`, `PurchaseOrder`, `PurchaseOrderItem`, `SupplierDebt`, `Product`, `Category`, `Unit`; migration tại [`database/migrations`](database/migrations).
- **Kiểm thử:** [`PurchaseToPaymentEndToEndTest`](tests/Feature/PurchaseToPaymentEndToEndTest.php), [`DebtFlowEndToEndTest`](tests/Feature/DebtFlowEndToEndTest.php), [`ProductAvailabilityTest`](tests/Feature/ProductAvailabilityTest.php).

> `ProductController`, `CategoryController` và `UnitController` được dùng chung với Kho. Hãy xác định ngữ cảnh bằng prefix route `/api/purchase` hoặc `/api/warehouse`.

## 6. Bán hàng

**Vai trò:** khách hàng, đơn bán, duyệt đơn, xuất kho, doanh thu và công nợ khách hàng.

- **Điểm vào:** `/sale/*`; web và API route tại [`routes/web.php`](routes/web.php), [`routes/api.php`](routes/api.php).
- **Frontend:** [`Customer`](resources/js/Pages/Sale/Customer), [`Order`](resources/js/Pages/Sale/Order), [`Dashboard.vue`](resources/js/Pages/Sale/Dashboard.vue).
- **Backend:** [`SalesOrderController`](app/Http/Controllers/SalesOrderController.php), [`CustomerController`](app/Http/Controllers/CustomerController.php); service liên quan [`CustomerDebtService`](app/Services/CustomerDebtService.php), [`StockService`](app/Services/StockService.php), [`CodeGeneratorService`](app/Services/CodeGeneratorService.php).
- **Dữ liệu:** model tại [`app/Models`](app/Models): `Customer`, `CustomerDebt`, `CustomerPayment`, `SalesOrder`, `SalesOrderItem`; migration tại [`database/migrations`](database/migrations).
- **Kiểm thử:** [`InventoryLifecycleEndToEndTest`](tests/Feature/InventoryLifecycleEndToEndTest.php), [`InventoryAccountingFlowTest`](tests/Feature/InventoryAccountingFlowTest.php), [`DebtSummaryTest`](tests/Feature/DebtSummaryTest.php), [`ProductAvailabilityTest`](tests/Feature/ProductAvailabilityTest.php).

## 7. Kho

**Vai trò:** kho, sản phẩm tồn, phiếu nhập/xuất, chuyển kho và sổ biến động tồn.

- **Điểm vào:** `/warehouse/*`; web và API route tại [`routes/web.php`](routes/web.php), [`routes/api.php`](routes/api.php).
- **Frontend:** [`Warehouse`](resources/js/Pages/Warehouse) gồm `Product`, `Category`, `Unit`, `Order`, `Slip`, `Transfer`, `InventoryMovement`.
- **Backend:** [`WarehouseController`](app/Http/Controllers/WarehouseController.php), [`WarehouseSlipController`](app/Http/Controllers/WarehouseSlipController.php), [`WarehouseInventoryController`](app/Http/Controllers/WarehouseInventoryController.php), [`InventoryMovementController`](app/Http/Controllers/InventoryMovementController.php), [`WarehouseTransferController`](app/Http/Controllers/WarehouseTransferController.php); service [`StockService`](app/Services/StockService.php), [`InventoryMovementService`](app/Services/InventoryMovementService.php).
- **Dữ liệu:** model tại [`app/Models`](app/Models): `Warehouse`, `WarehouseProductStock`, `Stock`, `WarehouseSlip`, `WarehouseSlipItem`, `WarehouseTransfer`, `WarehouseTransferItem`, `InventoryMovement`; migration tại [`database/migrations`](database/migrations).
- **Kiểm thử:** [`InventoryLifecycleEndToEndTest`](tests/Feature/InventoryLifecycleEndToEndTest.php), [`InventoryAccountingFlowTest`](tests/Feature/InventoryAccountingFlowTest.php), [`WarehouseFilterTest`](tests/Feature/WarehouseFilterTest.php), [`ProductAvailabilityTest`](tests/Feature/ProductAvailabilityTest.php).

## 8. Kế toán, giao dịch và công nợ

**Vai trò:** tiền tệ, ngân hàng, tài khoản/quỹ, giao dịch, sổ tài khoản, công nợ và báo cáo lãi lỗ.

- **Điểm vào:** `/accountant/*`; web và API route tại [`routes/web.php`](routes/web.php), [`routes/api.php`](routes/api.php).
- **Frontend:** [`Accountant`](resources/js/Pages/Accountant) gồm danh mục kế toán, giao dịch, sổ tài khoản, công nợ và báo cáo.
- **Backend:** [`AccountController`](app/Http/Controllers/AccountController.php), [`TransactionController`](app/Http/Controllers/TransactionController.php), [`AccountLedgerController`](app/Http/Controllers/Accountant/AccountLedgerController.php), [`ProfitLossReportController`](app/Http/Controllers/Accountant/ProfitLossReportController.php); các controller còn lại nằm trong [`app/Http/Controllers`](app/Http/Controllers).
- **Nghiệp vụ:** [`TransactionService`](app/Services/TransactionService.php), [`LedgerService`](app/Services/LedgerService.php), [`CurrencyService`](app/Services/CurrencyService.php), [`AccountBalanceService`](app/Services/AccountBalanceService.php); repository tại [`app/Repositories`](app/Repositories).
- **Dữ liệu:** model tại [`app/Models`](app/Models): `Currency`, `CurrencyRate`, `CompanyCurrencyRate`, `Bank`, `Account`, `AccountLedger`, `Transaction`, `TransactionCategory`, `CustomerDebt`, `SupplierDebt`; migration tại [`database/migrations`](database/migrations).
- **Kiểm thử:** [`TransactionFlowTest`](tests/Feature/TransactionFlowTest.php), [`DebtSummaryTest`](tests/Feature/DebtSummaryTest.php), [`DebtFlowEndToEndTest`](tests/Feature/DebtFlowEndToEndTest.php), [`OpeningBalanceCurrencySnapshotTest`](tests/Feature/OpeningBalanceCurrencySnapshotTest.php), [`TransactionCategoryCompanyIsolationTest`](tests/Feature/TransactionCategoryCompanyIsolationTest.php), [`InventoryAccountingFlowTest`](tests/Feature/InventoryAccountingFlowTest.php).

## 9. Nhật ký hoạt động

**Vai trò:** lưu vết ai đã làm gì, trên dữ liệu nào và trong công ty nào.

- **Điểm vào:** `/audit-logs`; API `/api/audit-logs`.
- **Frontend:** [`Index.vue`](resources/js/Pages/AuditLog/Index.vue), [`AuditLogDetail.vue`](resources/js/Pages/AuditLog/AuditLogDetail.vue).
- **Backend:** [`AuditLogController`](app/Http/Controllers/AuditLogController.php), [`ActivityLogService`](app/Services/ActivityLogService.php), middleware `LogPermissionAction`, `LogUserActivity`.
- **Dữ liệu:** [`ActivityLog`](app/Models/ActivityLog.php), [`create_activity_logs_table`](database/migrations/2026_06_19_152918_create_activity_logs_table.php).
- **Kiểm thử:** [`AuditLogFeatureTest`](tests/Feature/AuditLogFeatureTest.php), [`UserActivityLogTest`](tests/Feature/UserActivityLogTest.php).

## 10. Thông báo và realtime

**Vai trò:** thông báo theo người dùng/module và tự làm mới dữ liệu khi công ty có thay đổi.

- **Điểm vào:** menu trên header; API `/api/notifications`; WebSocket tại [`routes/channels.php`](routes/channels.php).
- **Frontend:** [`NotificationMenu.vue`](resources/js/components/layout/header/NotificationMenu.vue), [`echo.js`](resources/js/echo.js), [`companyData.js`](resources/js/realtime/companyData.js), [`useRealtimeRefresh.js`](resources/js/composables/useRealtimeRefresh.js).
- **Backend:** [`NotificationController`](app/Http/Controllers/NotificationController.php), [`BroadcastController`](app/Http/Controllers/BroadcastController.php), [`NotificationService`](app/Services/NotificationService.php); event `NotificationCreated`, `CompanyDataChanged`.
- **Dữ liệu:** [`Notification`](app/Models/Notification.php), [`create_notifications_table`](database/migrations/2026_07_22_120000_create_notifications_table.php).
- **Kiểm thử:** [`NotificationFeatureTest`](tests/Feature/NotificationFeatureTest.php), [`NotificationRecipientsTest`](tests/Feature/NotificationRecipientsTest.php), [`NotificationCreatedTest`](tests/Unit/NotificationCreatedTest.php), [`CompanyDataChangedTest`](tests/Unit/CompanyDataChangedTest.php).

## 11. Xác thực

**Vai trò:** đăng nhập, đăng ký, đăng xuất, đặt lại mật khẩu, xác minh email và Google OAuth.

- **Điểm vào:** `/login`, `/register`, `/forgot-password`; route tại [`routes/auth.php`](routes/auth.php).
- **Frontend:** Blade và shell view tại [`resources/views`](resources/views).
- **Backend:** controller tại [`app/Http/Controllers/Auth`](app/Http/Controllers/Auth), cấu hình tại [`config/auth.php`](config/auth.php), [`config/services.php`](config/services.php).
- **Dữ liệu:** [`User`](app/Models/User.php), [`PasswordResetToken`](app/Models/PasswordResetToken.php), [`Session`](app/Models/Session.php).
- **Kiểm thử:** toàn bộ [`tests/Feature/Auth`](tests/Feature/Auth).

## 12. Thành phần dùng chung và hạ tầng

| Nhóm                    | Mở tại                                                                                                                            |
| ----------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| Khởi tạo frontend       | [`app.js`](resources/js/app.js), [`bootstrap.js`](resources/js/bootstrap.js), [`echo.js`](resources/js/echo.js)                   |
| Giao diện dùng chung    | [`Layouts`](resources/js/Layouts), [`components`](resources/js/components), [`components/layout`](resources/js/components/layout) |
| Logic frontend dùng lại | [`composables`](resources/js/composables)                                                                                         |
| Backend dùng chung      | [`Middleware`](app/Http/Middleware), [`Providers`](app/Providers), [`Traits`](app/Traits), [`Helpers`](app/Helpers)               |
| Cấu hình                | [`config`](config/), [`.env.example`](.env.example), [`vite.config.js`](vite.config.js), [`package.json`](package.json)           |
| Database                | [`migrations`](database/migrations), `seeders`, `factories`                                                                       |
| Scheduler               | [`routes/console.php`](routes/console.php)                                                                                        |
| Test                    | [`tests/Feature`](tests/Feature), [`tests/Unit`](tests/Unit)                                                                      |

## 13. Hướng dẫn sử dụng

**Vai trò:** cung cấp hướng dẫn thao tác và tài liệu nghiệp vụ ngay trong ứng dụng.

- **Điểm vào:** `/guide`; route tại [`routes/web.php`](routes/web.php).
- **Frontend:** [`resources/js/Pages/Guide/Index.vue`](resources/js/Pages/Guide/Index.vue).
- **Nội dung tham chiếu:** [`Document.md`](Document.md), [`BUSINESS_FLOWS.md`](resources/docs/BUSINESS_FLOWS.md).
- **Lưu ý:** đây là trang nội dung tĩnh, hiện không có Controller, Model hoặc API riêng.

## Tra cứu nhanh khi sửa code

| Muốn sửa                       | Mở đầu tiên                                                                             |
| ------------------------------ | --------------------------------------------------------------------------------------- |
| Chữ, màu, icon, vị trí button  | Vue page trong [`resources/js/Pages`](resources/js/Pages), sau đó component được import |
| Button ẩn/hiện hoặc bị disable | `v-if`, `v-show`, `disabled`, `usePermission.js`                                        |
| Hành động khi bấm button       | Hàm `@click`, rồi tìm Axios endpoint                                                    |
| URL màn hình                   | [`routes/web.php`](routes/web.php)                                                      |
| API và permission              | [`routes/api.php`](routes/api.php)                                                      |
| Validate dữ liệu               | [`app/Http/Requests`](app/Http/Requests) hoặc validation trong Controller               |
| Quy tắc nghiệp vụ/trạng thái   | Service, sau đó Controller nếu module cũ chưa tách Service                              |
| Truy vấn dữ liệu               | Repository hoặc Eloquent Model/Controller                                               |
| Cấu trúc bảng                  | [`database/migrations`](database/migrations) và Model                                   |
| Response JSON                  | [`app/Http/Resources`](app/Http/Resources) hoặc Controller                              |
| Lỗi 403                        | Permission middleware, role/permission seeder và quyền user                             |
| Lỗi 422                        | Form Request/validation                                                                 |
| Lỗi 500                        | [`storage/logs/laravel.log`](storage/logs/laravel.log), Controller và Service           |

<details>
<summary><strong>Lệnh tìm kiếm thường dùng</strong></summary>

<br>

```bash
# Tìm chữ đang hiển thị trên giao diện
rg -n "Nội dung cần tìm" resources/js

# Tìm endpoint từ DevTools > Network
rg -n "purchase/orders" routes app resources/js tests

# Tìm toàn bộ nơi dùng một permission
rg -n "don_mua.duyet" routes app resources/js database tests

# Xem route đã đăng ký
php artisan route:list --path=purchase

# Chạy test đúng luồng
php artisan test --filter=PurchaseToPaymentEndToEndTest
```

</details>

## Tài liệu đọc sâu

- Tổng quan kiến trúc và toàn bộ luồng: [`PROJECT_INDEX.md`](PROJECT_INDEX.md).
- Đặc tả nghiệp vụ: [`Document.md`](Document.md).
- Danh mục endpoint: [`API_DOCUMENTATION.md`](API_DOCUMENTATION.md).
- Quy chuẩn kiến trúc: [`resources/docs/ARCHITECTURE.md`](resources/docs/ARCHITECTURE.md).
- Quy chuẩn frontend: [`resources/docs/FRONTEND_COMPONENTS.md`](resources/docs/FRONTEND_COMPONENTS.md).
- Quy tắc bảo mật: [`resources/docs/SECURITY.md`](resources/docs/SECURITY.md).

## Quy tắc cập nhật mục lục

Khi thêm module hoặc chuyển vị trí code, cập nhật trang này trong cùng pull request. Chỉ ghi thành phần thực sự tồn tại và ưu tiên link tương đối có thể mở trực tiếp.

<details>
<summary><strong>Mẫu thêm module mới</strong></summary>

```md
## N. Tên module

**Vai trò:** mô tả ngắn mục đích nghiệp vụ.

- **Điểm vào:** URL; web route; API prefix.
- **Frontend:** page và component chính.
- **Backend:** Controller, Service, Repository hoặc middleware liên quan.
- **Dữ liệu:** Model và migration chính.
- **Kiểm thử:** Feature/Unit test quan trọng.
```

</details>
