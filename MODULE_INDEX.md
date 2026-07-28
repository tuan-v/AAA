# MỤC LỤC MODULE DỰ ÁN ERP

> Trang bắt đầu dành cho thành viên mới. Mục tiêu: nhìn tên module là biết ngay nghiệp vụ, URL, frontend, backend, dữ liệu và test nằm ở đâu.
>
> Cập nhật theo mã nguồn ngày 28/07/2026. Khi tài liệu khác code, ưu tiên `routes/web.php`, `routes/api.php` và implementation thực tế.

## Cách sử dụng mục lục này

Một chức năng trong dự án thường đi theo đường dẫn:

```text
URL trình duyệt
→ routes/web.php
→ resources/js/Pages/... (Vue/Inertia)
→ Axios gọi /api/...
→ routes/api.php
→ Controller
→ Form Request (nếu có)
→ Service/Repository/Model
→ Database
→ tests/
```

Khi nhận một ticket, hãy mở mục module tương ứng bên dưới và đi theo thứ tự **FE → API route → Controller → Service → Model → Test**.

## Mục lục nhanh toàn dự án

| Chương | Module | URL màn hình | Frontend chính | Backend chính |
|---|---|---|---|---|
| 1 | Khung ứng dụng và Dashboard | `/`, `/dashboard` | `resources/js/Pages/DashBoard.vue`, layout/components | `DashboardController`, `DashboardService`, `DashboardRepository` |
| 2 | Công ty và hồ sơ | `/company/*`, `/profile` | `Pages/Company`, `Pages/Profile` | `CompanyController`, `ProfileController` |
| 3 | Nhân sự và tổ chức | `/user`, `/departments`, `/positions` | `Pages/Manage` | `API/UserController`, `DepartmentController`, `PositionController` |
| 4 | Vai trò và phân quyền | `/role`, `/permission` | `Pages/Manage/Role*`, `Permission*` | `RoleController`, `PermissionController` |
| 5 | Mua hàng | `/purchase/*` | `Pages/Purchase` | `PurchaseOrderController`, `SupplierController`, `PurchaseOrderService` |
| 6 | Bán hàng | `/sale/*` | `Pages/Sale` | `SalesOrderController`, `CustomerController` |
| 7 | Kho | `/warehouse/*` | `Pages/Warehouse` | `Warehouse*Controller`, `StockService`, `InventoryMovementService` |
| 8 | Kế toán và công nợ | `/accountant/*` | `Pages/Accountant` | Controller kế toán, `TransactionService`, `LedgerService`, Debt services |
| 9 | Nhật ký hoạt động | `/audit-logs` | `Pages/AuditLog` | `AuditLogController`, `ActivityLogService`, middleware audit |
| 10 | Thông báo và realtime | menu thông báo trên header | `NotificationMenu.vue`, `resources/js/realtime` | `NotificationController`, events, `NotificationService` |
| 11 | Xác thực | `/login`, `/register`, `/forgot-password` | Blade/Auth flow | `Controllers/Auth`, `routes/auth.php` |
| 12 | Thành phần dùng chung | dùng trong mọi màn hình | `components`, `composables`, `config`, `store` | Middleware, Traits, Helpers, Providers |

---

## 1. Khung ứng dụng và Dashboard

**Vai trò:** trang vào hệ thống, dashboard tổng hợp và khung giao diện dùng chung.

- Web route: `routes/web.php` — nhóm `Account, company onboarding and application entry flow`.
- API route: `routes/api.php` — nhóm `DASHBOARD & CURRENT USER`.
- FE trang: `resources/js/Pages/DashBoard.vue` và dashboard riêng tại `Pages/Purchase`, `Pages/Sale`, `Pages/Warehouse`, `Pages/Accountant`.
- FE khung: `resources/js/Layouts/AdminLayout.vue`, `resources/js/components/layout/`.
- BE: `app/Http/Controllers/DashboardController.php`.
- Service: `app/Services/DashboardService.php`.
- Repository: `app/Repositories/DashboardRepository.php` và interface tương ứng.
- Test: `tests/Feature/ModuleDashboardTest.php`, `tests/Feature/DemoAccountPageSmokeTest.php`.

## 2. Công ty và hồ sơ cá nhân

**Vai trò:** tạo công ty, thiết lập ngữ cảnh công ty và quản lý hồ sơ người dùng hiện tại.

- URL: `/company/create`, `/company`, `/profile`.
- Web route: `routes/web.php` — nhóm `Account, company onboarding and application entry flow`.
- FE: `resources/js/Pages/Company/Create.vue`, `resources/js/Pages/Profile/Edit.vue`.
- BE: `CompanyController`, `ProfileController`.
- Model: `Company`, `User`, `CompanyCurrencyRate`.
- Middleware: `EnsureCompanyCreated`, `HandleInertiaRequests`.
- Trait cô lập dữ liệu: `app/Traits/BelongsToCompany.php`.
- Migration: các file chứa `companies`, `company_id`, `company_currency`.
- Test: `ProfileTest`, `CompanyCurrencyServiceTest`, `OpeningBalanceCurrencySnapshotTest`.

## 3. Nhân sự và cơ cấu tổ chức

**Vai trò:** nhân viên, duyệt/từ chối tài khoản, phòng ban, chức vụ và quan hệ quản lý.

- URL: `/user`, `/user/{id}`, `/departments`, `/positions`.
- Web route: `routes/web.php` — nhóm `Administration flow`.
- API prefix: `/api/users`, `/api/departments`, `/api/positions`.
- FE nhân sự: `resources/js/Pages/Manage/User.vue`, `UserForm.vue`, `UserDetail.vue`.
- FE tổ chức: `resources/js/Pages/Manage/Department`, `resources/js/Pages/Manage/Position`.
- BE: `app/Http/Controllers/API/UserController.php`, `DepartmentController`, `PositionController`.
- Model: `User`, `Department`, `Position`, `Company`.
- Migration: các file chứa `users`, `departments`, `positions`.
- Seeder: `DepartmentDemoSeeder`, `PositionDemoSeeder`, `DepartmentEmployeeDemoSeeder`.
- Test: `DepartmentPositionFlowTest`, `DepartmentManagerAssignmentTest`, `UserListVisibilityTest`, `UserActivityLogTest`.

## 4. Vai trò và phân quyền

**Vai trò:** định nghĩa vai trò, permission và giới hạn hành động của từng tài khoản.

- URL: `/role`, `/permission`.
- API prefix: `/api/roles`, `/api/permissions`.
- FE: `resources/js/Pages/Manage/Role.vue`, `RoleForm.vue`, `Permission.vue`, `PermissionForm.vue`.
- BE: `RoleController`, `PermissionController`.
- Model: `Role`, `Permission`, `User`.
- Middleware: Spatie `permission`, `role`, `role_or_permission` được khai báo tại `bootstrap/app.php`.
- FE kiểm tra quyền: `resources/js/composables/usePermission.js`.
- Migration: `create_permission_tables`, các migration về group/hierarchy của permission và role.
- Seeder: `RoleSeeder`, `PermissionSeeder`, `RolePermissionSeeder`, `UserRoleSeeder`.
- Test: `PermissionListTest`, `DemoModuleRolesTest`, `DemoAccountPageSmokeTest`.

## 5. Mua hàng

**Vai trò:** nhà cung cấp, danh mục/sản phẩm mua, đơn mua, duyệt đơn và chuyển sang nhập kho/công nợ.

- URL: `/purchase/*`.
- Web route: `routes/web.php` — nhóm `Purchase flow`.
- API route: `routes/api.php` — nhóm `PURCHASE MODULE`.
- FE: `resources/js/Pages/Purchase/`.
  - `Supplier/`: nhà cung cấp.
  - `Product/`, `Category/`, `Unit/`: danh mục hàng mua.
  - `Order/`: danh sách, form và chi tiết đơn mua.
- BE: `PurchaseOrderController`, `SupplierController`, `ProductController`, `CategoryController`, `UnitController`.
- Service: `PurchaseOrderService`, `OrderQuantityValidationService`, `SupplierDebtService`, `CodeGeneratorService`.
- Repository: `SupplierDebtRepository` và interface.
- Model: `Supplier`, `PurchaseOrder`, `PurchaseOrderItem`, `SupplierDebt`, `Product`, `Category`, `Unit`.
- Migration: các file chứa `suppliers`, `purchase_orders`, `purchase_order_items`.
- Test chính: `PurchaseToPaymentEndToEndTest`, `DebtFlowEndToEndTest`, `ProductAvailabilityTest`.

> `ProductController`, `CategoryController` và `UnitController` được dùng chung với Kho. Hãy xác định ngữ cảnh bằng prefix route `/api/purchase` hoặc `/api/warehouse`.

## 6. Bán hàng

**Vai trò:** khách hàng, đơn bán, duyệt đơn, xuất kho, doanh thu và công nợ khách hàng.

- URL: `/sale/*`.
- Web route: `routes/web.php` — nhóm `Sale flow`.
- API route: `routes/api.php` — nhóm `SALE MODULE`.
- FE: `resources/js/Pages/Sale/Customer`, `resources/js/Pages/Sale/Order`, `resources/js/Pages/Sale/Dashboard.vue`.
- BE: `SalesOrderController`, `CustomerController`.
- Service liên quan: `CustomerDebtService`, `OrderQuantityValidationService`, `StockService`, `CodeGeneratorService`.
- Model: `Customer`, `CustomerDebt`, `CustomerPayment`, `SalesOrder`, `SalesOrderItem`.
- Migration: các file chứa `customers`, `customer_debts`, `customer_payments`, `sales_orders`, `sales_order_items`.
- Test: `InventoryLifecycleEndToEndTest`, `InventoryAccountingFlowTest`, `DebtSummaryTest`, `ProductAvailabilityTest`.

## 7. Kho

**Vai trò:** kho, sản phẩm tồn, phiếu nhập/xuất, chuyển kho và sổ biến động tồn.

- URL: `/warehouse/*`.
- Web route: `routes/web.php` — nhóm `Warehouse flow`.
- API route: `routes/api.php` — nhóm `WAREHOUSES & SLIPS`.
- FE: `resources/js/Pages/Warehouse/`.
  - `Product/`, `Category/`, `Unit/`: hàng hóa và danh mục.
  - `Order/`: đơn chờ xử lý kho.
  - `Slip/`: phiếu nhập/xuất.
  - `Transfer/`: chuyển kho.
  - `InventoryMovement/`: sổ biến động tồn.
- BE: `WarehouseController`, `WarehouseSlipController`, `WarehouseInventoryController`, `InventoryMovementController`, `WarehouseTransferController`.
- Service: `StockService`, `InventoryMovementService`, `OrderQuantityValidationService`.
- Model: `Warehouse`, `WarehouseProductStock`, `Stock`, `WarehouseSlip`, `WarehouseSlipItem`, `WarehouseTransfer`, `WarehouseTransferItem`, `InventoryMovement`.
- Migration: các file chứa `warehouses`, `warehouse_slips`, `warehouse_product_stocks`, `warehouse_transfers`, `inventory_movements`.
- Test: `InventoryLifecycleEndToEndTest`, `InventoryAccountingFlowTest`, `WarehouseFilterTest`, `ProductAvailabilityTest`.

## 8. Kế toán, giao dịch và công nợ

**Vai trò:** tiền tệ, ngân hàng, tài khoản/quỹ, giao dịch, sổ tài khoản, công nợ và báo cáo lãi lỗ.

- URL: `/accountant/*`.
- Web route: `routes/web.php` — nhóm `Accounting flow`.
- API route: `routes/api.php` — nhóm `ACCOUNTANT MODULE`.
- FE: `resources/js/Pages/Accountant/`.
  - `Currency/`, `Bank/`, `Account/`, `TransactionCategory/`: danh mục kế toán.
  - `Transaction/`, `AccountLedger/`: giao dịch và sổ tài khoản.
  - `Customer/`, `Supplier/`: công nợ.
  - `Report/`: báo cáo lãi lỗ.
- BE: `CurrencyController`, `BankController`, `AccountController`, `TransactionCategoryController`, `TransactionController`, `Accountant/AccountLedgerController`, `Accountant/ProfitLossReportController`.
- Service: `CurrencyService`, `CompanyCurrencyService`, `AccountBalanceService`, `TransactionCategoryService`, `TransactionService`, `LedgerService`, `CustomerDebtService`, `SupplierDebtService`.
- Repository: `AccountRepository`, `TransactionRepository`, `TransactionCategoryRepository`, `SupplierDebtRepository` và các interface.
- Model: `Currency`, `CurrencyRate`, `CompanyCurrencyRate`, `Bank`, `Account`, `AccountLedger`, `Transaction`, `TransactionCategory`, `CustomerDebt`, `SupplierDebt`.
- Migration: các file chứa `currencies`, `rates`, `banks`, `accounts`, `transactions`, `ledgers`, `debts`.
- Test: `TransactionFlowTest`, `DebtSummaryTest`, `DebtFlowEndToEndTest`, `OpeningBalanceCurrencySnapshotTest`, `TransactionCategoryCompanyIsolationTest`, `InventoryAccountingFlowTest`.

## 9. Nhật ký hoạt động

**Vai trò:** lưu vết ai đã làm gì, trên dữ liệu nào và trong công ty nào.

- URL: `/audit-logs`.
- API prefix: `/api/audit-logs`.
- FE: `resources/js/Pages/AuditLog/Index.vue`, `AuditLogDetail.vue`.
- BE: `AuditLogController`, `ActivityLogService`.
- Middleware: `LogPermissionAction`, `LogUserActivity`.
- Model: `ActivityLog`.
- Migration: các file chứa `activity_logs`.
- Test: `AuditLogFeatureTest`, `UserActivityLogTest`.

## 10. Thông báo và realtime

**Vai trò:** thông báo theo người dùng/module và tự làm mới dữ liệu khi công ty có thay đổi.

- API prefix: `/api/notifications`.
- FE: `resources/js/components/layout/header/NotificationMenu.vue`.
- FE realtime: `resources/js/echo.js`, `resources/js/realtime/companyData.js`, `resources/js/composables/useRealtimeRefresh.js`.
- BE: `NotificationController`, `NotificationService`, `BroadcastController`.
- Event: `NotificationCreated`, `CompanyDataChanged`.
- Middleware: `BroadcastCompanyDataChanges`.
- Kênh WebSocket: `routes/channels.php`.
- Model: `Notification`.
- Test: `NotificationFeatureTest`, `NotificationRecipientsTest`, `NotificationCreatedTest`, `CompanyDataChangedTest`, `BroadcastCompanyDataChangesMiddlewareTest`.

## 11. Xác thực

**Vai trò:** đăng nhập, đăng ký, đăng xuất, đặt lại mật khẩu, xác minh email và Google OAuth.

- Route: `routes/auth.php`; được nạp ở cuối `routes/web.php`.
- FE shell/view: `resources/views/`.
- BE: `app/Http/Controllers/Auth/`.
- Model: `User`, `PasswordResetToken`, `Session`.
- Cấu hình: `config/auth.php`, `config/services.php`, `.env.example`.
- Test: toàn bộ `tests/Feature/Auth/`.

## 12. Thành phần dùng chung và hạ tầng

| Phần | Vị trí | Khi nào cần mở |
|---|---|---|
| Entry Vue/Inertia | `resources/js/app.js` | Khởi tạo app, plugin, resolve page |
| Axios/CSRF | `resources/js/bootstrap.js` | Request FE, cookie và CSRF |
| Reverb/Echo | `resources/js/echo.js` | WebSocket/realtime |
| Layout/menu/header | `resources/js/Layouts`, `resources/js/components/layout` | Menu, header, khung trang |
| Component dùng chung | `resources/js/components` | Button, modal, bảng, input dùng lại |
| Composable | `resources/js/composables` | Permission, validation, confirm, currency, realtime |
| Middleware | `app/Http/Middleware` | Auth context, company, audit, broadcast |
| Provider | `app/Providers` | Binding repository và khởi tạo dịch vụ |
| Trait | `app/Traits` | Logic dùng lại, đặc biệt cô lập company |
| Helper | `app/Helpers` | Tiện ích dùng chung backend |
| Cấu hình | `config/`, `.env.example` | DB, queue, mail, filesystem, Reverb |
| Database | `database/migrations`, `seeders`, `factories` | Schema, dữ liệu nền và dữ liệu test |
| Scheduler | `routes/console.php` | Tác vụ định kỳ |
| Build | `vite.config.js`, `package.json` | Bundle frontend |
| Test | `tests/Feature`, `tests/Unit` | Hành vi mong đợi và regression |

## Tra cứu nhanh khi sửa code

| Muốn sửa | Mở đầu tiên |
|---|---|
| Chữ, màu, icon, vị trí button | Vue page trong `resources/js/Pages`, sau đó component được import |
| Button ẩn/hiện hoặc bị disable | `v-if`, `v-show`, `disabled`, `usePermission.js` |
| Hành động khi bấm button | Hàm `@click`, rồi tìm Axios endpoint |
| URL màn hình | `routes/web.php` |
| API và permission | `routes/api.php` |
| Validate dữ liệu | `app/Http/Requests` hoặc validation trong Controller |
| Quy tắc nghiệp vụ/trạng thái | Service, sau đó Controller nếu module cũ chưa tách Service |
| Truy vấn dữ liệu | Repository hoặc Eloquent Model/Controller |
| Cấu trúc bảng | `database/migrations` và Model |
| Response JSON | `app/Http/Resources` hoặc Controller |
| Lỗi 403 | Permission middleware, role/permission seeder và quyền user |
| Lỗi 422 | Form Request/validation |
| Lỗi 500 | `storage/logs/laravel.log`, Controller và Service |

## Lệnh tìm kiếm dành cho người mới

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

## Tài liệu đọc sâu

- Tổng quan kiến trúc và toàn bộ luồng: [`PROJECT_INDEX.md`](PROJECT_INDEX.md).
- Đặc tả nghiệp vụ: [`Document.md`](Document.md).
- Danh mục endpoint: [`API_DOCUMENTATION.md`](API_DOCUMENTATION.md).
- Quy chuẩn kiến trúc: [`resources/docs/ARCHITECTURE.md`](resources/docs/ARCHITECTURE.md).
- Quy chuẩn frontend: [`resources/docs/FRONTEND_COMPONENTS.md`](resources/docs/FRONTEND_COMPONENTS.md).
- Quy tắc bảo mật: [`resources/docs/SECURITY.md`](resources/docs/SECURITY.md).

## Quy tắc cập nhật mục lục

Khi thêm module hoặc chuyển vị trí code, người thực hiện phải cập nhật trang này trong cùng pull request. Tối thiểu cần cập nhật: URL, FE page, API prefix, Controller, Service, Model và test chính.
