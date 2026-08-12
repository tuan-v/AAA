# Chỉ mục theo phân hệ, nghiệp vụ và trang

> Sinh tự động ngày **10/08/2026** bằng `php docs/generate_project_function_index.php`. Trang này là mục lục tra cứu nhanh; chi tiết được tách sang [Function](PROJECT_FUNCTION_INDEX.md), [tìm lỗi](PROJECT_DEBUGGING_INDEX.md), [database](PROJECT_DATABASE_INDEX.md) và [luồng nghiệp vụ](../resources/docs/BUSINESS_FLOWS.md).

## Mục lục theo chức năng/nghiệp vụ

> Dùng mục này khi chỉ nhớ việc cần làm, ví dụ “Tạo giao dịch”, “Duyệt đơn bán” hoặc “Tạo phiếu xuất”, nhưng không nhớ tên trang hay file.


### Nền tảng và quản trị

- **Bật/tắt trạng thái người dùng/nhân sự** → `PATCH /api/users/{user}/status` → [API\UserController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:363) → Trang/Component: [Trang Người dùng — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/User.vue:457)
- **Lấy danh sách lựa chọn chức vụ** → `GET/HEAD /api/positions/all` → [PositionController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/PositionController.php:35) → Trang/Component: [Trang Biểu mẫu người dùng — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:602)
- **Lấy danh sách lựa chọn phòng ban** → `GET/HEAD /api/departments/all` → [DepartmentController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:48) → Trang/Component: [Trang Chức vụ — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Position/Index.vue:332), [Trang Người dùng — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/User.vue:481), [Trang Biểu mẫu người dùng — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:592)
- **Lấy danh sách quyền vai trò** → `GET/HEAD /api/permissions/all` → [RoleController::permissions()](vscode://file/D:/clone/project-base/app/Http/Controllers/RoleController.php:66) → Trang/Component: [Trang Biểu mẫu vai trò — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/RoleForm.vue:288)
- **Lấy danh sách quản lý phòng ban** → `GET/HEAD /api/departments/managers` → [DepartmentController::managers()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:56) → Trang/Component: [Trang Phòng ban — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Department/Index.vue:335)
- **Lấy vai trò người dùng/nhân sự** → `GET/HEAD /api/users/roles` → [API\UserController::role()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:81) → Trang/Component: [Trang Người dùng — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/User.vue:480), [Trang Biểu mẫu người dùng — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:580)
- **Mở trang chỉnh sửa hồ sơ người dùng** → `GET/HEAD /profile` → [ProfileController::edit()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProfileController.php:18) → Trang/Component: [Trang Chỉnh sửa hồ sơ](vscode://file/D:/clone/project-base/resources/js/Pages/Profile/Edit.vue:1)
- **Mở trang tạo công ty** → `GET/HEAD /api/company/create` → [CompanyController::create()](vscode://file/D:/clone/project-base/app/Http/Controllers/CompanyController.php:18) → Trang/Component: [Trang Tạo công ty](vscode://file/D:/clone/project-base/resources/js/Pages/Company/Create.vue:1)
- **Mở trang tạo công ty** → `GET/HEAD /company/create` → [CompanyController::create()](vscode://file/D:/clone/project-base/app/Http/Controllers/CompanyController.php:18) → Trang/Component: [Trang Tạo công ty](vscode://file/D:/clone/project-base/resources/js/Pages/Company/Create.vue:1)
- **Sửa chức vụ** → `PUT /api/positions/{position}` → [PositionController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/PositionController.php:63) → Trang/Component: [Trang Chức vụ — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Position/Index.vue:298)
- **Sửa hồ sơ người dùng** → `PATCH /profile` → [ProfileController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProfileController.php:28) → Trang/Component: [Trang Chỉnh sửa hồ sơ](vscode://file/D:/clone/project-base/resources/js/Pages/Profile/Edit.vue:29)
- **Sửa người dùng/nhân sự** → `PUT /api/users/user/{id}` → [API\UserController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:268) → Trang/Component: [Trang Biểu mẫu người dùng — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:685)
- **Sửa phòng ban** → `PUT /api/departments/{department}` → [DepartmentController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:109) → Trang/Component: [Trang Phòng ban — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Department/Index.vue:301)
- **Sửa quyền** → `PUT /api/permissions/{id}` → [PermissionController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/PermissionController.php:66) → Trang/Component: [Trang Biểu mẫu quyền — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/PermissionForm.vue:109)
- **Sửa vai trò** → `PUT /api/roles/{id}` → [RoleController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/RoleController.php:112) → Trang/Component: [Trang Biểu mẫu vai trò — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/RoleForm.vue:305)
- **Tạo chức vụ** → `POST /api/positions` → [PositionController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/PositionController.php:43) → Trang/Component: [Trang Chức vụ — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Position/Index.vue:299), [Trang Biểu mẫu người dùng — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:660)
- **Tạo công ty** → `POST /company` → [CompanyController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CompanyController.php:33) → Trang/Component: [Trang Tạo công ty](vscode://file/D:/clone/project-base/resources/js/Pages/Company/Create.vue:258)
- **Tạo người dùng/nhân sự** → `POST /api/users/user` → [API\UserController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:165) → Trang/Component: [Trang Biểu mẫu người dùng — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:687)
- **Tạo phòng ban** → `POST /api/departments` → [DepartmentController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:80) → Trang/Component: [Trang Phòng ban — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Department/Index.vue:302), [Trang Biểu mẫu người dùng — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:640)
- **Tạo quyền** → `POST /api/permissions` → [PermissionController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/PermissionController.php:35) → Trang/Component: [Trang Biểu mẫu quyền — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/PermissionForm.vue:111)
- **Tạo vai trò** → `POST /api/roles` → [RoleController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/RoleController.php:71) → Trang/Component: [Trang Biểu mẫu vai trò — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/RoleForm.vue:307)
- **Xem chi tiết người dùng/nhân sự** → `GET/HEAD /api/users/user/{id}` → [API\UserController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:105) → Trang/Component: [Trang Chi tiết người dùng — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserDetail.vue:116)
- **Xem danh sách chức vụ** → `GET/HEAD /api/positions` → [PositionController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/PositionController.php:22) → Trang/Component: [Trang Chức vụ — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Position/Index.vue:260)
- **Xem danh sách người dùng/nhân sự** → `GET/HEAD /api/users/user` → [API\UserController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:22) → Trang/Component: [Trang Nhật ký hoạt động](vscode://file/D:/clone/project-base/resources/js/Pages/AuditLog/Index.vue:209), [Trang Người dùng — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/User.vue:424)
- **Xem danh sách phòng ban** → `GET/HEAD /api/departments` → [DepartmentController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:26) → Trang/Component: [Trang Phòng ban — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Department/Index.vue:263)
- **Xem danh sách quyền** → `GET/HEAD /api/permissions` → [PermissionController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/PermissionController.php:12) → Trang/Component: [Trang Quyền — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Permission.vue:170)
- **Xem danh sách vai trò** → `GET/HEAD /api/roles` → [RoleController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/RoleController.php:13) → Trang/Component: [Trang Vai trò — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Role.vue:322)
- **Xóa chức vụ** → `DELETE /api/positions/{position}` → [PositionController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/PositionController.php:70) → Trang/Component: [Trang Chức vụ — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Position/Index.vue:322)
- **Xóa hồ sơ người dùng** → `DELETE /profile` → [ProfileController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProfileController.php:44) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa phòng ban** → `DELETE /api/departments/{department}` → [DepartmentController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:120) → Trang/Component: [Trang Phòng ban — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Department/Index.vue:325)
- **Xóa quyền** → `DELETE /api/permissions/{id}` → [PermissionController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/PermissionController.php:89) → Trang/Component: [Trang Quyền — Quản trị](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Permission.vue:145)
- **Xóa vai trò** → `DELETE /api/roles/{id}` → [RoleController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/RoleController.php:169) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp

### Mua hàng

- **Bật/tắt trạng thái danh mục sản phẩm** → `PATCH /api/purchase/categories/{id}/status` → [CategoryController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:215) → Trang/Component: [Trang Danh mục — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Category/Index.vue:244)
- **Bật/tắt trạng thái danh mục sản phẩm** → `PATCH /api/warehouse/categories/{id}/status` → [CategoryController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:215) → Trang/Component: [Trang Danh mục — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Category/Index.vue:239)
- **Bật/tắt trạng thái nhà cung cấp** → `PATCH /api/purchase/suppliers/{id}/status` → [SupplierController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:431) → Trang/Component: [Trang Nhà cung cấp — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/Index.vue:363)
- **Bật/tắt trạng thái sản phẩm** → `PATCH /api/purchase/products/{id}/status` → [ProductController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:445) → Trang/Component: [Trang Sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/Index.vue:391)
- **Bật/tắt trạng thái sản phẩm** → `PATCH /api/warehouse/products/{id}/status` → [ProductController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:445) → Trang/Component: [Trang Sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:390)
- **Bật/tắt trạng thái đơn vị tính** → `PATCH /api/purchase/units/{id}/status` → [UnitController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:148) → Trang/Component: [Trang Đơn vị tính — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Unit/Index.vue:227)
- **Bật/tắt trạng thái đơn vị tính** → `PATCH /api/units/{id}/status` → [UnitController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:148) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Bật/tắt trạng thái đơn vị tính** → `PATCH /api/warehouse/units/{id}/status` → [UnitController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:148) → Trang/Component: [Trang Đơn vị tính — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Unit/Index.vue:227)
- **Chuẩn bị tạo phiếu nhập đơn mua** → `GET/HEAD /api/purchase/orders/{id}/stock-in-data` → [PurchaseOrderController::stockInData()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:647) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Chuẩn bị tạo phiếu nhập đơn mua** → `GET/HEAD /api/warehouse/orders/{id}/stock-in` → [PurchaseOrderController::stockInData()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:647) → Trang/Component: [Trang tạo phiếu nhập — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:379)
- **Duyệt đơn mua** → `POST /api/purchase/orders/{id}/approve` → [PurchaseOrderController::approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:524) → Trang/Component: [Trang Đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:438)
- **Hủy đơn mua** → `POST /api/purchase/orders/{id}/cancel` → [PurchaseOrderController::cancel()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:576) → Trang/Component: [Trang Đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:503)
- **Lấy danh sách lựa chọn danh mục sản phẩm** → `GET/HEAD /api/purchase/categories/select` → [CategoryController::select()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:82) → Trang/Component: [Trang Biểu mẫu sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:495)
- **Lấy danh sách lựa chọn danh mục sản phẩm** → `GET/HEAD /api/warehouse/categories/select` → [CategoryController::select()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:82) → Trang/Component: [Trang Biểu mẫu sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:499)
- **Lấy danh sách lựa chọn nhà cung cấp** → `GET/HEAD /api/purchase/suppliers/all` → [SupplierController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:100) → Trang/Component: [Trang Giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:472), [Trang Đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:471), [Trang Nhà cung cấp — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/Index.vue:296)
- **Lấy danh sách lựa chọn sản phẩm** → `GET/HEAD /api/products/for-select` → [ProductController::forSelect()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:174) → Trang/Component: [Trang Biểu mẫu đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/PurchaseOrderForm.vue:493), [Trang Khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:391), [Trang Đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:447), [Form tạo/sửa đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:950)
- **Lấy danh sách lựa chọn đơn vị tính** → `GET/HEAD /api/purchase/units/select` → [UnitController::select()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:35) → Trang/Component: [Trang Biểu mẫu sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:496)
- **Lấy danh sách lựa chọn đơn vị tính** → `GET/HEAD /api/warehouse/units/select` → [UnitController::select()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:35) → Trang/Component: [Trang Biểu mẫu sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:500)
- **Sửa danh mục sản phẩm** → `PUT /api/purchase/categories/{category}` → [CategoryController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:132) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Sửa danh mục sản phẩm** → `PUT /api/warehouse/categories/{category}` → [CategoryController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:132) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Sửa nhà cung cấp** → `PUT /api/purchase/suppliers/{supplier}` → [SupplierController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:345) → Trang/Component: [Trang Biểu mẫu nhà cung cấp — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/SupplierForm.vue:516)
- **Sửa sản phẩm** → `PUT /api/purchase/products/{product}` → [ProductController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:318) → Trang/Component: [Trang Biểu mẫu sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:460)
- **Sửa sản phẩm** → `PUT /api/warehouse/products/{product}` → [ProductController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:318) → Trang/Component: [Trang Biểu mẫu sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:471)
- **Sửa đơn mua** → `PUT /api/purchase/orders/{order}` → [PurchaseOrderController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:382) → Trang/Component: [Trang Biểu mẫu đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/PurchaseOrderForm.vue:758)
- **Sửa đơn vị tính** → `PUT /api/purchase/units/{unit}` → [UnitController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:88) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Sửa đơn vị tính** → `PUT /api/warehouse/units/{unit}` → [UnitController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:88) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo danh mục sản phẩm** → `POST /api/purchase/categories` → [CategoryController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:37) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo danh mục sản phẩm** → `POST /api/warehouse/categories` → [CategoryController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:37) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo nhà cung cấp** → `POST /api/purchase/suppliers` → [SupplierController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:134) → Trang/Component: [Trang Biểu mẫu nhà cung cấp — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/SupplierForm.vue:522)
- **Tạo sản phẩm** → `POST /api/purchase/products` → [ProductController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:211) → Trang/Component: [Trang Biểu mẫu sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:464)
- **Tạo sản phẩm** → `POST /api/warehouse/products` → [ProductController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:211) → Trang/Component: [Trang Biểu mẫu sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:473)
- **Tạo đơn mua** → `POST /api/purchase/orders` → [PurchaseOrderController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:238) → Trang/Component: [Trang Biểu mẫu đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/PurchaseOrderForm.vue:760)
- **Tạo đơn vị tính** → `POST /api/purchase/units` → [UnitController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:48) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo đơn vị tính** → `POST /api/warehouse/units` → [UnitController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:48) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem chi tiết danh mục sản phẩm** → `GET/HEAD /api/purchase/categories/{category}` → [CategoryController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:118) → Trang/Component: [Trang Biểu mẫu sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:495)
- **Xem chi tiết danh mục sản phẩm** → `GET/HEAD /api/warehouse/categories/{category}` → [CategoryController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:118) → Trang/Component: [Trang Biểu mẫu sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:499)
- **Xem chi tiết nhà cung cấp** → `GET/HEAD /api/purchase/suppliers/{supplier}` → [SupplierController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:197) → Trang/Component: [Trang Giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:472), [Trang Đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:471), [Trang Nhà cung cấp — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/Index.vue:287)
- **Xem chi tiết sản phẩm** → `GET/HEAD /api/purchase/products/{product}` → [ProductController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:307) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem chi tiết sản phẩm** → `GET/HEAD /api/warehouse/products/{product}` → [ProductController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:307) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem chi tiết đơn mua** → `GET/HEAD /api/purchase/orders/{order}` → [PurchaseOrderController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:179) → Trang/Component: [Trang Đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:517), [Trang Chi tiết nhà cung cấp — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/SupplierDetail.vue:558), [Trang Đơn chờ kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Order/Index.vue:559)
- **Xem chi tiết đơn vị tính** → `GET/HEAD /api/purchase/units/{unit}` → [UnitController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:81) → Trang/Component: [Trang Biểu mẫu sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:496)
- **Xem chi tiết đơn vị tính** → `GET/HEAD /api/warehouse/units/{unit}` → [UnitController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:81) → Trang/Component: [Trang Biểu mẫu sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:500)
- **Xem danh sách chờ kho đơn mua** → `GET/HEAD /api/warehouse/orders` → [PurchaseOrderController::warehouseIndex()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:118) → Trang/Component: [Trang Đơn chờ kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Order/Index.vue:569)
- **Xem danh sách danh mục sản phẩm** → `GET/HEAD /api/categories` → [CategoryController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:11) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem danh sách danh mục sản phẩm** → `GET/HEAD /api/purchase/categories` → [CategoryController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:11) → Trang/Component: [Trang Danh mục — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Category/Index.vue:210)
- **Xem danh sách danh mục sản phẩm** → `GET/HEAD /api/warehouse/categories` → [CategoryController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:11) → Trang/Component: [Trang Danh mục — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Category/Index.vue:227), [Trang Danh mục — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Category/Index.vue:205)
- **Xem danh sách nhà cung cấp** → `GET/HEAD /api/accountant/suppliers-debt` → [SupplierController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:29) → Trang/Component: [Trang Nhà cung cấp — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Supplier/Index.vue:154)
- **Xem danh sách nhà cung cấp** → `GET/HEAD /api/purchase/suppliers` → [SupplierController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:29) → Trang/Component: [Trang Nhà cung cấp — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/Index.vue:335)
- **Xem danh sách sản phẩm** → `GET/HEAD /api/purchase/products` → [ProductController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:51) → Trang/Component: [Trang Đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:477), [Trang Sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/Index.vue:309)
- **Xem danh sách sản phẩm** → `GET/HEAD /api/warehouse/products` → [ProductController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:51) → Trang/Component: [Trang Sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:308), [Trang Chuyển kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:312)
- **Xem danh sách đơn mua** → `GET/HEAD /api/purchase/orders` → [PurchaseOrderController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:46) → Trang/Component: [Form giao dịch kế toán — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1222), [Trang Đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:454)
- **Xem danh sách đơn vị tính** → `GET/HEAD /api/purchase/units` → [UnitController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:19) → Trang/Component: [Trang Đơn vị tính — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Unit/Index.vue:154)
- **Xem danh sách đơn vị tính** → `GET/HEAD /api/units` → [UnitController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:19) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem danh sách đơn vị tính** → `GET/HEAD /api/warehouse/units` → [UnitController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:19) → Trang/Component: [Trang Đơn vị tính — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Unit/Index.vue:212), [Trang Đơn vị tính — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Unit/Index.vue:154)
- **Xem hồ sơ chi tiết nhà cung cấp** → `GET/HEAD /api/accountant/suppliers-debt/{id}/detail` → [SupplierController::detail()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:202) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem hồ sơ chi tiết nhà cung cấp** → `GET/HEAD /api/purchase/suppliers/{id}/detail` → [SupplierController::detail()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:202) → Trang/Component: [Trang Chi tiết nhà cung cấp — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/SupplierDetail.vue:572)
- **Xóa danh mục sản phẩm** → `DELETE /api/purchase/categories/{category}` → [CategoryController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:200) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa danh mục sản phẩm** → `DELETE /api/warehouse/categories/{category}` → [CategoryController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:200) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa nhà cung cấp** → `DELETE /api/purchase/suppliers/{supplier}` → [SupplierController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:416) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa sản phẩm** → `DELETE /api/purchase/products/{product}` → [ProductController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:427) → Trang/Component: [Trang Sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/Index.vue:380)
- **Xóa sản phẩm** → `DELETE /api/warehouse/products/{product}` → [ProductController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:427) → Trang/Component: [Trang Sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:379)
- **Xóa đơn mua** → `DELETE /api/purchase/orders/{order}` → [PurchaseOrderController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:624) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa đơn vị tính** → `DELETE /api/purchase/units/{unit}` → [UnitController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:133) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa đơn vị tính** → `DELETE /api/warehouse/units/{unit}` → [UnitController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:133) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp

### Bán hàng

- **Bật/tắt trạng thái khách hàng** → `PATCH /api/sale/customers/{customer}/status` → [CustomerController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:399) → Trang/Component: [Trang Khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:372)
- **Chuẩn bị tạo phiếu xuất đơn bán** → `GET/HEAD /api/warehouse/orders/{id}/stock-out` → [SalesOrderController::stockOutData()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:923) → Trang/Component: [Trang tạo phiếu xuất — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Salecreate.vue:327)
- **Duyệt đơn bán** → `POST /api/sale/orders/{id}/approve` → [SalesOrderController::approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:776) → Trang/Component: [Trang Đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:524)
- **Gửi duyệt đơn bán** → `POST /api/sale/orders/{id}/submit` → [SalesOrderController::submitForApproval()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:729) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Hủy đơn bán** → `POST /api/sale/orders/{id}/cancel` → [SalesOrderController::cancel()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:824) → Trang/Component: [Trang Đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:511)
- **Hủy đơn nháp đơn bán POS** → `DELETE /api/sale/pos/drafts/{order}` → [PosController::cancelDraft()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:189) → Trang/Component: [Trang bán hàng POS — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:414)
- **Kiểm tra số lượng có thể xuất đơn bán** → `GET/HEAD /api/available-for-export` → [SalesOrderController::availableForExport()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:245) → Trang/Component: [Trang tạo phiếu xuất — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Salecreate.vue:363)
- **Lấy danh sách lựa chọn khách hàng** → `GET/HEAD /api/sale/customers/all` → [CustomerController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:92) → Trang/Component: [Trang Giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:471), [Trang Biểu mẫu mã giảm giá — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/CouponForm.vue:104), [Trang Khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:390), [Trang Đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:442)
- **Lấy danh sách đang hoạt động mã giảm giá** → `GET/HEAD /api/sale/coupons/active` → [CouponController::active()](vscode://file/D:/clone/project-base/app/Http/Controllers/CouponController.php:52) → Trang/Component: [Form tạo/sửa đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:933)
- **Lấy dữ liệu lựa chọn đơn bán POS** → `GET/HEAD /api/sale/pos/options` → [PosController::options()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:39) → Trang/Component: [Trang bán hàng POS — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:486)
- **Sửa khách hàng** → `PUT /api/sale/customers/{customer}` → [CustomerController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:179) → Trang/Component: [Trang Biểu mẫu khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/CustomerForm.vue:497)
- **Sửa mã giảm giá** → `PUT /api/sale/coupons/{coupon}` → [CouponController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/CouponController.php:84) → Trang/Component: [Trang Biểu mẫu mã giảm giá — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/CouponForm.vue:109)
- **Sửa đơn bán** → `PUT /api/sale/orders/{order}` → [SalesOrderController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:562) → Trang/Component: [Form tạo/sửa đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:1000)
- **Sửa đơn nháp đơn bán POS** → `PUT /api/sale/pos/drafts/{order}` → [PosController::updateDraft()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:121) → Trang/Component: [Trang bán hàng POS — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:394)
- **Tạo khách hàng** → `POST /api/sale/customers` → [CustomerController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:120) → Trang/Component: [Trang Biểu mẫu khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/CustomerForm.vue:504)
- **Tạo mã giảm giá** → `POST /api/sale/coupons` → [CouponController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CouponController.php:68) → Trang/Component: [Trang Biểu mẫu mã giảm giá — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/CouponForm.vue:109)
- **Tạo nhanh khách hàng đơn bán POS** → `POST /api/sale/pos/customers` → [PosController::storeCustomer()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:96) → Trang/Component: [Trang bán hàng POS — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:436)
- **Tạo nhanh đơn hàng khách hàng** → `POST /api/sale/customers/{id}/quick-order` → [CustomerController::createQuickOrder()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:377) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo đơn bán POS** → `POST /api/sale/pos/orders` → [PosController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:202) → Trang/Component: [Trang bán hàng POS — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:456)
- **Tạo đơn bán** → `POST /api/sale/orders` → [SalesOrderController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:363) → Trang/Component: [Form tạo/sửa đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:1002)
- **Tạo đơn nháp đơn bán POS** → `POST /api/sale/pos/drafts` → [PosController::createDraft()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:80) → Trang/Component: [Trang bán hàng POS — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:350)
- **Xem chi tiết khách hàng** → `GET/HEAD /api/sale/customers/{customer}` → [CustomerController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:248) → Trang/Component: [Trang Giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:471), [Trang Biểu mẫu mã giảm giá — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/CouponForm.vue:104), [Trang Khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:319), [Trang Đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:442)
- **Xem chi tiết đơn bán POS** → `GET/HEAD /api/sale/pos/orders/{order}` → [PosController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:348) → Trang/Component: [Trang bán hàng POS — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:481)
- **Xem chi tiết đơn bán** → `GET/HEAD /api/sale/orders/{order}` → [SalesOrderController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:296) → Trang/Component: [Trang chi tiết khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/CustomerDetail.vue:552), [Trang Đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:414), [Trang Đơn chờ kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Order/Index.vue:412)
- **Xem danh sách chờ kho đơn bán** → `GET/HEAD /api/saleorders/warehouse` → [SalesOrderController::warehouseIndex()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:149) → Trang/Component: [Trang Đơn chờ kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Order/Index.vue:586)
- **Xem danh sách khách hàng** → `GET/HEAD /api/accountant/customers-debt` → [CustomerController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:15) → Trang/Component: [Trang Khách hàng — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Customer/Index.vue:158)
- **Xem danh sách khách hàng** → `GET/HEAD /api/sale/customers` → [CustomerController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:15) → Trang/Component: [Trang Khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:344)
- **Xem danh sách mã giảm giá** → `GET/HEAD /api/sale/coupons` → [CouponController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CouponController.php:18) → Trang/Component: [Trang Mã giảm giá — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/Index.vue:103)
- **Xem danh sách đơn bán** → `GET/HEAD /api/sale/orders` → [SalesOrderController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:40) → Trang/Component: [Form giao dịch kế toán — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1190), [Trang Đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:425)
- **Xem hồ sơ chi tiết khách hàng** → `GET/HEAD /api/accountant/customers-debt/{id}/detail` → [CustomerController::detail()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:282) → Trang/Component: [Trang Chi tiết khách hàng — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Customer/CustomerDetail.vue:141)
- **Xem hồ sơ chi tiết khách hàng** → `GET/HEAD /api/sale/customers/{id}/detail` → [CustomerController::detail()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:282) → Trang/Component: [Trang chi tiết khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/CustomerDetail.vue:573), [Form tạo/sửa đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:639)
- **Xem lịch sử sử dụng mã giảm giá** → `GET/HEAD /api/sale/coupons/{coupon}/usages` → [CouponController::usages()](vscode://file/D:/clone/project-base/app/Http/Controllers/CouponController.php:113) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem lịch sử đơn bán POS** → `GET/HEAD /api/sale/pos/history` → [PosController::history()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:339) → Trang/Component: [Trang bán hàng POS — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:480)
- **Xem đơn nháp đơn bán POS** → `GET/HEAD /api/sale/pos/drafts` → [PosController::drafts()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:25) → Trang/Component: [Trang bán hàng POS — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:488)
- **Xóa mã giảm giá** → `DELETE /api/sale/coupons/{coupon}` → [CouponController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/CouponController.php:102) → Trang/Component: [Trang Mã giảm giá — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/Index.vue:130)
- **Xóa đơn bán** → `DELETE /api/sale/orders/{order}` → [SalesOrderController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:903) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp

### Cửa hàng trực tuyến

- **Hủy đơn hàng tài khoản khách hàng cửa hàng trực tuyến** → `POST /shop/{company}/account/orders/{code}/cancel` → [StorefrontAccountController::cancelOrder()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:368) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:711), [Trang chi tiết đơn cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/OrderDetail.vue:190)
- **Lấy mã giảm giá khả dụng cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/vouchers` → [StorefrontController::vouchers()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:128) → Trang/Component: [Trang thanh toán cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:534)
- **Lấy thông tin tài khoản hiện tại tài khoản khách hàng cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/account/me` → [StorefrontAccountController::me()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:185) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:558), [Trang thanh toán cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:535)
- **Mở cửa hàng cửa hàng trực tuyến** → `GET/HEAD /shop/{company}` → [StorefrontController::shop()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:36) → Trang/Component: [Trang Cửa hàng — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Shop.vue:1)
- **Mở danh bạ cửa hàng cửa hàng trực tuyến** → `GET/HEAD /shop` → [StorefrontController::directory()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:27) → Trang/Component: [Trang Danh bạ cửa hàng — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Directory.vue:1)
- **Mở trang chi tiết đơn tài khoản khách hàng cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/my-account/orders/{code}` → [StorefrontAccountController::orderPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:27) → Trang/Component: [Trang chi tiết đơn cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/OrderDetail.vue:1)
- **Mở trang giỏ hàng cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/cart` → [StorefrontController::cartPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:58) → Trang/Component: [Trang Giỏ hàng — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Cart.vue:1)
- **Mở trang sản phẩm cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/product/{product}` → [StorefrontController::productPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:50) → Trang/Component: [Trang Sản phẩm — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Product.vue:1)
- **Mở trang thanh toán cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/checkout` → [StorefrontController::checkoutPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:65) → Trang/Component: [Trang thanh toán cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:1)
- **Mở trang thông báo tài khoản khách hàng cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/my-account/notifications` → [StorefrontAccountController::notificationPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:114) → Trang/Component: [Trang Thông báo — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Notifications.vue:1)
- **Mở trang tài khoản cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/my-account` → [StorefrontController::accountPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:43) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:1)
- **Mở trang đặt hàng thành công cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/order-success` → [StorefrontController::successPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:72) → Trang/Component: [Trang Đặt hàng thành công — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Success.vue:1)
- **Sửa hồ sơ tài khoản khách hàng cửa hàng trực tuyến** → `PUT /shop/{company}/account/profile` → [StorefrontAccountController::updateProfile()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:192) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:584)
- **Sửa địa chỉ tài khoản khách hàng cửa hàng trực tuyến** → `PUT /shop/{company}/account/addresses/{address}` → [StorefrontAccountController::updateAddress()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:458) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:670)
- **Tạo địa chỉ tài khoản khách hàng cửa hàng trực tuyến** → `POST /shop/{company}/account/addresses` → [StorefrontAccountController::storeAddress()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:405) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:672), [Trang thanh toán cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:433)
- **Xem chi tiết sản phẩm cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/products/{product}` → [StorefrontController::product()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:118) → Trang/Component: [Trang Sản phẩm — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Product.vue:152)
- **Xem danh sách sản phẩm cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/products` → [StorefrontController::products()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:79) → Trang/Component: [Trang Cửa hàng — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Shop.vue:407)
- **Xem danh sách thông báo tài khoản khách hàng cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/account/notifications` → [StorefrontAccountController::notifications()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:248) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem danh sách đơn tài khoản khách hàng cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/account/orders` → [StorefrontAccountController::orders()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:221) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:566)
- **Xem danh sách địa chỉ tài khoản khách hàng cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/account/addresses` → [StorefrontAccountController::addresses()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:400) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:567), [Trang thanh toán cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:544)
- **Xem lịch sử thông báo tài khoản khách hàng cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/account/notification-history` → [StorefrontAccountController::notificationHistory()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:266) → Trang/Component: [Trang Thông báo — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Notifications.vue:166)
- **Xóa thông báo tài khoản khách hàng cửa hàng trực tuyến** → `DELETE /shop/{company}/account/notifications/{notification}` → [StorefrontAccountController::destroyNotification()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:328) → Trang/Component: [Trang Thông báo — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Notifications.vue:213)
- **Xóa địa chỉ tài khoản khách hàng cửa hàng trực tuyến** → `DELETE /shop/{company}/account/addresses/{address}` → [StorefrontAccountController::destroyAddress()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:439) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:706)
- **Đánh dấu thông báo đã đọc tài khoản khách hàng cửa hàng trực tuyến** → `POST /shop/{company}/account/notifications/{notification}/read` → [StorefrontAccountController::markNotificationRead()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:307) → Trang/Component: [Trang Thông báo — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Notifications.vue:186)
- **Đánh dấu tất cả thông báo đã đọc tài khoản khách hàng cửa hàng trực tuyến** → `POST /shop/{company}/account/notifications/read-all` → [StorefrontAccountController::markAllNotificationsRead()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:316) → Trang/Component: [Trang Thông báo — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Notifications.vue:194)
- **Đăng ký tài khoản khách hàng cửa hàng trực tuyến** → `POST /shop/{company}/account/register` → [StorefrontAccountController::register()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:128) → Trang/Component: [Component CustomerAuthPanel](vscode://file/D:/clone/project-base/resources/js/components/Storefront/CustomerAuthPanel.vue:95), [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:482)
- **Đăng nhập tài khoản khách hàng cửa hàng trực tuyến** → `POST /shop/{company}/account/login` → [StorefrontAccountController::login()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:161) → Trang/Component: [Component CustomerAuthPanel](vscode://file/D:/clone/project-base/resources/js/components/Storefront/CustomerAuthPanel.vue:86), [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:482)
- **Đăng xuất tài khoản khách hàng cửa hàng trực tuyến** → `POST /shop/{company}/account/logout` → [StorefrontAccountController::logout()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:177) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:574)
- **Đặt hàng và thanh toán cửa hàng trực tuyến** → `POST /shop/{company}/checkout` → [StorefrontController::checkout()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:140) → Trang/Component: [Trang thanh toán cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:496)
- **Đếm thông báo chưa đọc tài khoản khách hàng cửa hàng trực tuyến** → `GET/HEAD /shop/{company}/account/notifications/unread-count` → [StorefrontAccountController::notificationUnreadCount()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:293) → Trang/Component: [Component NotificationBadgeLink](vscode://file/D:/clone/project-base/resources/js/components/Storefront/NotificationBadgeLink.vue:33), [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:488), [Trang Giỏ hàng — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Cart.vue:236), [Trang thanh toán cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:329), [Trang chi tiết đơn cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/OrderDetail.vue:153), [Trang Sản phẩm — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Product.vue:134), [Trang Cửa hàng — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Shop.vue:373)
- **Đổi mật khẩu tài khoản khách hàng cửa hàng trực tuyến** → `PUT /shop/{company}/account/password` → [StorefrontAccountController::updatePassword()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:204) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:594)

### Kho

- **Bật/tắt trạng thái danh mục sản phẩm** → `PATCH /api/purchase/categories/{id}/status` → [CategoryController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:215) → Trang/Component: [Trang Danh mục — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Category/Index.vue:244)
- **Bật/tắt trạng thái danh mục sản phẩm** → `PATCH /api/warehouse/categories/{id}/status` → [CategoryController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:215) → Trang/Component: [Trang Danh mục — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Category/Index.vue:239)
- **Bật/tắt trạng thái kho** → `PATCH /api/warehouse/{id}/status` → [WarehouseController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:231) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Bật/tắt trạng thái kho** → `PATCH /api/warehouses/{warehouse}/status` → [WarehouseController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:231) → Trang/Component: [Trang Kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Index.vue:281)
- **Bật/tắt trạng thái sản phẩm** → `PATCH /api/purchase/products/{id}/status` → [ProductController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:445) → Trang/Component: [Trang Sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/Index.vue:391)
- **Bật/tắt trạng thái sản phẩm** → `PATCH /api/warehouse/products/{id}/status` → [ProductController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:445) → Trang/Component: [Trang Sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:390)
- **Bật/tắt trạng thái đơn vị tính** → `PATCH /api/purchase/units/{id}/status` → [UnitController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:148) → Trang/Component: [Trang Đơn vị tính — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Unit/Index.vue:227)
- **Bật/tắt trạng thái đơn vị tính** → `PATCH /api/units/{id}/status` → [UnitController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:148) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Bật/tắt trạng thái đơn vị tính** → `PATCH /api/warehouse/units/{id}/status` → [UnitController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:148) → Trang/Component: [Trang Đơn vị tính — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Unit/Index.vue:227)
- **Duyệt phiếu chuyển kho** → `POST /api/warehouse/transfers/{id}/approve` → [WarehouseTransferController::approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseTransferController.php:102) → Trang/Component: [Trang Chuyển kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:347)
- **Duyệt phiếu nhập/xuất kho** → `POST /api/warehouse/slips/{id}/approve` → [WarehouseSlipController::approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:818) → Trang/Component: [Trang Phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:423), [Trang tạo phiếu nhập — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:264), [Trang tạo phiếu xuất — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Salecreate.vue:509)
- **Gán đơn vị vận chuyển phiếu nhập/xuất kho** → `PUT /api/warehouse/slips/{id}/shipping` → [WarehouseSlipController::assignShipping()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:600) → Trang/Component: [Trang Phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:673)
- **Hủy phiếu chuyển kho** → `POST /api/warehouse/transfers/{id}/cancel` → [WarehouseTransferController::cancel()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseTransferController.php:172) → Trang/Component: [Trang Chuyển kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:359)
- **Kiểm tra tồn kho kho** → `GET/HEAD /api/warehouse/stocks` → [WarehouseController::getStocks()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:262) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Kế toán duyệt hàng hoàn phiếu nhập/xuất kho** → `POST /api/warehouse/slips/{id}/accountant-approve-delivery-return` → [WarehouseSlipController::accountantApproveDeliveryReturn()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:720) → Trang/Component: [Trang Phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:598)
- **Kế toán duyệt phiếu nhập/xuất kho** → `POST /api/warehouse/slips/{id}/accountant-approve` → [WarehouseSlipController::accountantApprove()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:862) → Trang/Component: [Trang Phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:456)
- **Lấy danh sách lựa chọn danh mục sản phẩm** → `GET/HEAD /api/purchase/categories/select` → [CategoryController::select()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:82) → Trang/Component: [Trang Biểu mẫu sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:495)
- **Lấy danh sách lựa chọn danh mục sản phẩm** → `GET/HEAD /api/warehouse/categories/select` → [CategoryController::select()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:82) → Trang/Component: [Trang Biểu mẫu sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:499)
- **Lấy danh sách lựa chọn kho** → `GET/HEAD /api/warehouses/all` → [WarehouseController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:159) → Trang/Component: [Trang Sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/Index.vue:404), [Trang Biến động tồn kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/InventoryMovement/Index.vue:132), [Trang Sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:403), [Trang Phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:722), [Trang tạo phiếu nhập — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:405), [Trang Chuyển kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:366)
- **Lấy danh sách lựa chọn sản phẩm** → `GET/HEAD /api/products/for-select` → [ProductController::forSelect()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:174) → Trang/Component: [Trang Biểu mẫu đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/PurchaseOrderForm.vue:493), [Trang Khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:391), [Trang Đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:447), [Form tạo/sửa đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:950)
- **Lấy danh sách lựa chọn đơn vị tính** → `GET/HEAD /api/purchase/units/select` → [UnitController::select()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:35) → Trang/Component: [Trang Biểu mẫu sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:496)
- **Lấy danh sách lựa chọn đơn vị tính** → `GET/HEAD /api/warehouse/units/select` → [UnitController::select()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:35) → Trang/Component: [Trang Biểu mẫu sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:500)
- **Lấy danh sách đối tác vận chuyển phiếu nhập/xuất kho** → `GET/HEAD /api/warehouse/slips/shipping/partners` → [WarehouseSlipController::shippingPartners()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:579) → Trang/Component: [Trang Phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:389)
- **Nhận hàng hoàn phiếu nhập/xuất kho** → `POST /api/warehouse/slips/{id}/receive-delivery-return` → [WarehouseSlipController::receiveDeliveryReturn()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:700) → Trang/Component: [Trang Phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:566)
- **Sửa danh mục sản phẩm** → `PUT /api/purchase/categories/{category}` → [CategoryController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:132) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Sửa danh mục sản phẩm** → `PUT /api/warehouse/categories/{category}` → [CategoryController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:132) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Sửa kho** → `PUT /api/warehouses/{warehouse}` → [WarehouseController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:180) → Trang/Component: [Trang Biểu mẫu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/WarehouseForm.vue:211)
- **Sửa phiếu nhập/xuất kho** → `PUT /api/warehouse/slips/{slip}` → [WarehouseSlipController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:409) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Sửa sản phẩm** → `PUT /api/purchase/products/{product}` → [ProductController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:318) → Trang/Component: [Trang Biểu mẫu sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:460)
- **Sửa sản phẩm** → `PUT /api/warehouse/products/{product}` → [ProductController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:318) → Trang/Component: [Trang Biểu mẫu sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:471)
- **Sửa đơn vị tính** → `PUT /api/purchase/units/{unit}` → [UnitController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:88) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Sửa đơn vị tính** → `PUT /api/warehouse/units/{unit}` → [UnitController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:88) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo danh mục sản phẩm** → `POST /api/purchase/categories` → [CategoryController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:37) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo danh mục sản phẩm** → `POST /api/warehouse/categories` → [CategoryController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:37) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo kho** → `POST /api/warehouses` → [WarehouseController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:114) → Trang/Component: [Trang Biểu mẫu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/WarehouseForm.vue:215)
- **Tạo phiếu chuyển kho** → `POST /api/warehouse/transfers` → [WarehouseTransferController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseTransferController.php:39) → Trang/Component: [Trang Chuyển kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:326)
- **Tạo phiếu nhập/xuất kho** → `POST /api/warehouse/slips` → [WarehouseSlipController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:182) → Trang/Component: [Trang tạo phiếu nhập — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:456), [Trang tạo phiếu xuất — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Salecreate.vue:418)
- **Tạo sản phẩm** → `POST /api/purchase/products` → [ProductController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:211) → Trang/Component: [Trang Biểu mẫu sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:464)
- **Tạo sản phẩm** → `POST /api/warehouse/products` → [ProductController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:211) → Trang/Component: [Trang Biểu mẫu sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:473)
- **Tạo đơn vị tính** → `POST /api/purchase/units` → [UnitController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:48) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo đơn vị tính** → `POST /api/warehouse/units` → [UnitController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:48) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo đối tác vận chuyển phiếu nhập/xuất kho** → `POST /api/warehouse/slips/shipping/partners` → [WarehouseSlipController::storeShippingPartner()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:585) → Trang/Component: [Trang Phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:658)
- **Từ chối phiếu nhập/xuất kho** → `POST /api/warehouse/slips/{id}/reject` → [WarehouseSlipController::reject()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:1031) → Trang/Component: [Trang Phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:630), [Trang tạo phiếu nhập — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:296), [Trang tạo phiếu xuất — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Salecreate.vue:546)
- **Xem chi tiết danh mục sản phẩm** → `GET/HEAD /api/purchase/categories/{category}` → [CategoryController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:118) → Trang/Component: [Trang Biểu mẫu sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:495)
- **Xem chi tiết danh mục sản phẩm** → `GET/HEAD /api/warehouse/categories/{category}` → [CategoryController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:118) → Trang/Component: [Trang Biểu mẫu sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:499)
- **Xem chi tiết kho** → `GET/HEAD /api/warehouses/{warehouse}` → [WarehouseController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:171) → Trang/Component: [Trang Sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/Index.vue:404), [Trang Biến động tồn kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/InventoryMovement/Index.vue:132), [Trang Sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:403), [Trang Phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:722), [Trang tạo phiếu nhập — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:405), [Trang Chuyển kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:366)
- **Xem chi tiết phiếu nhập/xuất kho** → `GET/HEAD /api/warehouse/slips/{slip}` → [WarehouseSlipController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:114) → Trang/Component: [Trang Chi tiết phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/SlipDetail.vue:229)
- **Xem chi tiết sản phẩm** → `GET/HEAD /api/purchase/products/{product}` → [ProductController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:307) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem chi tiết sản phẩm** → `GET/HEAD /api/warehouse/products/{product}` → [ProductController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:307) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem chi tiết đơn vị tính** → `GET/HEAD /api/purchase/units/{unit}` → [UnitController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:81) → Trang/Component: [Trang Biểu mẫu sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:496)
- **Xem chi tiết đơn vị tính** → `GET/HEAD /api/warehouse/units/{unit}` → [UnitController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:81) → Trang/Component: [Trang Biểu mẫu sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:500)
- **Xem danh sách biến động tồn kho** → `GET/HEAD /api/warehouse/inventory-movements` → [InventoryMovementController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/InventoryMovementController.php:13) → Trang/Component: [Trang Biến động tồn kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/InventoryMovement/Index.vue:112)
- **Xem danh sách danh mục sản phẩm** → `GET/HEAD /api/categories` → [CategoryController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:11) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem danh sách danh mục sản phẩm** → `GET/HEAD /api/purchase/categories` → [CategoryController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:11) → Trang/Component: [Trang Danh mục — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Category/Index.vue:210)
- **Xem danh sách danh mục sản phẩm** → `GET/HEAD /api/warehouse/categories` → [CategoryController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:11) → Trang/Component: [Trang Danh mục — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Category/Index.vue:227), [Trang Danh mục — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Category/Index.vue:205)
- **Xem danh sách kho** → `GET/HEAD /api/warehouses` → [WarehouseController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:24) → Trang/Component: [Trang Kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Index.vue:238)
- **Xem danh sách phiếu chuyển kho** → `GET/HEAD /api/warehouse/transfers` → [WarehouseTransferController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseTransferController.php:31) → Trang/Component: [Trang Chuyển kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:285)
- **Xem danh sách phiếu nhập/xuất kho** → `GET/HEAD /api/warehouse/slips` → [WarehouseSlipController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:32) → Trang/Component: [Trang tạo phiếu nhập — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:396), [Trang tạo phiếu xuất — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Salecreate.vue:352)
- **Xem danh sách sản phẩm** → `GET/HEAD /api/purchase/products` → [ProductController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:51) → Trang/Component: [Trang Đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:477), [Trang Sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/Index.vue:309)
- **Xem danh sách sản phẩm** → `GET/HEAD /api/warehouse/products` → [ProductController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:51) → Trang/Component: [Trang Sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:308), [Trang Chuyển kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:312)
- **Xem danh sách đơn vị tính** → `GET/HEAD /api/purchase/units` → [UnitController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:19) → Trang/Component: [Trang Đơn vị tính — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Unit/Index.vue:154)
- **Xem danh sách đơn vị tính** → `GET/HEAD /api/units` → [UnitController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:19) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem danh sách đơn vị tính** → `GET/HEAD /api/warehouse/units` → [UnitController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:19) → Trang/Component: [Trang Đơn vị tính — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Unit/Index.vue:212), [Trang Đơn vị tính — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Unit/Index.vue:154)
- **Xem hồ sơ chi tiết kho** → `GET/HEAD /api/warehouses/{warehouse}/detail` → [WarehouseController::detail()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:283) → Trang/Component: [Trang Chi tiết kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/WarehouseDetail.vue:343)
- **Xác nhận giao hàng phiếu nhập/xuất kho** → `POST /api/warehouse/slips/{id}/confirm-delivery` → [WarehouseSlipController::confirmDelivery()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:531) → Trang/Component: [Trang Phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:491)
- **Xóa danh mục sản phẩm** → `DELETE /api/purchase/categories/{category}` → [CategoryController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:200) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa danh mục sản phẩm** → `DELETE /api/warehouse/categories/{category}` → [CategoryController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:200) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa kho** → `DELETE /api/warehouses/{warehouse}` → [WarehouseController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:216) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa sản phẩm** → `DELETE /api/purchase/products/{product}` → [ProductController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:427) → Trang/Component: [Trang Sản phẩm — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/Index.vue:380)
- **Xóa sản phẩm** → `DELETE /api/warehouse/products/{product}` → [ProductController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:427) → Trang/Component: [Trang Sản phẩm — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:379)
- **Xóa đơn vị tính** → `DELETE /api/purchase/units/{unit}` → [UnitController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:133) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa đơn vị tính** → `DELETE /api/warehouse/units/{unit}` → [UnitController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:133) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Yêu cầu hoàn hàng giao phiếu nhập/xuất kho** → `POST /api/warehouse/slips/{id}/request-delivery-return` → [WarehouseSlipController::requestDeliveryReturn()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:640) → Trang/Component: [Trang Phiếu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:533)

### Kế toán và công nợ

- **Bật/tắt trạng thái ngân hàng** → `PATCH /api/accountant/banks/{bank}/toggle-status` → [BankController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/BankController.php:134) → Trang/Component: [Trang Ngân hàng — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Bank/Index.vue:207)
- **Bật/tắt trạng thái tiền tệ và tỷ giá** → `PATCH /api/accountant/currencies/{currency}/toggle-status` → [CurrencyController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:247) → Trang/Component: [Trang Tiền tệ — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/Index.vue:280)
- **Bật/tắt trạng thái tiền tệ và tỷ giá** → `PATCH /api/currencies/{currency}/toggle-status` → [CurrencyController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:247) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Bật/tắt trạng thái tài khoản kế toán** → `PATCH /api/accountant/accounts/{account}/toggle-status` → [AccountController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:216) → Trang/Component: [Trang Tài khoản — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/Index.vue:270)
- **Duyệt giao dịch kế toán** → `POST /api/accountant/transactions/{transaction}/approve` → [TransactionController::approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:267) → Trang/Component: [Trang Giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:380)
- **Lấy danh sách lựa chọn tiền tệ và tỷ giá** → `GET/HEAD /api/currencies/for-select` → [CurrencyController::forSelect()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:14) → Trang/Component: [Trang Đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:485), [Trang Biểu mẫu đơn mua — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/PurchaseOrderForm.vue:536), [Trang Nhà cung cấp — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/Index.vue:314), [Trang Khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:389), [Trang Đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:452), [Form tạo/sửa đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:762)
- **Lấy danh sách lựa chọn tài khoản kế toán** → `GET/HEAD /api/accountant/accounts/all` → [AccountController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:69) → Trang/Component: [Trang Sổ cái tài khoản — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/AccountLedger/Index.vue:194), [Trang Giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:468), [Form giao dịch kế toán — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:916)
- **Lấy danh sách đang hoạt động loại giao dịch** → `GET/HEAD /api/accountant/transaction-categories/active` → [TransactionCategoryController::active()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionCategoryController.php:42) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Lấy tỷ giá giao dịch giao dịch kế toán** → `GET/HEAD /api/accountant/transactions/exchange-rate` → [TransactionController::exchangeRate()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:140) → Trang/Component: [Form giao dịch kế toán — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1297)
- **Sửa giao dịch kế toán** → `PUT /api/accountant/transactions/{transaction}` → [TransactionController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:159) → Trang/Component: [Form giao dịch kế toán — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1439)
- **Sửa loại giao dịch** → `PUT /api/accountant/transaction-categories/{transactionCategory}` → [TransactionCategoryController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionCategoryController.php:82) → Trang/Component: [Trang Loại giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/TransactionCategory/Index.vue:238), [Trang Biểu mẫu loại giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/TransactionCategory/TransactionCategoryForm.vue:212)
- **Sửa ngân hàng** → `PUT /api/accountant/banks/{bank}` → [BankController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/BankController.php:102) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Sửa tiền tệ và tỷ giá** → `PUT /api/accountant/currencies/{currency}` → [CurrencyController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:123) → Trang/Component: [Trang Biểu mẫu tiền tệ — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/CurrencyForm.vue:220)
- **Sửa tài khoản kế toán** → `PUT /api/accountant/accounts/{account}` → [AccountController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:139) → Trang/Component: [Trang Biểu mẫu tài khoản — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/AccountForm.vue:267)
- **Thêm tỷ giá tiền tệ và tỷ giá** → `POST /api/accountant/currencies/{currency}/rates` → [CurrencyController::storeRate()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:206) → Trang/Component: [Trang Lịch sử tỷ giá — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/CurencyRateHistory.vue:145)
- **Tính lại số dư tài khoản kế toán** → `POST /api/accountant/accounts/{id}/rebuild-balance` → [AccountController::rebuildBalance()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:230) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tính số tiền đơn còn phải thu/trả giao dịch kế toán** → `GET/HEAD /api/accountant/transactions/order-outstanding` → [TransactionController::orderOutstanding()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:301) → Trang/Component: [Form giao dịch kế toán — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1351)
- **Tạo giao dịch kế toán** → `POST /api/accountant/transactions` → [TransactionController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:78) → Trang/Component: [Form giao dịch kế toán — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1441)
- **Tạo loại giao dịch** → `POST /api/accountant/transaction-categories` → [TransactionCategoryController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionCategoryController.php:68) → Trang/Component: [Trang Biểu mẫu loại giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/TransactionCategory/TransactionCategoryForm.vue:216)
- **Tạo ngân hàng** → `POST /api/accountant/banks` → [BankController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/BankController.php:65) → Trang/Component: [Trang Biểu mẫu ngân hàng — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Bank/BankForm.vue:146)
- **Tạo tiền tệ và tỷ giá** → `POST /api/accountant/currencies` → [CurrencyController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:94) → Trang/Component: [Trang Biểu mẫu tiền tệ — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/CurrencyForm.vue:224)
- **Tạo tài khoản kế toán** → `POST /api/accountant/accounts` → [AccountController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:81) → Trang/Component: [Trang Biểu mẫu tài khoản — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/AccountForm.vue:273)
- **Tạo đối soát COD** → `POST /api/accountant/cod-reconciliations` → [CodReconciliationController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CodReconciliationController.php:56) → Trang/Component: [Trang Đối soát COD — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/CodReconciliation/Index.vue:448)
- **Tạo đối tác đối soát đối soát COD** → `POST /api/accountant/cod-reconciliations/partners` → [CodReconciliationController::storePartner()](vscode://file/D:/clone/project-base/app/Http/Controllers/CodReconciliationController.php:77) → Trang/Component: [Trang Đối soát COD — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/CodReconciliation/Index.vue:426)
- **Từ chối giao dịch kế toán** → `POST /api/accountant/transactions/{transaction}/reject` → [TransactionController::reject()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:281) → Trang/Component: [Trang Giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:328)
- **Xem chi tiết giao dịch kế toán** → `GET/HEAD /api/accountant/transactions/{transaction}` → [TransactionController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:221) → Trang/Component: [Trang Chi tiết giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionDetail.vue:416), [Form giao dịch kế toán — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1297)
- **Xem chi tiết loại giao dịch** → `GET/HEAD /api/accountant/transaction-categories/{transactionCategory}` → [TransactionCategoryController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionCategoryController.php:55) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem chi tiết tiền tệ và tỷ giá** → `GET/HEAD /api/accountant/currencies/{currency}` → [CurrencyController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:116) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem chi tiết tài khoản kế toán** → `GET/HEAD /api/accountant/accounts/{account}` → [AccountController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:131) → Trang/Component: [Trang Sổ cái tài khoản — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/AccountLedger/Index.vue:194), [Trang Giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:468), [Form giao dịch kế toán — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:916)
- **Xem chi tiết đối soát COD** → `GET/HEAD /api/accountant/cod-reconciliations/{reconciliation}` → [CodReconciliationController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/CodReconciliationController.php:49) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem danh sách báo cáo lãi lỗ** → `GET/HEAD /api/accountant/profit-loss-report` → [Accountant\ProfitLossReportController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/Accountant/ProfitLossReportController.php:15) → Trang/Component: [Trang Báo cáo lãi lỗ — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Report/ProfitLoss.vue:249)
- **Xem danh sách giao dịch kế toán** → `GET/HEAD /api/accountant/transactions` → [TransactionController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:24) → Trang/Component: [Trang Giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:420)
- **Xem danh sách loại giao dịch** → `GET/HEAD /api/accountant/transaction-categories` → [TransactionCategoryController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionCategoryController.php:22) → Trang/Component: [Trang Loại giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/TransactionCategory/Index.vue:203), [Trang Giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:469)
- **Xem danh sách ngân hàng** → `GET/HEAD /api/accountant/banks` → [BankController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/BankController.php:16) → Trang/Component: [Trang Biểu mẫu tài khoản — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/AccountForm.vue:295), [Trang Ngân hàng — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Bank/Index.vue:213)
- **Xem danh sách sổ cái tài khoản** → `GET/HEAD /api/accountant/account-ledgers` → [Accountant\AccountLedgerController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/Accountant/AccountLedgerController.php:11) → Trang/Component: [Trang Sổ cái tài khoản — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/AccountLedger/Index.vue:170)
- **Xem danh sách tiền tệ và tỷ giá** → `GET/HEAD /api/accountant/currencies` → [CurrencyController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:61) → Trang/Component: [Trang Biểu mẫu tài khoản — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/AccountForm.vue:294), [Trang Tiền tệ — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/Index.vue:253), [Trang Giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:470)
- **Xem danh sách tài khoản kế toán** → `GET/HEAD /api/accountant/accounts` → [AccountController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:12) → Trang/Component: [Trang Tài khoản — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/Index.vue:281)
- **Xem danh sách đối soát COD** → `GET/HEAD /api/accountant/cod-reconciliations` → [CodReconciliationController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CodReconciliationController.php:16) → Trang/Component: [Trang Đối soát COD — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/CodReconciliation/Index.vue:414)
- **Xem lịch sử tỷ giá tiền tệ và tỷ giá** → `GET/HEAD /api/accountant/currencies/{currency}/rates` → [CurrencyController::rates()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:194) → Trang/Component: [Trang Lịch sử tỷ giá — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/CurencyRateHistory.vue:135)
- **Xóa giao dịch kế toán** → `DELETE /api/accountant/transactions/{transaction}` → [TransactionController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:325) → Trang/Component: [Trang Giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:351)
- **Xóa loại giao dịch** → `DELETE /api/accountant/transaction-categories/{transactionCategory}` → [TransactionCategoryController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionCategoryController.php:103) → Trang/Component: [Trang Loại giao dịch — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/TransactionCategory/Index.vue:253)
- **Xóa ngân hàng** → `DELETE /api/accountant/banks/{bank}` → [BankController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/BankController.php:130) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa tiền tệ và tỷ giá** → `DELETE /api/accountant/currencies/{currency}` → [CurrencyController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:179) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa tài khoản kế toán** → `DELETE /api/accountant/accounts/{account}` → [AccountController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:196) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp

### Bảng điều khiển, nhật ký và thông báo

- **Mở trang tổng quan bảng điều khiển** → `GET/HEAD //` → [DashboardController::landing()](vscode://file/D:/clone/project-base/app/Http/Controllers/DashboardController.php:25) → Trang/Component: [Trang Nhật ký hoạt động](vscode://file/D:/clone/project-base/resources/js/Pages/AuditLog/Index.vue:209), [Trang Danh mục — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Category/Index.vue:227), [Trang Đơn vị tính — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Unit/Index.vue:212), [Trang Danh mục — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Category/Index.vue:222), [Trang tạo phiếu nhập — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:373), [Trang Đơn vị tính — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Unit/Index.vue:212), [Trang reference.js](vscode://file/D:/clone/project-base/resources/js/store/modules/reference.js:64), [Trang Bảng điều khiển](vscode://file/D:/clone/project-base/resources/js/Pages/DashBoard.vue:1)
- **Mở trang tổng quan bảng điều khiển** → `GET/HEAD /dashboard` → [DashboardController::landing()](vscode://file/D:/clone/project-base/app/Http/Controllers/DashboardController.php:25) → Trang/Component: [Trang Bảng điều khiển](vscode://file/D:/clone/project-base/resources/js/Pages/DashBoard.vue:1)
- **Truy vết nhật ký nhật ký hoạt động** → `GET/HEAD /api/audit-logs/trace` → [AuditLogController::trace()](vscode://file/D:/clone/project-base/app/Http/Controllers/AuditLogController.php:86) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem chi tiết nhật ký hoạt động** → `GET/HEAD /api/audit-logs/{auditLog}` → [AuditLogController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/AuditLogController.php:109) → Trang/Component: [Trang Nhật ký hoạt động](vscode://file/D:/clone/project-base/resources/js/Pages/AuditLog/Index.vue:184)
- **Xem danh sách nhật ký hoạt động** → `GET/HEAD /api/audit-logs` → [AuditLogController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/AuditLogController.php:13) → Trang/Component: [Trang Nhật ký hoạt động](vscode://file/D:/clone/project-base/resources/js/Pages/AuditLog/Index.vue:202)
- **Xem danh sách thông báo** → `GET/HEAD /api/notifications` → [NotificationController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/NotificationController.php:13) → Trang/Component: [Component NotificationCenter](vscode://file/D:/clone/project-base/resources/js/components/Notifications/NotificationCenter.vue:824)
- **Xem số liệu phân hệ bảng điều khiển** → `GET/HEAD /api/dashboard/{module}` → [DashboardController::module()](vscode://file/D:/clone/project-base/app/Http/Controllers/DashboardController.php:86) → Trang/Component: [Trang Bảng điều khiển](vscode://file/D:/clone/project-base/resources/js/Pages/DashBoard.vue:1205), [Component ModuleDashboard](vscode://file/D:/clone/project-base/resources/js/components/dashboard/ModuleDashboard.vue:390), [Trang Bảng điều khiển — Kế toán](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Dashboard.vue:2), [Trang Bảng điều khiển — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Dashboard.vue:2), [Trang Bảng điều khiển — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Dashboard.vue:2), [Trang Bảng điều khiển — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Dashboard.vue:2)
- **Xem tổng quan bảng điều khiển** → `GET/HEAD /api/dashboard/overview` → [DashboardController::overview()](vscode://file/D:/clone/project-base/app/Http/Controllers/DashboardController.php:50) → Trang/Component: [Trang Bảng điều khiển](vscode://file/D:/clone/project-base/resources/js/Pages/DashBoard.vue:1205)
- **Xóa thông báo** → `DELETE /api/notifications/{notification}` → [NotificationController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/NotificationController.php:45) → Trang/Component: [Component NotificationCenter](vscode://file/D:/clone/project-base/resources/js/components/Notifications/NotificationCenter.vue:935)
- **Đánh dấu tất cả đã đọc thông báo** → `POST /api/notifications/mark-all-read` → [NotificationController::markAllAsRead()](vscode://file/D:/clone/project-base/app/Http/Controllers/NotificationController.php:39) → Trang/Component: [Component NotificationCenter](vscode://file/D:/clone/project-base/resources/js/components/Notifications/NotificationCenter.vue:916)
- **Đánh dấu đã đọc thông báo** → `POST /api/notifications/{notification}/mark-as-read` → [NotificationController::markAsRead()](vscode://file/D:/clone/project-base/app/Http/Controllers/NotificationController.php:33) → Trang/Component: [Component NotificationCenter](vscode://file/D:/clone/project-base/resources/js/components/Notifications/NotificationCenter.vue:904)
- **Đếm số thông báo chưa đọc** → `GET/HEAD /api/notifications/unread-count` → [NotificationController::unreadCount()](vscode://file/D:/clone/project-base/app/Http/Controllers/NotificationController.php:24) → Trang/Component: [Component NotificationCenter](vscode://file/D:/clone/project-base/resources/js/components/Notifications/NotificationCenter.vue:779), [Component UserMenu](vscode://file/D:/clone/project-base/resources/js/components/layout/header/UserMenu.vue:403)

### Xác thực và API dùng chung

- **Chuyển tới trang đăng nhập Google** → `GET/HEAD /login/google` → [Auth\GoogleController::redirectToGoogle()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/GoogleController.php:19) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Lấy danh sách phường/xã địa chỉ hành chính** → `GET/HEAD /api/provinces/{province}/wards` → [AddressController::wards()](vscode://file/D:/clone/project-base/app/Http/Controllers/AddressController.php:15) → Trang/Component: [Trang Tạo công ty](vscode://file/D:/clone/project-base/resources/js/Pages/Company/Create.vue:233), [Trang Biểu mẫu nhà cung cấp — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/SupplierForm.vue:458), [Trang Biểu mẫu khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/CustomerForm.vue:449), [Form tạo/sửa đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:628), [Trang Biểu mẫu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/WarehouseForm.vue:185)
- **Lấy danh sách phường/xã địa chỉ hành chính** → `GET/HEAD /shop/locations/provinces/{province}/wards` → [AddressController::wards()](vscode://file/D:/clone/project-base/app/Http/Controllers/AddressController.php:15) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:618), [Trang thanh toán cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:405)
- **Lấy danh sách tỉnh/thành địa chỉ hành chính** → `GET/HEAD /api/provinces` → [AddressController::provinces()](vscode://file/D:/clone/project-base/app/Http/Controllers/AddressController.php:10) → Trang/Component: [Trang Tạo công ty](vscode://file/D:/clone/project-base/resources/js/Pages/Company/Create.vue:223), [Trang Biểu mẫu nhà cung cấp — Mua hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/SupplierForm.vue:445), [Trang Biểu mẫu khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/CustomerForm.vue:434), [Trang Khách hàng — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:392), [Trang Đơn bán — Bán hàng](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:567), [Trang Biểu mẫu kho — Kho](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/WarehouseForm.vue:240)
- **Lấy danh sách tỉnh/thành địa chỉ hành chính** → `GET/HEAD /shop/locations/provinces` → [AddressController::provinces()](vscode://file/D:/clone/project-base/app/Http/Controllers/AddressController.php:10) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:605), [Trang thanh toán cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:394)
- **Mở trang tạo liên kết đặt lại mật khẩu** → `GET/HEAD /forgot-password` → [Auth\PasswordResetLinkController::create()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/PasswordResetLinkController.php:16) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Mở trang tạo mật khẩu mới** → `GET/HEAD /reset-password/{token}` → [Auth\NewPasswordController::create()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/NewPasswordController.php:21) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Mở trang tạo phiên đăng nhập** → `GET/HEAD /login` → [Auth\AuthenticatedSessionController::create()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/AuthenticatedSessionController.php:19) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Mở trang tạo đăng ký người dùng** → `GET/HEAD /register` → [Auth\RegisteredUserController::create()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/RegisteredUserController.php:20) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Sửa mật khẩu** → `PUT /password` → [Auth\PasswordController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/PasswordController.php:16) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:594)
- **Sửa số điện thoại** → `PUT /phone-update` → [Auth\UpdatePhoneController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/UpdatePhoneController.php:16) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo email xác minh** → `POST /email/verification-notification` → [Auth\EmailVerificationNotificationController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/EmailVerificationNotificationController.php:14) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo liên kết đặt lại mật khẩu** → `POST /forgot-password` → [Auth\PasswordResetLinkController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/PasswordResetLinkController.php:26) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo mật khẩu mới** → `POST /reset-password` → [Auth\NewPasswordController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/NewPasswordController.php:31) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo phiên đăng nhập** → `POST /login` → [Auth\AuthenticatedSessionController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/AuthenticatedSessionController.php:31) → Trang/Component: [Component CustomerAuthPanel](vscode://file/D:/clone/project-base/resources/js/components/Storefront/CustomerAuthPanel.vue:86), [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:482)
- **Tạo xác nhận mật khẩu** → `POST /confirm-password` → [Auth\ConfirmablePasswordController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/ConfirmablePasswordController.php:25) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Tạo đăng ký người dùng** → `POST /register` → [Auth\RegisteredUserController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/RegisteredUserController.php:30) → Trang/Component: [Component CustomerAuthPanel](vscode://file/D:/clone/project-base/resources/js/components/Storefront/CustomerAuthPanel.vue:95), [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:482)
- **Xem chi tiết xác nhận mật khẩu** → `GET/HEAD /confirm-password` → [Auth\ConfirmablePasswordController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/ConfirmablePasswordController.php:17) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xem danh sách số điện thoại** → `GET/HEAD /phone-update` → [Auth\UpdatePhoneController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/UpdatePhoneController.php:11) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp
- **Xóa phiên đăng nhập** → `GET/POST/HEAD /logout` → [Auth\AuthenticatedSessionController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/AuthenticatedSessionController.php:44) → Trang/Component: [Trang tài khoản cửa hàng trực tuyến — Cửa hàng trực tuyến](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:574)
- **Xử lý phản hồi đăng nhập từ Google** → `GET/HEAD /login/google/callback` → [Auth\GoogleController::handleGoogleCallback()](vscode://file/D:/clone/project-base/app/Http/Controllers/Auth/GoogleController.php:31) → Chưa phát hiện Vue/JS gọi endpoint này; có thể endpoint chưa được dùng hoặc được gọi gián tiếp

## Tìm lỗi và luồng trạng thái

- [Tìm theo triệu chứng, input, mã lỗi và thứ tự debug](PROJECT_DEBUGGING_INDEX.md).
- [Xem luồng nghiệp vụ hiện hành](../resources/docs/BUSINESS_FLOWS.md).
- [Tra bảng dữ liệu và migration](PROJECT_DATABASE_INDEX.md).

## Mục lục tra cứu như sách

> Chọn phân hệ, sau đó chọn đúng trang cần sửa hoặc đang có lỗi. Mỗi trang có mục riêng với API, controller/function và phạm vi ảnh hưởng.

- [Nền tảng và quản trị](#nn-tng-v-a-qun-tr)
  - [Trang Phòng ban — `Manage/Department/Index.vue`](#nn-tng-v-a-qun-tr-resources-js-pages-manage-department-index-vue)
  - [Trang Quyền — `Manage/Permission.vue`](#nn-tng-v-a-qun-tr-resources-js-pages-manage-permission-vue)
  - [Trang Biểu mẫu quyền — `Manage/PermissionForm.vue`](#nn-tng-v-a-qun-tr-resources-js-pages-manage-permissionform-vue)
  - [Trang Chức vụ — `Manage/Position/Index.vue`](#nn-tng-v-a-qun-tr-resources-js-pages-manage-position-index-vue)
  - [Trang Vai trò — `Manage/Role.vue`](#nn-tng-v-a-qun-tr-resources-js-pages-manage-role-vue)
  - [Trang Biểu mẫu vai trò — `Manage/RoleForm.vue`](#nn-tng-v-a-qun-tr-resources-js-pages-manage-roleform-vue)
  - [Trang Người dùng — `Manage/User.vue`](#nn-tng-v-a-qun-tr-resources-js-pages-manage-user-vue)
  - [Trang Chi tiết người dùng — `Manage/UserDetail.vue`](#nn-tng-v-a-qun-tr-resources-js-pages-manage-userdetail-vue)
  - [Trang Biểu mẫu người dùng — `Manage/UserForm.vue`](#nn-tng-v-a-qun-tr-resources-js-pages-manage-userform-vue)
  - [Trang Tạo công ty — `Company/Create.vue`](#nn-tng-v-a-qun-tr-resources-js-pages-company-create-vue)
  - [Trang Chỉnh sửa hồ sơ — `Profile/Edit.vue`](#nn-tng-v-a-qun-tr-resources-js-pages-profile-edit-vue)
  - [Service](#nn-tng-v-a-qun-tr-service)
  - [Mô hình và kiểm thử](#nn-tng-v-a-qun-tr-model-test)
- [Mua hàng](#mua-h-ang)
  - [Trang Biểu mẫu danh mục — `Purchase/Category/CategoryForm.vue`](#mua-h-ang-resources-js-pages-purchase-category-categoryform-vue)
  - [Trang Danh mục — `Purchase/Category/Index.vue`](#mua-h-ang-resources-js-pages-purchase-category-index-vue)
  - [Trang Bảng điều khiển — `Purchase/Dashboard.vue`](#mua-h-ang-resources-js-pages-purchase-dashboard-vue)
  - [Trang Đơn mua — `Purchase/Order/Index.vue`](#mua-h-ang-resources-js-pages-purchase-order-index-vue)
  - [Trang Chi tiết đơn mua — `Purchase/Order/PurchaseOrderDetail.vue`](#mua-h-ang-resources-js-pages-purchase-order-purchaseorderdetail-vue)
  - [Trang Biểu mẫu đơn mua — `Purchase/Order/PurchaseOrderForm.vue`](#mua-h-ang-resources-js-pages-purchase-order-purchaseorderform-vue)
  - [Trang Sản phẩm — `Purchase/Product/Index.vue`](#mua-h-ang-resources-js-pages-purchase-product-index-vue)
  - [Trang Biểu mẫu sản phẩm — `Purchase/Product/ProductForm.vue`](#mua-h-ang-resources-js-pages-purchase-product-productform-vue)
  - [Trang Nhà cung cấp — `Purchase/Supplier/Index.vue`](#mua-h-ang-resources-js-pages-purchase-supplier-index-vue)
  - [Trang Chi tiết nhà cung cấp — `Purchase/Supplier/SupplierDetail.vue`](#mua-h-ang-resources-js-pages-purchase-supplier-supplierdetail-vue)
  - [Trang Biểu mẫu nhà cung cấp — `Purchase/Supplier/SupplierForm.vue`](#mua-h-ang-resources-js-pages-purchase-supplier-supplierform-vue)
  - [Trang Đơn vị tính — `Purchase/Unit/Index.vue`](#mua-h-ang-resources-js-pages-purchase-unit-index-vue)
  - [Trang Biểu mẫu đơn vị tính — `Purchase/Unit/UnitForm.vue`](#mua-h-ang-resources-js-pages-purchase-unit-unitform-vue)
  - [Service](#mua-h-ang-service)
  - [Mô hình và kiểm thử](#mua-h-ang-model-test)
- [Bán hàng](#b-an-h-ang)
  - [Trang Biểu mẫu mã giảm giá — `Sale/Coupon/CouponForm.vue`](#b-an-h-ang-resources-js-pages-sale-coupon-couponform-vue)
  - [Trang Mã giảm giá — `Sale/Coupon/Index.vue`](#b-an-h-ang-resources-js-pages-sale-coupon-index-vue)
  - [Trang chi tiết khách hàng — `Sale/Customer/CustomerDetail.vue`](#b-an-h-ang-resources-js-pages-sale-customer-customerdetail-vue)
  - [Trang Biểu mẫu khách hàng — `Sale/Customer/CustomerForm.vue`](#b-an-h-ang-resources-js-pages-sale-customer-customerform-vue)
  - [Trang Khách hàng — `Sale/Customer/Index.vue`](#b-an-h-ang-resources-js-pages-sale-customer-index-vue)
  - [Trang Bảng điều khiển — `Sale/Dashboard.vue`](#b-an-h-ang-resources-js-pages-sale-dashboard-vue)
  - [Trang Đơn bán — `Sale/Order/Index.vue`](#b-an-h-ang-resources-js-pages-sale-order-index-vue)
  - [Trang Chi tiết đơn bán — `Sale/Order/SaleOrderDetail.vue`](#b-an-h-ang-resources-js-pages-sale-order-saleorderdetail-vue)
  - [Form tạo/sửa đơn bán — `Sale/Order/SaleOrderForm.vue`](#b-an-h-ang-resources-js-pages-sale-order-saleorderform-vue)
  - [Trang bán hàng POS — `Sale/Pos/Index.vue`](#b-an-h-ang-resources-js-pages-sale-pos-index-vue)
  - [Service](#b-an-h-ang-service)
  - [Mô hình và kiểm thử](#b-an-h-ang-model-test)
- [Cửa hàng trực tuyến](#ca-h-ang-trc-tuyn)
  - [Trang tài khoản cửa hàng trực tuyến — `Storefront/Account.vue`](#ca-h-ang-trc-tuyn-resources-js-pages-storefront-account-vue)
  - [Trang Giỏ hàng — `Storefront/Cart.vue`](#ca-h-ang-trc-tuyn-resources-js-pages-storefront-cart-vue)
  - [Trang thanh toán cửa hàng trực tuyến — `Storefront/Checkout.vue`](#ca-h-ang-trc-tuyn-resources-js-pages-storefront-checkout-vue)
  - [Trang Danh bạ cửa hàng — `Storefront/Directory.vue`](#ca-h-ang-trc-tuyn-resources-js-pages-storefront-directory-vue)
  - [Trang Thông báo — `Storefront/Notifications.vue`](#ca-h-ang-trc-tuyn-resources-js-pages-storefront-notifications-vue)
  - [Trang chi tiết đơn cửa hàng trực tuyến — `Storefront/OrderDetail.vue`](#ca-h-ang-trc-tuyn-resources-js-pages-storefront-orderdetail-vue)
  - [Trang Sản phẩm — `Storefront/Product.vue`](#ca-h-ang-trc-tuyn-resources-js-pages-storefront-product-vue)
  - [Trang Cửa hàng — `Storefront/Shop.vue`](#ca-h-ang-trc-tuyn-resources-js-pages-storefront-shop-vue)
  - [Trang Đặt hàng thành công — `Storefront/Success.vue`](#ca-h-ang-trc-tuyn-resources-js-pages-storefront-success-vue)
  - [Service](#ca-h-ang-trc-tuyn-service)
  - [Mô hình và kiểm thử](#ca-h-ang-trc-tuyn-model-test)
- [Kho](#kho)
  - [Trang Biểu mẫu danh mục — `Warehouse/Category/CategoryForm.vue`](#kho-resources-js-pages-warehouse-category-categoryform-vue)
  - [Trang Danh mục — `Warehouse/Category/Index.vue`](#kho-resources-js-pages-warehouse-category-index-vue)
  - [Trang Bảng điều khiển — `Warehouse/Dashboard.vue`](#kho-resources-js-pages-warehouse-dashboard-vue)
  - [Trang Kho — `Warehouse/Index.vue`](#kho-resources-js-pages-warehouse-index-vue)
  - [Trang Biến động tồn kho — `Warehouse/InventoryMovement/Index.vue`](#kho-resources-js-pages-warehouse-inventorymovement-index-vue)
  - [Trang Đơn chờ kho — `Warehouse/Order/Index.vue`](#kho-resources-js-pages-warehouse-order-index-vue)
  - [Trang Sản phẩm — `Warehouse/Product/Index.vue`](#kho-resources-js-pages-warehouse-product-index-vue)
  - [Trang Chi tiết sản phẩm — `Warehouse/Product/ProductDetail.vue`](#kho-resources-js-pages-warehouse-product-productdetail-vue)
  - [Trang Biểu mẫu sản phẩm — `Warehouse/Product/ProductForm.vue`](#kho-resources-js-pages-warehouse-product-productform-vue)
  - [Trang Phiếu kho — `Warehouse/Slip/Index.vue`](#kho-resources-js-pages-warehouse-slip-index-vue)
  - [Trang tạo phiếu nhập — `Warehouse/Slip/Purchasecreate.vue`](#kho-resources-js-pages-warehouse-slip-purchasecreate-vue)
  - [Trang tạo phiếu xuất — `Warehouse/Slip/Salecreate.vue`](#kho-resources-js-pages-warehouse-slip-salecreate-vue)
  - [Trang Chi tiết phiếu kho — `Warehouse/Slip/SlipDetail.vue`](#kho-resources-js-pages-warehouse-slip-slipdetail-vue)
  - [Trang Chuyển kho — `Warehouse/Transfer/Index.vue`](#kho-resources-js-pages-warehouse-transfer-index-vue)
  - [Trang Đơn vị tính — `Warehouse/Unit/Index.vue`](#kho-resources-js-pages-warehouse-unit-index-vue)
  - [Trang Biểu mẫu đơn vị tính — `Warehouse/Unit/UnitForm.vue`](#kho-resources-js-pages-warehouse-unit-unitform-vue)
  - [Trang Chi tiết kho — `Warehouse/WarehouseDetail.vue`](#kho-resources-js-pages-warehouse-warehousedetail-vue)
  - [Trang Biểu mẫu kho — `Warehouse/WarehouseForm.vue`](#kho-resources-js-pages-warehouse-warehouseform-vue)
  - [Service](#kho-service)
  - [Mô hình và kiểm thử](#kho-model-test)
- [Kế toán và công nợ](#k-to-an-v-a-c-ong-n)
  - [Trang Sổ cái tài khoản — `Accountant/AccountLedger/Index.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-accountledger-index-vue)
  - [Trang Biểu mẫu tài khoản — `Accountant/Account/AccountForm.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-account-accountform-vue)
  - [Trang Tài khoản — `Accountant/Account/Index.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-account-index-vue)
  - [Trang Biểu mẫu ngân hàng — `Accountant/Bank/BankForm.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-bank-bankform-vue)
  - [Trang Ngân hàng — `Accountant/Bank/Index.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-bank-index-vue)
  - [Trang Đối soát COD — `Accountant/CodReconciliation/Index.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-codreconciliation-index-vue)
  - [Trang Lịch sử tỷ giá — `Accountant/Currency/CurencyRateHistory.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-currency-curencyratehistory-vue)
  - [Trang Biểu mẫu tiền tệ — `Accountant/Currency/CurrencyForm.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-currency-currencyform-vue)
  - [Trang Tiền tệ — `Accountant/Currency/Index.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-currency-index-vue)
  - [Trang Chi tiết khách hàng — `Accountant/Customer/CustomerDetail.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-customer-customerdetail-vue)
  - [Trang Khách hàng — `Accountant/Customer/Index.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-customer-index-vue)
  - [Trang Bảng điều khiển — `Accountant/Dashboard.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-dashboard-vue)
  - [Trang Báo cáo lãi lỗ — `Accountant/Report/ProfitLoss.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-report-profitloss-vue)
  - [Trang Nhà cung cấp — `Accountant/Supplier/Index.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-supplier-index-vue)
  - [Trang Loại giao dịch — `Accountant/TransactionCategory/Index.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-transactioncategory-index-vue)
  - [Trang Biểu mẫu loại giao dịch — `Accountant/TransactionCategory/TransactionCategoryForm.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-transactioncategory-transactioncategoryform-vue)
  - [Trang Giao dịch — `Accountant/Transaction/Index.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-transaction-index-vue)
  - [Trang Chi tiết giao dịch — `Accountant/Transaction/TransactionDetail.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-transaction-transactiondetail-vue)
  - [Form giao dịch kế toán — `Accountant/Transaction/TransactionForm.vue`](#k-to-an-v-a-c-ong-n-resources-js-pages-accountant-transaction-transactionform-vue)
  - [Service](#k-to-an-v-a-c-ong-n-service)
  - [Mô hình và kiểm thử](#k-to-an-v-a-c-ong-n-model-test)
- [Bảng điều khiển, nhật ký và thông báo](#bng-diu-khin-nht-k-y-v-a-th-ong-b-ao)
  - [Trang Chi tiết nhật ký hoạt động — `AuditLog/AuditLogDetail.vue`](#bng-diu-khin-nht-k-y-v-a-th-ong-b-ao-resources-js-pages-auditlog-auditlogdetail-vue)
  - [Trang Nhật ký hoạt động — `AuditLog/Index.vue`](#bng-diu-khin-nht-k-y-v-a-th-ong-b-ao-resources-js-pages-auditlog-index-vue)
  - [Trang Bảng điều khiển — `DashBoard.vue`](#bng-diu-khin-nht-k-y-v-a-th-ong-b-ao-resources-js-pages-dashboard-vue)
  - [Trang chủ — `Home.vue`](#bng-diu-khin-nht-k-y-v-a-th-ong-b-ao-resources-js-pages-home-vue)
  - [Service](#bng-diu-khin-nht-k-y-v-a-th-ong-b-ao-service)
  - [Mô hình và kiểm thử](#bng-diu-khin-nht-k-y-v-a-th-ong-b-ao-model-test)
- [Xác thực và API dùng chung](#x-ac-thc-v-a-api-d-ung-chung)
  - [Service](#x-ac-thc-v-a-api-d-ung-chung-service)
  - [Mô hình và kiểm thử](#x-ac-thc-v-a-api-d-ung-chung-model-test)
- [Hướng dẫn và tài liệu](#hng-dn-v-a-t-ai-liu)
  - [Trang Hướng dẫn — `Guide/Index.vue`](#hng-dn-v-a-t-ai-liu-resources-js-pages-guide-index-vue)
  - [Trang Tài liệu — `Document.vue`](#hng-dn-v-a-t-ai-liu-resources-js-pages-document-vue)
  - [Trang nội dung — `Page.vue`](#hng-dn-v-a-t-ai-liu-resources-js-pages-page-vue)
  - [Service](#hng-dn-v-a-t-ai-liu-service)
  - [Mô hình và kiểm thử](#hng-dn-v-a-t-ai-liu-model-test)

<a id="nn-tng-v-a-qun-tr"></a>

## Nền tảng và quản trị

Quản lý công ty, hồ sơ, nhân sự, phòng ban, chức vụ, vai trò và quyền.

### Các trang trong phân hệ


<a id="nn-tng-v-a-qun-tr-resources-js-pages-manage-department-index-vue"></a>

#### Trang Phòng ban — `Manage/Department/Index.vue`

File: [resources/js/Pages/Manage/Department/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Department/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/departments](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Department/Index.vue:263) | [DepartmentController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:26) | Lấy danh sách phòng ban thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn kiểm tra dữ liệu đầu vào. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/departments/managers](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Department/Index.vue:335) | [DepartmentController::managers()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:56) | Thực hiện nghiệp vụ “managers” cho phòng ban. | dữ liệu hoặc hành động liên quan đến phòng ban trên trang này có thể thay đổi. |
| [POST /api/departments](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Department/Index.vue:302) | [DepartmentController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:80) | Kiểm tra đầu vào và tạo phòng ban mới cùng dữ liệu liên quan. Hàm còn chạy trong giao dịch cơ sở dữ liệu, gửi thông báo. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/departments/{department}](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Department/Index.vue:301) | [DepartmentController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:109) | Kiểm tra và cập nhật phòng ban hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn chạy trong giao dịch cơ sở dữ liệu. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |
| [DELETE /api/departments/{department}](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Department/Index.vue:325) | [DepartmentController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:120) | Kiểm tra điều kiện rồi xóa phòng ban và dữ liệu phụ thuộc được xử lý trong hàm. Hàm còn chạy trong giao dịch cơ sở dữ liệu, gửi thông báo. | nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi. |

<a id="nn-tng-v-a-qun-tr-resources-js-pages-manage-permission-vue"></a>

#### Trang Quyền — `Manage/Permission.vue`

File: [resources/js/Pages/Manage/Permission.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Permission.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/permissions](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Permission.vue:170) | [PermissionController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/PermissionController.php:12) | Lấy danh sách quyền thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [DELETE /api/permissions/{id}](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Permission.vue:145) | [PermissionController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/PermissionController.php:89) | Kiểm tra điều kiện rồi xóa quyền và dữ liệu phụ thuộc được xử lý trong hàm. | nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi. |

<a id="nn-tng-v-a-qun-tr-resources-js-pages-manage-permissionform-vue"></a>

#### Trang Biểu mẫu quyền — `Manage/PermissionForm.vue`

File: [resources/js/Pages/Manage/PermissionForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/PermissionForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [POST /api/permissions](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/PermissionForm.vue:111) | [PermissionController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/PermissionController.php:35) | Kiểm tra đầu vào và tạo quyền mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/permissions/{id}](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/PermissionForm.vue:109) | [PermissionController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/PermissionController.php:66) | Kiểm tra và cập nhật quyền hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |

<a id="nn-tng-v-a-qun-tr-resources-js-pages-manage-position-index-vue"></a>

#### Trang Chức vụ — `Manage/Position/Index.vue`

File: [resources/js/Pages/Manage/Position/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Position/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/departments/all](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Position/Index.vue:332) | [DepartmentController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:48) | Lấy toàn bộ phòng ban thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/positions](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Position/Index.vue:260) | [PositionController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/PositionController.php:22) | Lấy danh sách chức vụ thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn kiểm tra dữ liệu đầu vào. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [POST /api/positions](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Position/Index.vue:299) | [PositionController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/PositionController.php:43) | Kiểm tra đầu vào và tạo chức vụ mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, gửi thông báo. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/positions/{position}](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Position/Index.vue:298) | [PositionController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/PositionController.php:63) | Kiểm tra và cập nhật chức vụ hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |
| [DELETE /api/positions/{position}](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Position/Index.vue:322) | [PositionController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/PositionController.php:70) | Kiểm tra điều kiện rồi xóa chức vụ và dữ liệu phụ thuộc được xử lý trong hàm. | nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi. |

<a id="nn-tng-v-a-qun-tr-resources-js-pages-manage-role-vue"></a>

#### Trang Vai trò — `Manage/Role.vue`

File: [resources/js/Pages/Manage/Role.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Role.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/roles](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/Role.vue:322) | [RoleController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/RoleController.php:13) | Lấy danh sách vai trò thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |

<a id="nn-tng-v-a-qun-tr-resources-js-pages-manage-roleform-vue"></a>

#### Trang Biểu mẫu vai trò — `Manage/RoleForm.vue`

File: [resources/js/Pages/Manage/RoleForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/RoleForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/permissions/all](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/RoleForm.vue:288) | [RoleController::permissions()](vscode://file/D:/clone/project-base/app/Http/Controllers/RoleController.php:66) | Thực hiện nghiệp vụ “permissions” cho vai trò. | dữ liệu hoặc hành động liên quan đến vai trò trên trang này có thể thay đổi. |
| [POST /api/roles](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/RoleForm.vue:307) | [RoleController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/RoleController.php:71) | Kiểm tra đầu vào và tạo vai trò mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/roles/{id}](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/RoleForm.vue:305) | [RoleController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/RoleController.php:112) | Kiểm tra và cập nhật vai trò hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |

<a id="nn-tng-v-a-qun-tr-resources-js-pages-manage-user-vue"></a>

#### Trang Người dùng — `Manage/User.vue`

File: [resources/js/Pages/Manage/User.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/User.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/users/user](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/User.vue:424) | [API\UserController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:22) | Lấy danh sách người dùng/nhân sự thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/users/roles](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/User.vue:480) | [API\UserController::role()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:81) | Thực hiện nghiệp vụ “role” cho người dùng/nhân sự. | dữ liệu hoặc hành động liên quan đến người dùng/nhân sự trên trang này có thể thay đổi. |
| [PATCH /api/users/{user}/status](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/User.vue:457) | [API\UserController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:363) | Đổi trạng thái hoạt động của người dùng/nhân sự sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến người dùng/nhân sự trên trang này có thể thay đổi. |
| [GET\|HEAD /api/departments/all](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/User.vue:481) | [DepartmentController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:48) | Lấy toàn bộ phòng ban thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |

<a id="nn-tng-v-a-qun-tr-resources-js-pages-manage-userdetail-vue"></a>

#### Trang Chi tiết người dùng — `Manage/UserDetail.vue`

File: [resources/js/Pages/Manage/UserDetail.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserDetail.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/users/user/{id}](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserDetail.vue:116) | [API\UserController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:105) | Lấy chi tiết một người dùng/nhân sự kèm các quan hệ cần cho màn hình xem/sửa. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |

<a id="nn-tng-v-a-qun-tr-resources-js-pages-manage-userform-vue"></a>

#### Trang Biểu mẫu người dùng — `Manage/UserForm.vue`

File: [resources/js/Pages/Manage/UserForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/users/roles](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:580) | [API\UserController::role()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:81) | Thực hiện nghiệp vụ “role” cho người dùng/nhân sự. | dữ liệu hoặc hành động liên quan đến người dùng/nhân sự trên trang này có thể thay đổi. |
| [POST /api/users/user](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:687) | [API\UserController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:165) | Kiểm tra đầu vào và tạo người dùng/nhân sự mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/users/user/{id}](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:685) | [API\UserController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/API/UserController.php:268) | Kiểm tra và cập nhật người dùng/nhân sự hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |
| [GET\|HEAD /api/departments/all](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:592) | [DepartmentController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:48) | Lấy toàn bộ phòng ban thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [POST /api/departments](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:640) | [DepartmentController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/DepartmentController.php:80) | Kiểm tra đầu vào và tạo phòng ban mới cùng dữ liệu liên quan. Hàm còn chạy trong giao dịch cơ sở dữ liệu, gửi thông báo. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [GET\|HEAD /api/positions/all](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:602) | [PositionController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/PositionController.php:35) | Lấy toàn bộ chức vụ thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [POST /api/positions](vscode://file/D:/clone/project-base/resources/js/Pages/Manage/UserForm.vue:660) | [PositionController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/PositionController.php:43) | Kiểm tra đầu vào và tạo chức vụ mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, gửi thông báo. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |

<a id="nn-tng-v-a-qun-tr-resources-js-pages-company-create-vue"></a>

#### Trang Tạo công ty — `Company/Create.vue`

File: [resources/js/Pages/Company/Create.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Company/Create.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/company/create](vscode://file/D:/clone/project-base/resources/js/Pages/Company/Create.vue:1) | [CompanyController::create()](vscode://file/D:/clone/project-base/app/Http/Controllers/CompanyController.php:18) | Render hoặc chuẩn bị dữ liệu cho màn hình tạo công ty. Hàm còn quy đổi/kiểm tra tiền tệ. | dữ liệu hoặc hành động liên quan đến công ty trên trang này có thể thay đổi. |
| [POST /company](vscode://file/D:/clone/project-base/resources/js/Pages/Company/Create.vue:258) | [CompanyController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CompanyController.php:33) | Kiểm tra đầu vào và tạo công ty mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, chạy trong giao dịch cơ sở dữ liệu, quy đổi/kiểm tra tiền tệ. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |

<a id="nn-tng-v-a-qun-tr-resources-js-pages-profile-edit-vue"></a>

#### Trang Chỉnh sửa hồ sơ — `Profile/Edit.vue`

File: [resources/js/Pages/Profile/Edit.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Profile/Edit.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /profile](vscode://file/D:/clone/project-base/resources/js/Pages/Profile/Edit.vue:1) | [ProfileController::edit()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProfileController.php:18) | Render màn hình chỉnh sửa hồ sơ người dùng với dữ liệu người dùng hiện tại. | dữ liệu hoặc hành động liên quan đến hồ sơ người dùng trên trang này có thể thay đổi. |
| [PATCH /profile](vscode://file/D:/clone/project-base/resources/js/Pages/Profile/Edit.vue:29) | [ProfileController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProfileController.php:28) | Kiểm tra và cập nhật hồ sơ người dùng hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |


### Controller và Function

- **Controller:** [API\UserController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-api-usercontroller), [WEB\UserController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-web-usercontroller), [CompanyController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-companycontroller), [WEB\CompaniesController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-web-companiescontroller), [DepartmentController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-departmentcontroller), [WEB\DepartmentsController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-web-departmentscontroller), [PositionController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-positioncontroller), [WEB\PositionUserController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-web-positionusercontroller), [EmployeeController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-employeecontroller), [RoleController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-rolecontroller), [PermissionController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-permissioncontroller), [ProfileController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-profilecontroller)
- **Chi tiết Function, validation và ảnh hưởng:** [Mở chỉ mục Function toàn dự án](PROJECT_FUNCTION_INDEX.md).
- **Database:** [Mở Model, bảng và migration của phân hệ](PROJECT_DATABASE_INDEX.md#nn-tng-v-a-qun-tr).

<a id="nn-tng-v-a-qun-tr-service"></a>

### Service

| Service | Viết gì? | Hàm công khai |
| --- | --- | --- |
| [NotificationService](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:1) | Tạo, phân phối và quản lý thông báo nội bộ. | [create()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:17)<br>[createForUsers()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:47)<br>[createForCustomerAccount()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:68)<br>[createForPermission()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:92)<br>[createForRole()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:149)<br>[createForHigherRoleUsers()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:188)<br>[createForCompany()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:227)<br>[getUserNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:246)<br>[getUserNotificationsByCategory()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:268)<br>[markAsRead()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:310)<br>[markAllAsRead()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:326)<br>[delete()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:339)<br>[getUnreadCount()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:353)<br>[getUnreadCountByCategory()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:366)<br>[deleteOldNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:396)<br>[getCompanyNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:405) |
| [ActivityLogService](vscode://file/D:/clone/project-base/app/Services/ActivityLogService.php:1) | Ghi nhật ký hoạt động cho các thao tác nghiệp vụ. | [log()](vscode://file/D:/clone/project-base/app/Services/ActivityLogService.php:9) |

<a id="nn-tng-v-a-qun-tr-model-test"></a>

### Mô hình và kiểm thử

- **Mô hình chính:** [User](vscode://file/D:/clone/project-base/app/Models/User.php:1), [Company](vscode://file/D:/clone/project-base/app/Models/Company.php:1), [Department](vscode://file/D:/clone/project-base/app/Models/Department.php:1), [Position](vscode://file/D:/clone/project-base/app/Models/Position.php:1), [Role](vscode://file/D:/clone/project-base/app/Models/Role.php:1), [Permission](vscode://file/D:/clone/project-base/app/Models/Permission.php:1).
- **Kiểm thử liên quan:** [DemoModuleRolesTest.php](vscode://file/D:/clone/project-base/tests/Feature/DemoModuleRolesTest.php:1), [DepartmentManagerAssignmentTest.php](vscode://file/D:/clone/project-base/tests/Feature/DepartmentManagerAssignmentTest.php:1), [DepartmentPositionFlowTest.php](vscode://file/D:/clone/project-base/tests/Feature/DepartmentPositionFlowTest.php:1), [PermissionListTest.php](vscode://file/D:/clone/project-base/tests/Feature/PermissionListTest.php:1), [TransactionCategoryCompanyIsolationTest.php](vscode://file/D:/clone/project-base/tests/Feature/TransactionCategoryCompanyIsolationTest.php:1), [UserActivityLogTest.php](vscode://file/D:/clone/project-base/tests/Feature/UserActivityLogTest.php:1), [UserListVisibilityTest.php](vscode://file/D:/clone/project-base/tests/Feature/UserListVisibilityTest.php:1), [BroadcastCompanyDataChangesMiddlewareTest.php](vscode://file/D:/clone/project-base/tests/Unit/BroadcastCompanyDataChangesMiddlewareTest.php:1), [CompanyCurrencyServiceTest.php](vscode://file/D:/clone/project-base/tests/Unit/CompanyCurrencyServiceTest.php:1), [CompanyDataChangedTest.php](vscode://file/D:/clone/project-base/tests/Unit/CompanyDataChangedTest.php:1).

<a id="mua-h-ang"></a>

## Mua hàng

Quản lý nhà cung cấp và vòng đời đơn mua từ tạo, duyệt đến chuyển kho nhập.

### Các trang trong phân hệ


<a id="mua-h-ang-resources-js-pages-purchase-category-categoryform-vue"></a>

#### Trang Biểu mẫu danh mục — `Purchase/Category/CategoryForm.vue`

File: [resources/js/Pages/Purchase/Category/CategoryForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Category/CategoryForm.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="mua-h-ang-resources-js-pages-purchase-category-index-vue"></a>

#### Trang Danh mục — `Purchase/Category/Index.vue`

File: [resources/js/Pages/Purchase/Category/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Category/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/purchase/categories](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Category/Index.vue:210) | [CategoryController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:11) | Lấy danh sách danh mục sản phẩm thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [PATCH /api/purchase/categories/{id}/status](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Category/Index.vue:244) | [CategoryController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:215) | Đổi trạng thái hoạt động của danh mục sản phẩm sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến danh mục sản phẩm trên trang này có thể thay đổi. |

<a id="mua-h-ang-resources-js-pages-purchase-dashboard-vue"></a>

#### Trang Bảng điều khiển — `Purchase/Dashboard.vue`

File: [resources/js/Pages/Purchase/Dashboard.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Dashboard.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="mua-h-ang-resources-js-pages-purchase-order-index-vue"></a>

#### Trang Đơn mua — `Purchase/Order/Index.vue`

File: [resources/js/Pages/Purchase/Order/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/purchase/orders](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:454) | [PurchaseOrderController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:46) | Lấy danh sách đơn mua thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/purchase/orders/{order}](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:517) | [PurchaseOrderController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:179) | Lấy chi tiết một đơn mua kèm các quan hệ cần cho màn hình xem/sửa. Hàm còn xử lý số lượng/tồn kho, quy đổi/kiểm tra tiền tệ. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [POST /api/purchase/orders/{id}/approve](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:438) | [PurchaseOrderController::approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:524) | Duyệt đơn mua, cập nhật trạng thái và kích hoạt bước nghiệp vụ tiếp theo. Hàm còn chạy trong giao dịch cơ sở dữ liệu, liên quan công nợ. | nút duyệt/gửi/từ chối, trạng thái hiển thị và khả năng chứng từ đi tiếp sang bước nghiệp vụ sau sẽ thay đổi. |
| [POST /api/purchase/orders/{id}/cancel](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:503) | [PurchaseOrderController::cancel()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:576) | Hủy đơn mua hợp lệ và hoàn tác dữ liệu/trạng thái liên quan nếu có. | nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi. |
| [GET\|HEAD /api/purchase/suppliers/all](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:471) | [SupplierController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:100) | Lấy toàn bộ nhà cung cấp thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/purchase/suppliers/{supplier}](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:471) | [SupplierController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:197) | Lấy chi tiết một nhà cung cấp kèm các quan hệ cần cho màn hình xem/sửa. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [GET\|HEAD /api/purchase/products](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/Index.vue:477) | [ProductController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:51) | Lấy danh sách sản phẩm thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn xử lý số lượng/tồn kho, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |

<a id="mua-h-ang-resources-js-pages-purchase-order-purchaseorderdetail-vue"></a>

#### Trang Chi tiết đơn mua — `Purchase/Order/PurchaseOrderDetail.vue`

File: [resources/js/Pages/Purchase/Order/PurchaseOrderDetail.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/PurchaseOrderDetail.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="mua-h-ang-resources-js-pages-purchase-order-purchaseorderform-vue"></a>

#### Trang Biểu mẫu đơn mua — `Purchase/Order/PurchaseOrderForm.vue`

File: [resources/js/Pages/Purchase/Order/PurchaseOrderForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/PurchaseOrderForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [POST /api/purchase/orders](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/PurchaseOrderForm.vue:760) | [PurchaseOrderController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:238) | Kiểm tra đầu vào và tạo đơn mua mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, chạy trong giao dịch cơ sở dữ liệu, gửi thông báo, xử lý số lượng/tồn kho. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/purchase/orders/{order}](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/PurchaseOrderForm.vue:758) | [PurchaseOrderController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:382) | Kiểm tra và cập nhật đơn mua hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào, chạy trong giao dịch cơ sở dữ liệu, gửi thông báo, xử lý số lượng/tồn kho. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |
| [GET\|HEAD /api/products/for-select](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Order/PurchaseOrderForm.vue:493) | [ProductController::forSelect()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:174) | Lấy danh sách sản phẩm rút gọn, đang hoạt động để dùng trong danh sách chọn/biểu mẫu. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |

<a id="mua-h-ang-resources-js-pages-purchase-product-index-vue"></a>

#### Trang Sản phẩm — `Purchase/Product/Index.vue`

File: [resources/js/Pages/Purchase/Product/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/purchase/products](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/Index.vue:309) | [ProductController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:51) | Lấy danh sách sản phẩm thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn xử lý số lượng/tồn kho, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [DELETE /api/purchase/products/{product}](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/Index.vue:380) | [ProductController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:427) | Kiểm tra điều kiện rồi xóa sản phẩm và dữ liệu phụ thuộc được xử lý trong hàm. | nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi. |
| [PATCH /api/purchase/products/{id}/status](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/Index.vue:391) | [ProductController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:445) | Đổi trạng thái hoạt động của sản phẩm sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến sản phẩm trên trang này có thể thay đổi. |

<a id="mua-h-ang-resources-js-pages-purchase-product-productform-vue"></a>

#### Trang Biểu mẫu sản phẩm — `Purchase/Product/ProductForm.vue`

File: [resources/js/Pages/Purchase/Product/ProductForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [POST /api/purchase/products](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:464) | [ProductController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:211) | Kiểm tra đầu vào và tạo sản phẩm mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, xử lý số lượng/tồn kho. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/purchase/products/{product}](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:460) | [ProductController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:318) | Kiểm tra và cập nhật sản phẩm hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào, xử lý số lượng/tồn kho. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |
| [GET\|HEAD /api/purchase/categories/select](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:495) | [CategoryController::select()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:82) | Lấy danh sách danh mục sản phẩm rút gọn để dùng trong danh sách chọn/biểu mẫu. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/purchase/categories/{category}](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:495) | [CategoryController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:118) | Lấy chi tiết một danh mục sản phẩm kèm các quan hệ cần cho màn hình xem/sửa. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [GET\|HEAD /api/purchase/units/select](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:496) | [UnitController::select()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:35) | Lấy danh sách đơn vị tính rút gọn để dùng trong danh sách chọn/biểu mẫu. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/purchase/units/{unit}](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Product/ProductForm.vue:496) | [UnitController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:81) | Lấy chi tiết một đơn vị tính kèm các quan hệ cần cho màn hình xem/sửa. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |

<a id="mua-h-ang-resources-js-pages-purchase-supplier-index-vue"></a>

#### Trang Nhà cung cấp — `Purchase/Supplier/Index.vue`

File: [resources/js/Pages/Purchase/Supplier/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/purchase/suppliers](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/Index.vue:335) | [SupplierController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:29) | Lấy danh sách nhà cung cấp thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/purchase/suppliers/all](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/Index.vue:296) | [SupplierController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:100) | Lấy toàn bộ nhà cung cấp thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/purchase/suppliers/{supplier}](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/Index.vue:287) | [SupplierController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:197) | Lấy chi tiết một nhà cung cấp kèm các quan hệ cần cho màn hình xem/sửa. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [PATCH /api/purchase/suppliers/{id}/status](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/Index.vue:363) | [SupplierController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:431) | Đổi trạng thái hoạt động của nhà cung cấp sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến nhà cung cấp trên trang này có thể thay đổi. |

<a id="mua-h-ang-resources-js-pages-purchase-supplier-supplierdetail-vue"></a>

#### Trang Chi tiết nhà cung cấp — `Purchase/Supplier/SupplierDetail.vue`

File: [resources/js/Pages/Purchase/Supplier/SupplierDetail.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/SupplierDetail.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/purchase/orders/{order}](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/SupplierDetail.vue:558) | [PurchaseOrderController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:179) | Lấy chi tiết một đơn mua kèm các quan hệ cần cho màn hình xem/sửa. Hàm còn xử lý số lượng/tồn kho, quy đổi/kiểm tra tiền tệ. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [GET\|HEAD /api/purchase/suppliers/{id}/detail](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/SupplierDetail.vue:572) | [SupplierController::detail()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:202) | Lấy hồ sơ chi tiết nhà cung cấp kèm các quan hệ và số liệu nghiệp vụ liên quan. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |

<a id="mua-h-ang-resources-js-pages-purchase-supplier-supplierform-vue"></a>

#### Trang Biểu mẫu nhà cung cấp — `Purchase/Supplier/SupplierForm.vue`

File: [resources/js/Pages/Purchase/Supplier/SupplierForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/SupplierForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [POST /api/purchase/suppliers](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/SupplierForm.vue:522) | [SupplierController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:134) | Kiểm tra đầu vào và tạo nhà cung cấp mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, liên quan công nợ, quy đổi/kiểm tra tiền tệ. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/purchase/suppliers/{supplier}](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Supplier/SupplierForm.vue:516) | [SupplierController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/SupplierController.php:345) | Kiểm tra và cập nhật nhà cung cấp hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào, liên quan công nợ, quy đổi/kiểm tra tiền tệ. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |

<a id="mua-h-ang-resources-js-pages-purchase-unit-index-vue"></a>

#### Trang Đơn vị tính — `Purchase/Unit/Index.vue`

File: [resources/js/Pages/Purchase/Unit/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Unit/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/purchase/units](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Unit/Index.vue:154) | [UnitController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:19) | Lấy danh sách đơn vị tính thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [PATCH /api/purchase/units/{id}/status](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Unit/Index.vue:227) | [UnitController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:148) | Đổi trạng thái hoạt động của đơn vị tính sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến đơn vị tính trên trang này có thể thay đổi. |

<a id="mua-h-ang-resources-js-pages-purchase-unit-unitform-vue"></a>

#### Trang Biểu mẫu đơn vị tính — `Purchase/Unit/UnitForm.vue`

File: [resources/js/Pages/Purchase/Unit/UnitForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Purchase/Unit/UnitForm.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.


### Controller và Function

- **Controller:** [PurchaseOrderController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-purchaseordercontroller), [SupplierController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-suppliercontroller), [ProductController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-productcontroller), [CategoryController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-categorycontroller), [UnitController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-unitcontroller)
- **Chi tiết Function, validation và ảnh hưởng:** [Mở chỉ mục Function toàn dự án](PROJECT_FUNCTION_INDEX.md).
- **Database:** [Mở Model, bảng và migration của phân hệ](PROJECT_DATABASE_INDEX.md#mua-h-ang).

<a id="mua-h-ang-service"></a>

### Service

| Service | Viết gì? | Hàm công khai |
| --- | --- | --- |
| [PurchaseOrderService](vscode://file/D:/clone/project-base/app/Services/PurchaseOrderService.php:1) | Đóng gói logic nghiệp vụ dùng cho đơn mua. | [updateStatus()](vscode://file/D:/clone/project-base/app/Services/PurchaseOrderService.php:10) |
| [SupplierDebtService](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:1) | Ghi nhận và tính công nợ phải trả nhà cung cấp. | [createFromWarehouseSlip()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:27)<br>[applyAdvanceToOrder()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:59)<br>[createDebt()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:131)<br>[getDebtBalance()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:155)<br>[getOutstandingBalance()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:167)<br>[getOpeningDebtBalance()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:173)<br>[getAdvanceBalance()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:182)<br>[paySupplier()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:220)<br>[payOpeningDebt()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:245)<br>[receiveFromSupplier()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:274)<br>[advanceSupplier()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:298)<br>[refundAdvance()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:318)<br>[getBalance()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:345)<br>[getHistory()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:353) |
| [OrderQuantityValidationService](vscode://file/D:/clone/project-base/app/Services/OrderQuantityValidationService.php:1) | Kiểm tra số lượng đặt/nhập/xuất so với tồn và lượng đã xử lý. | [validate()](vscode://file/D:/clone/project-base/app/Services/OrderQuantityValidationService.php:10) |
| [CompanyCurrencyService](vscode://file/D:/clone/project-base/app/Services/CompanyCurrencyService.php:1) | Lấy tiền tệ công ty và tính tỷ giá quy đổi. | [rate()](vscode://file/D:/clone/project-base/app/Services/CompanyCurrencyService.php:11)<br>[toBase()](vscode://file/D:/clone/project-base/app/Services/CompanyCurrencyService.php:30) |
| [CodeGeneratorService](vscode://file/D:/clone/project-base/app/Services/CodeGeneratorService.php:1) | Sinh mã chứng từ/mã đối tượng theo công ty. | [generate()](vscode://file/D:/clone/project-base/app/Services/CodeGeneratorService.php:9) |
| [NotificationService](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:1) | Tạo, phân phối và quản lý thông báo nội bộ. | [create()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:17)<br>[createForUsers()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:47)<br>[createForCustomerAccount()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:68)<br>[createForPermission()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:92)<br>[createForRole()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:149)<br>[createForHigherRoleUsers()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:188)<br>[createForCompany()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:227)<br>[getUserNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:246)<br>[getUserNotificationsByCategory()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:268)<br>[markAsRead()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:310)<br>[markAllAsRead()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:326)<br>[delete()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:339)<br>[getUnreadCount()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:353)<br>[getUnreadCountByCategory()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:366)<br>[deleteOldNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:396)<br>[getCompanyNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:405) |

<a id="mua-h-ang-model-test"></a>

### Mô hình và kiểm thử

- **Mô hình chính:** [PurchaseOrder](vscode://file/D:/clone/project-base/app/Models/PurchaseOrder.php:1), [PurchaseOrderItem](vscode://file/D:/clone/project-base/app/Models/PurchaseOrderItem.php:1), [Supplier](vscode://file/D:/clone/project-base/app/Models/Supplier.php:1).
- **Kiểm thử liên quan:** [PurchaseToPaymentEndToEndTest.php](vscode://file/D:/clone/project-base/tests/Feature/PurchaseToPaymentEndToEndTest.php:1).

<a id="b-an-h-ang"></a>

## Bán hàng

Quản lý khách hàng, đơn bán, POS và mã giảm giá từ tạo đơn đến duyệt/hủy.

### Các trang trong phân hệ


<a id="b-an-h-ang-resources-js-pages-sale-coupon-couponform-vue"></a>

#### Trang Biểu mẫu mã giảm giá — `Sale/Coupon/CouponForm.vue`

File: [resources/js/Pages/Sale/Coupon/CouponForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/CouponForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/sale/customers/all](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/CouponForm.vue:104) | [CustomerController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:92) | Lấy toàn bộ khách hàng thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/sale/customers/{customer}](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/CouponForm.vue:104) | [CustomerController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:248) | Lấy chi tiết một khách hàng kèm các quan hệ cần cho màn hình xem/sửa. Hàm còn liên quan công nợ. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [POST /api/sale/coupons](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/CouponForm.vue:109) | [CouponController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CouponController.php:68) | Kiểm tra đầu vào và tạo mã giảm giá mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, xử lý mã giảm giá. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/sale/coupons/{coupon}](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/CouponForm.vue:109) | [CouponController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/CouponController.php:84) | Kiểm tra và cập nhật mã giảm giá hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào, xử lý mã giảm giá. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |

<a id="b-an-h-ang-resources-js-pages-sale-coupon-index-vue"></a>

#### Trang Mã giảm giá — `Sale/Coupon/Index.vue`

File: [resources/js/Pages/Sale/Coupon/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/sale/coupons](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/Index.vue:103) | [CouponController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CouponController.php:18) | Lấy danh sách mã giảm giá thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn phân trang kết quả, xử lý mã giảm giá. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [DELETE /api/sale/coupons/{coupon}](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Coupon/Index.vue:130) | [CouponController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/CouponController.php:102) | Kiểm tra điều kiện rồi xóa mã giảm giá và dữ liệu phụ thuộc được xử lý trong hàm. Hàm còn xử lý mã giảm giá. | nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi. |

<a id="b-an-h-ang-resources-js-pages-sale-customer-customerdetail-vue"></a>

#### Trang chi tiết khách hàng — `Sale/Customer/CustomerDetail.vue`

File: [resources/js/Pages/Sale/Customer/CustomerDetail.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/CustomerDetail.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/sale/orders/{order}](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/CustomerDetail.vue:552) | [SalesOrderController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:296) | Lấy chi tiết đơn bán, tính số lượng đã xuất từng dòng (có nhánh riêng cho POS), quy đổi/tính tiền từng dòng và tổng đơn, kèm khách hàng, kho, người tạo/duyệt, địa chỉ và mã giảm giá. | lịch sử đơn của khách hàng và nội dung chi tiết đơn được mở từ hồ sơ khách hàng có thể thay đổi. |
| [GET\|HEAD /api/sale/customers/{id}/detail](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/CustomerDetail.vue:573) | [CustomerController::detail()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:282) | Lấy hồ sơ chi tiết khách hàng kèm các quan hệ và số liệu nghiệp vụ liên quan. | lịch sử đơn của khách hàng và nội dung chi tiết đơn được mở từ hồ sơ khách hàng có thể thay đổi. |

<a id="b-an-h-ang-resources-js-pages-sale-customer-customerform-vue"></a>

#### Trang Biểu mẫu khách hàng — `Sale/Customer/CustomerForm.vue`

File: [resources/js/Pages/Sale/Customer/CustomerForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/CustomerForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [POST /api/sale/customers](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/CustomerForm.vue:504) | [CustomerController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:120) | Kiểm tra đầu vào và tạo khách hàng mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, liên quan công nợ, quy đổi/kiểm tra tiền tệ. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/sale/customers/{customer}](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/CustomerForm.vue:497) | [CustomerController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:179) | Kiểm tra và cập nhật khách hàng hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào, liên quan công nợ, quy đổi/kiểm tra tiền tệ. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |

<a id="b-an-h-ang-resources-js-pages-sale-customer-index-vue"></a>

#### Trang Khách hàng — `Sale/Customer/Index.vue`

File: [resources/js/Pages/Sale/Customer/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/sale/customers](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:344) | [CustomerController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:15) | Lấy danh sách khách hàng thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn liên quan công nợ, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/sale/customers/all](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:390) | [CustomerController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:92) | Lấy toàn bộ khách hàng thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/sale/customers/{customer}](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:319) | [CustomerController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:248) | Lấy chi tiết một khách hàng kèm các quan hệ cần cho màn hình xem/sửa. Hàm còn liên quan công nợ. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [PATCH /api/sale/customers/{customer}/status](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Customer/Index.vue:372) | [CustomerController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:399) | Đổi trạng thái hoạt động của khách hàng sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến khách hàng trên trang này có thể thay đổi. |

<a id="b-an-h-ang-resources-js-pages-sale-dashboard-vue"></a>

#### Trang Bảng điều khiển — `Sale/Dashboard.vue`

File: [resources/js/Pages/Sale/Dashboard.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Dashboard.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="b-an-h-ang-resources-js-pages-sale-order-index-vue"></a>

#### Trang Đơn bán — `Sale/Order/Index.vue`

File: [resources/js/Pages/Sale/Order/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/sale/orders](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:425) | [SalesOrderController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:40) | Lấy danh sách đơn bán (loại POS chưa thanh toán), lọc theo trạng thái, điều kiện giao dịch, mã đơn và khách hàng; quy đổi giá/VAT/phí vận chuyển/giảm giá sang tiền tệ công ty rồi phân trang. | các dòng đơn, bộ lọc, phân trang và giá/VAT/phí/giảm giá/tổng tiền trong danh sách có thể thay đổi. |
| [GET\|HEAD /api/sale/orders/{order}](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:414) | [SalesOrderController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:296) | Lấy chi tiết đơn bán, tính số lượng đã xuất từng dòng (có nhánh riêng cho POS), quy đổi/tính tiền từng dòng và tổng đơn, kèm khách hàng, kho, người tạo/duyệt, địa chỉ và mã giảm giá. | modal chi tiết và dữ liệu dùng để mở form sửa có thể sai/thiếu hoặc không mở được. |
| [POST /api/sale/orders/{id}/approve](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:524) | [SalesOrderController::approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:776) | Chuyển đơn `pending` sang `approved`, ghi nhận mã giảm giá đã dùng (trừ kênh cửa hàng trực tuyến), ghi nhật ký hoạt động và thông báo người tạo để đơn đi tiếp sang quy trình kho. | nút duyệt, trạng thái đơn sau duyệt và thời điểm đơn đủ điều kiện chuyển sang kho có thể thay đổi. |
| [POST /api/sale/orders/{id}/cancel](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:511) | [SalesOrderController::cancel()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:824) | Hủy đơn `draft/pending` chưa có phiếu xuất kho; bắt buộc lý do, hoàn tác mã giảm giá, ghi nhật ký và thông báo người tạo/khách cửa hàng trực tuyến. | nút hủy, thông báo lỗi và trạng thái đơn sau hủy có thể thay đổi. |
| [GET\|HEAD /api/sale/customers/all](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:442) | [CustomerController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:92) | Lấy toàn bộ khách hàng thuộc công ty hiện tại ở dạng rút gọn. | dữ liệu lựa chọn hoặc hành động liên quan đến khách hàng trên trang đơn bán có thể thay đổi. |
| [GET\|HEAD /api/sale/customers/{customer}](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/Index.vue:442) | [CustomerController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:248) | Lấy chi tiết một khách hàng kèm các quan hệ cần cho màn hình xem/sửa. Hàm còn liên quan công nợ. | modal chi tiết và dữ liệu dùng để mở form sửa có thể sai/thiếu hoặc không mở được. |

<a id="b-an-h-ang-resources-js-pages-sale-order-saleorderdetail-vue"></a>

#### Trang Chi tiết đơn bán — `Sale/Order/SaleOrderDetail.vue`

File: [resources/js/Pages/Sale/Order/SaleOrderDetail.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderDetail.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="b-an-h-ang-resources-js-pages-sale-order-saleorderform-vue"></a>

#### Form tạo/sửa đơn bán — `Sale/Order/SaleOrderForm.vue`

File: [resources/js/Pages/Sale/Order/SaleOrderForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [POST /api/sale/orders](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:1002) | [SalesOrderController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:363) | Kiểm tra dữ liệu và tạo đơn bán cùng các dòng sản phẩm trong giao dịch cơ sở dữ liệu; kiểm tra tồn, tính tỷ giá/giá trị tiền tệ công ty, áp mã giảm giá và gửi thông báo cho kế toán. | việc gửi biểu mẫu tạo đơn, lỗi kiểm tra dữ liệu, số tiền/mã giảm giá được lưu và đơn mới xuất hiện trong danh sách có thể thay đổi. |
| [PUT /api/sale/orders/{order}](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:1000) | [SalesOrderController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:562) | Chỉ sửa đơn `draft/pending`: kiểm tra dữ liệu, tính lại tỷ giá, xóa và tạo lại toàn bộ dòng sản phẩm, hoàn tác rồi áp lại mã giảm giá, sau đó gửi thông báo trong giao dịch cơ sở dữ liệu. | dữ liệu nạp vào biểu mẫu, lỗi kiểm tra dữ liệu và kết quả lưu đơn/dòng sản phẩm/mã giảm giá có thể thay đổi. |
| [GET\|HEAD /api/sale/customers/{id}/detail](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:639) | [CustomerController::detail()](vscode://file/D:/clone/project-base/app/Http/Controllers/CustomerController.php:282) | Lấy hồ sơ chi tiết khách hàng kèm các quan hệ và số liệu nghiệp vụ liên quan. | dữ liệu nạp vào biểu mẫu, lỗi kiểm tra dữ liệu và kết quả lưu đơn/dòng sản phẩm/mã giảm giá có thể thay đổi. |
| [GET\|HEAD /api/sale/coupons/active](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Order/SaleOrderForm.vue:933) | [CouponController::active()](vscode://file/D:/clone/project-base/app/Http/Controllers/CouponController.php:52) | Lấy các mã giảm giá đang hoạt động để dùng khi nhập liệu. | dữ liệu nạp vào biểu mẫu, lỗi kiểm tra dữ liệu và kết quả lưu đơn/dòng sản phẩm/mã giảm giá có thể thay đổi. |

<a id="b-an-h-ang-resources-js-pages-sale-pos-index-vue"></a>

#### Trang bán hàng POS — `Sale/Pos/Index.vue`

File: [resources/js/Pages/Sale/Pos/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/sale/pos/drafts](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:488) | [PosController::drafts()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:25) | Lấy danh sách đơn POS nháp của công ty hiện tại. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/sale/pos/options](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:486) | [PosController::options()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:39) | Lấy sản phẩm, khách hàng, kho, tiền tệ và dữ liệu chọn dùng cho POS. | dữ liệu hoặc hành động liên quan đến đơn bán POS trên trang này có thể thay đổi. |
| [POST /api/sale/pos/drafts](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:350) | [PosController::createDraft()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:80) | Tạo đơn POS nháp để giữ nội dung giỏ hàng trước khi thanh toán. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [POST /api/sale/pos/customers](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:436) | [PosController::storeCustomer()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:96) | Thực hiện nghiệp vụ “store Customer” cho đơn bán POS. Bao gồm: kiểm tra dữ liệu đầu vào, liên quan công nợ, quy đổi/kiểm tra tiền tệ. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/sale/pos/drafts/{order}](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:394) | [PosController::updateDraft()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:121) | Cập nhật sản phẩm, số lượng và thông tin của đơn POS nháp. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |
| [DELETE /api/sale/pos/drafts/{order}](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:414) | [PosController::cancelDraft()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:189) | Hủy đơn POS nháp và giải phóng dữ liệu tạm liên quan. | nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi. |
| [POST /api/sale/pos/orders](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:456) | [PosController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:202) | Kiểm tra đầu vào và tạo đơn bán POS mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, chạy trong giao dịch cơ sở dữ liệu, xử lý số lượng/tồn kho, liên quan công nợ. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [GET\|HEAD /api/sale/pos/history](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:480) | [PosController::history()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:339) | Lấy lịch sử các đơn POS đã hoàn tất. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/sale/pos/orders/{order}](vscode://file/D:/clone/project-base/resources/js/Pages/Sale/Pos/Index.vue:481) | [PosController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:348) | Lấy chi tiết một đơn bán POS kèm các quan hệ cần cho màn hình xem/sửa. Hàm còn quy đổi/kiểm tra tiền tệ. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |


### Controller và Function

- **Controller:** [SalesOrderController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-salesordercontroller), [CustomerController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-customercontroller), [CouponController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-couponcontroller), [PosController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-poscontroller)
- **Chi tiết Function, validation và ảnh hưởng:** [Mở chỉ mục Function toàn dự án](PROJECT_FUNCTION_INDEX.md).
- **Database:** [Mở Model, bảng và migration của phân hệ](PROJECT_DATABASE_INDEX.md#b-an-h-ang).

<a id="b-an-h-ang-service"></a>

### Service

| Service | Viết gì? | Hàm công khai |
| --- | --- | --- |
| [CustomerDebtService](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:1) | Ghi nhận và tính công nợ phải thu khách hàng. | [createFromSalesOrder()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:23)<br>[createFromWarehouseSlip()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:46)<br>[createDebt()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:110)<br>[receivePayment()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:140)<br>[receiveOpeningDebtPayment()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:166)<br>[getOpeningDebtBalance()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:188)<br>[receiveAdvance()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:198)<br>[refundAdvance()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:213)<br>[getAdvanceBalance()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:228)<br>[refundToCustomer()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:248)<br>[getBalance()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:281)<br>[getHistory()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:292) |
| [CouponService](vscode://file/D:/clone/project-base/app/Services/CouponService.php:1) | Kiểm tra, áp dụng, ghi nhận sử dụng và hoàn tác mã giảm giá theo vòng đời đơn. | [eligibility()](vscode://file/D:/clone/project-base/app/Services/CouponService.php:13)<br>[resolve()](vscode://file/D:/clone/project-base/app/Services/CouponService.php:56)<br>[applyToOrder()](vscode://file/D:/clone/project-base/app/Services/CouponService.php:77)<br>[redeemForOrder()](vscode://file/D:/clone/project-base/app/Services/CouponService.php:100)<br>[removeFromOrder()](vscode://file/D:/clone/project-base/app/Services/CouponService.php:111)<br>[reverseForOrder()](vscode://file/D:/clone/project-base/app/Services/CouponService.php:118) |
| [OrderQuantityValidationService](vscode://file/D:/clone/project-base/app/Services/OrderQuantityValidationService.php:1) | Kiểm tra số lượng đặt/nhập/xuất so với tồn và lượng đã xử lý. | [validate()](vscode://file/D:/clone/project-base/app/Services/OrderQuantityValidationService.php:10) |
| [CompanyCurrencyService](vscode://file/D:/clone/project-base/app/Services/CompanyCurrencyService.php:1) | Lấy tiền tệ công ty và tính tỷ giá quy đổi. | [rate()](vscode://file/D:/clone/project-base/app/Services/CompanyCurrencyService.php:11)<br>[toBase()](vscode://file/D:/clone/project-base/app/Services/CompanyCurrencyService.php:30) |
| [CodeGeneratorService](vscode://file/D:/clone/project-base/app/Services/CodeGeneratorService.php:1) | Sinh mã chứng từ/mã đối tượng theo công ty. | [generate()](vscode://file/D:/clone/project-base/app/Services/CodeGeneratorService.php:9) |
| [InventoryMovementService](vscode://file/D:/clone/project-base/app/Services/InventoryMovementService.php:1) | Ghi biến động tăng/giảm/chuyển tồn kho và liên kết chứng từ nguồn. | [record()](vscode://file/D:/clone/project-base/app/Services/InventoryMovementService.php:11) |
| [NotificationService](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:1) | Tạo, phân phối và quản lý thông báo nội bộ. | [create()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:17)<br>[createForUsers()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:47)<br>[createForCustomerAccount()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:68)<br>[createForPermission()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:92)<br>[createForRole()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:149)<br>[createForHigherRoleUsers()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:188)<br>[createForCompany()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:227)<br>[getUserNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:246)<br>[getUserNotificationsByCategory()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:268)<br>[markAsRead()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:310)<br>[markAllAsRead()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:326)<br>[delete()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:339)<br>[getUnreadCount()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:353)<br>[getUnreadCountByCategory()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:366)<br>[deleteOldNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:396)<br>[getCompanyNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:405) |

<a id="b-an-h-ang-model-test"></a>

### Mô hình và kiểm thử

- **Mô hình chính:** [SalesOrder](vscode://file/D:/clone/project-base/app/Models/SalesOrder.php:1), [SalesOrderItem](vscode://file/D:/clone/project-base/app/Models/SalesOrderItem.php:1), [Customer](vscode://file/D:/clone/project-base/app/Models/Customer.php:1), [PosCoupon](vscode://file/D:/clone/project-base/app/Models/PosCoupon.php:1).
- **Kiểm thử liên quan:** [CouponManagementTest.php](vscode://file/D:/clone/project-base/tests/Feature/CouponManagementTest.php:1), [DepartmentPositionFlowTest.php](vscode://file/D:/clone/project-base/tests/Feature/DepartmentPositionFlowTest.php:1), [PosCheckoutTest.php](vscode://file/D:/clone/project-base/tests/Feature/PosCheckoutTest.php:1), [SalesOrderWorkflowTest.php](vscode://file/D:/clone/project-base/tests/Feature/SalesOrderWorkflowTest.php:1).

<a id="ca-h-ang-trc-tuyn"></a>

## Cửa hàng trực tuyến

Cửa hàng trực tuyến, thanh toán, tài khoản khách hàng, địa chỉ, đơn và thông báo cho khách mua hàng.

### Các trang trong phân hệ


<a id="ca-h-ang-trc-tuyn-resources-js-pages-storefront-account-vue"></a>

#### Trang tài khoản cửa hàng trực tuyến — `Storefront/Account.vue`

File: [resources/js/Pages/Storefront/Account.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /shop/{company}/my-account](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Account.vue:1) | [StorefrontController::accountPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:43) | Thực hiện nghiệp vụ “account Page” cho cửa hàng trực tuyến. | dữ liệu hoặc hành động liên quan đến cửa hàng trực tuyến trên trang này có thể thay đổi. |

<a id="ca-h-ang-trc-tuyn-resources-js-pages-storefront-cart-vue"></a>

#### Trang Giỏ hàng — `Storefront/Cart.vue`

File: [resources/js/Pages/Storefront/Cart.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Cart.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /shop/{company}/cart](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Cart.vue:1) | [StorefrontController::cartPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:58) | Thực hiện nghiệp vụ “cart Page” cho cửa hàng trực tuyến. | dữ liệu hoặc hành động liên quan đến cửa hàng trực tuyến trên trang này có thể thay đổi. |

<a id="ca-h-ang-trc-tuyn-resources-js-pages-storefront-checkout-vue"></a>

#### Trang thanh toán cửa hàng trực tuyến — `Storefront/Checkout.vue`

File: [resources/js/Pages/Storefront/Checkout.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /shop/{company}/checkout](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:1) | [StorefrontController::checkoutPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:65) | Thực hiện nghiệp vụ “checkout Page” cho cửa hàng trực tuyến. | dữ liệu hoặc hành động liên quan đến cửa hàng trực tuyến trên trang này có thể thay đổi. |
| [GET\|HEAD /shop/{company}/vouchers](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:534) | [StorefrontController::vouchers()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:128) | Lấy mã giảm giá hợp lệ mà khách hàng có thể dùng trên cửa hàng trực tuyến. | dữ liệu hoặc hành động liên quan đến cửa hàng trực tuyến trên trang này có thể thay đổi. |
| [POST /shop/{company}/checkout](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:496) | [StorefrontController::checkout()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:140) | Kiểm tra giỏ hàng cửa hàng trực tuyến, tạo đơn bán và áp dụng thông tin giao hàng/mã giảm giá. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [GET\|HEAD /shop/{company}/account/me](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:535) | [StorefrontAccountController::me()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:185) | Thực hiện nghiệp vụ “me” cho tài khoản khách hàng cửa hàng trực tuyến. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [GET\|HEAD /shop/{company}/account/addresses](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:544) | [StorefrontAccountController::addresses()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:400) | Thực hiện nghiệp vụ “addresses” cho tài khoản khách hàng cửa hàng trực tuyến. | dữ liệu hoặc hành động liên quan đến tài khoản khách hàng cửa hàng trực tuyến trên trang này có thể thay đổi. |
| [POST /shop/{company}/account/addresses](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Checkout.vue:433) | [StorefrontAccountController::storeAddress()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:405) | Thực hiện nghiệp vụ “store Address” cho tài khoản khách hàng cửa hàng trực tuyến. Bao gồm: kiểm tra dữ liệu đầu vào. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |

<a id="ca-h-ang-trc-tuyn-resources-js-pages-storefront-directory-vue"></a>

#### Trang Danh bạ cửa hàng — `Storefront/Directory.vue`

File: [resources/js/Pages/Storefront/Directory.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Directory.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /shop](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Directory.vue:1) | [StorefrontController::directory()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:27) | Thực hiện nghiệp vụ “directory” cho cửa hàng trực tuyến. | dữ liệu hoặc hành động liên quan đến cửa hàng trực tuyến trên trang này có thể thay đổi. |

<a id="ca-h-ang-trc-tuyn-resources-js-pages-storefront-notifications-vue"></a>

#### Trang Thông báo — `Storefront/Notifications.vue`

File: [resources/js/Pages/Storefront/Notifications.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Notifications.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /shop/{company}/my-account/notifications](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Notifications.vue:1) | [StorefrontAccountController::notificationPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:114) | Thực hiện nghiệp vụ “notification Page” cho tài khoản khách hàng cửa hàng trực tuyến. Bao gồm: gửi thông báo. | số thông báo, trạng thái đã đọc và danh sách thông báo có thể thay đổi. |

<a id="ca-h-ang-trc-tuyn-resources-js-pages-storefront-orderdetail-vue"></a>

#### Trang chi tiết đơn cửa hàng trực tuyến — `Storefront/OrderDetail.vue`

File: [resources/js/Pages/Storefront/OrderDetail.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/OrderDetail.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /shop/{company}/my-account/orders/{code}](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/OrderDetail.vue:1) | [StorefrontAccountController::orderPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:27) | Thực hiện nghiệp vụ “order Page” cho tài khoản khách hàng cửa hàng trực tuyến. Bao gồm: xử lý số lượng/tồn kho, quy đổi/kiểm tra tiền tệ, xử lý mã giảm giá. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [POST /shop/{company}/account/orders/{code}/cancel](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/OrderDetail.vue:190) | [StorefrontAccountController::cancelOrder()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:368) | Cho khách hàng cửa hàng trực tuyến hủy đơn hợp lệ, ghi lý do và hoàn tác mã giảm giá nếu cần. | nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi. |

<a id="ca-h-ang-trc-tuyn-resources-js-pages-storefront-product-vue"></a>

#### Trang Sản phẩm — `Storefront/Product.vue`

File: [resources/js/Pages/Storefront/Product.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Product.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /shop/{company}/product/{product}](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Product.vue:1) | [StorefrontController::productPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:50) | Thực hiện nghiệp vụ “product Page” cho cửa hàng trực tuyến. | dữ liệu hoặc hành động liên quan đến cửa hàng trực tuyến trên trang này có thể thay đổi. |
| [GET\|HEAD /shop/{company}/products/{product}](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Product.vue:152) | [StorefrontController::product()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:118) | Thực hiện nghiệp vụ “product” cho cửa hàng trực tuyến. Bao gồm: xử lý số lượng/tồn kho. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |

<a id="ca-h-ang-trc-tuyn-resources-js-pages-storefront-shop-vue"></a>

#### Trang Cửa hàng — `Storefront/Shop.vue`

File: [resources/js/Pages/Storefront/Shop.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Shop.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /shop/{company}](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Shop.vue:1) | [StorefrontController::shop()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:36) | Thực hiện nghiệp vụ “shop” cho cửa hàng trực tuyến. | dữ liệu hoặc hành động liên quan đến cửa hàng trực tuyến trên trang này có thể thay đổi. |
| [GET\|HEAD /shop/{company}/products](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Shop.vue:407) | [StorefrontController::products()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:79) | Lấy danh sách sản phẩm cửa hàng trực tuyến có lọc và phân trang. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |

<a id="ca-h-ang-trc-tuyn-resources-js-pages-storefront-success-vue"></a>

#### Trang Đặt hàng thành công — `Storefront/Success.vue`

File: [resources/js/Pages/Storefront/Success.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Success.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /shop/{company}/order-success](vscode://file/D:/clone/project-base/resources/js/Pages/Storefront/Success.vue:1) | [StorefrontController::successPage()](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:72) | Thực hiện nghiệp vụ “success Page” cho cửa hàng trực tuyến. | dữ liệu hoặc hành động liên quan đến cửa hàng trực tuyến trên trang này có thể thay đổi. |


### Controller và Function

- **Controller:** [StorefrontController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-storefrontcontroller), [StorefrontAccountController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-storefrontaccountcontroller)
- **Chi tiết Function, validation và ảnh hưởng:** [Mở chỉ mục Function toàn dự án](PROJECT_FUNCTION_INDEX.md).
- **Database:** [Mở Model, bảng và migration của phân hệ](PROJECT_DATABASE_INDEX.md#ca-h-ang-trc-tuyn).

<a id="ca-h-ang-trc-tuyn-service"></a>

### Service

| Service | Viết gì? | Hàm công khai |
| --- | --- | --- |
| [CouponService](vscode://file/D:/clone/project-base/app/Services/CouponService.php:1) | Kiểm tra, áp dụng, ghi nhận sử dụng và hoàn tác mã giảm giá theo vòng đời đơn. | [eligibility()](vscode://file/D:/clone/project-base/app/Services/CouponService.php:13)<br>[resolve()](vscode://file/D:/clone/project-base/app/Services/CouponService.php:56)<br>[applyToOrder()](vscode://file/D:/clone/project-base/app/Services/CouponService.php:77)<br>[redeemForOrder()](vscode://file/D:/clone/project-base/app/Services/CouponService.php:100)<br>[removeFromOrder()](vscode://file/D:/clone/project-base/app/Services/CouponService.php:111)<br>[reverseForOrder()](vscode://file/D:/clone/project-base/app/Services/CouponService.php:118) |
| [CodeGeneratorService](vscode://file/D:/clone/project-base/app/Services/CodeGeneratorService.php:1) | Sinh mã chứng từ/mã đối tượng theo công ty. | [generate()](vscode://file/D:/clone/project-base/app/Services/CodeGeneratorService.php:9) |
| [NotificationService](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:1) | Tạo, phân phối và quản lý thông báo nội bộ. | [create()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:17)<br>[createForUsers()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:47)<br>[createForCustomerAccount()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:68)<br>[createForPermission()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:92)<br>[createForRole()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:149)<br>[createForHigherRoleUsers()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:188)<br>[createForCompany()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:227)<br>[getUserNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:246)<br>[getUserNotificationsByCategory()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:268)<br>[markAsRead()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:310)<br>[markAllAsRead()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:326)<br>[delete()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:339)<br>[getUnreadCount()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:353)<br>[getUnreadCountByCategory()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:366)<br>[deleteOldNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:396)<br>[getCompanyNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:405) |

<a id="ca-h-ang-trc-tuyn-model-test"></a>

### Mô hình và kiểm thử

- **Mô hình chính:** [CustomerAccount](vscode://file/D:/clone/project-base/app/Models/CustomerAccount.php:1), [CustomerAddress](vscode://file/D:/clone/project-base/app/Models/CustomerAddress.php:1), [SalesOrder](vscode://file/D:/clone/project-base/app/Models/SalesOrder.php:1), [SalesOrderItem](vscode://file/D:/clone/project-base/app/Models/SalesOrderItem.php:1).
- **Kiểm thử liên quan:** [StorefrontTest.php](vscode://file/D:/clone/project-base/tests/Feature/StorefrontTest.php:1).

<a id="kho"></a>

## Kho

Quản lý kho, sản phẩm, phiếu nhập/xuất, chuyển kho và biến động tồn.

### Các trang trong phân hệ


<a id="kho-resources-js-pages-warehouse-category-categoryform-vue"></a>

#### Trang Biểu mẫu danh mục — `Warehouse/Category/CategoryForm.vue`

File: [resources/js/Pages/Warehouse/Category/CategoryForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Category/CategoryForm.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="kho-resources-js-pages-warehouse-category-index-vue"></a>

#### Trang Danh mục — `Warehouse/Category/Index.vue`

File: [resources/js/Pages/Warehouse/Category/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Category/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/warehouse/categories](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Category/Index.vue:205) | [CategoryController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:11) | Lấy danh sách danh mục sản phẩm thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [PATCH /api/warehouse/categories/{id}/status](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Category/Index.vue:239) | [CategoryController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:215) | Đổi trạng thái hoạt động của danh mục sản phẩm sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến danh mục sản phẩm trên trang này có thể thay đổi. |

<a id="kho-resources-js-pages-warehouse-dashboard-vue"></a>

#### Trang Bảng điều khiển — `Warehouse/Dashboard.vue`

File: [resources/js/Pages/Warehouse/Dashboard.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Dashboard.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="kho-resources-js-pages-warehouse-index-vue"></a>

#### Trang Kho — `Warehouse/Index.vue`

File: [resources/js/Pages/Warehouse/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/warehouses](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Index.vue:238) | [WarehouseController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:24) | Lấy danh sách kho thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn kiểm tra dữ liệu đầu vào, xử lý số lượng/tồn kho, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [PATCH /api/warehouses/{warehouse}/status](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Index.vue:281) | [WarehouseController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:231) | Đổi trạng thái hoạt động của kho sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến kho trên trang này có thể thay đổi. |

<a id="kho-resources-js-pages-warehouse-inventorymovement-index-vue"></a>

#### Trang Biến động tồn kho — `Warehouse/InventoryMovement/Index.vue`

File: [resources/js/Pages/Warehouse/InventoryMovement/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/InventoryMovement/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/warehouses/all](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/InventoryMovement/Index.vue:132) | [WarehouseController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:159) | Lấy toàn bộ kho thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/warehouses/{warehouse}](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/InventoryMovement/Index.vue:132) | [WarehouseController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:171) | Lấy chi tiết một kho kèm các quan hệ cần cho màn hình xem/sửa. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [GET\|HEAD /api/warehouse/inventory-movements](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/InventoryMovement/Index.vue:112) | [InventoryMovementController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/InventoryMovementController.php:13) | Lấy danh sách biến động tồn kho thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn kiểm tra dữ liệu đầu vào, phân trang kết quả, xử lý số lượng/tồn kho, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |

<a id="kho-resources-js-pages-warehouse-order-index-vue"></a>

#### Trang Đơn chờ kho — `Warehouse/Order/Index.vue`

File: [resources/js/Pages/Warehouse/Order/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Order/Index.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="kho-resources-js-pages-warehouse-product-index-vue"></a>

#### Trang Sản phẩm — `Warehouse/Product/Index.vue`

File: [resources/js/Pages/Warehouse/Product/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/warehouses/all](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:403) | [WarehouseController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:159) | Lấy toàn bộ kho thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/warehouses/{warehouse}](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:403) | [WarehouseController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:171) | Lấy chi tiết một kho kèm các quan hệ cần cho màn hình xem/sửa. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [GET\|HEAD /api/warehouse/products](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:308) | [ProductController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:51) | Lấy danh sách sản phẩm thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn xử lý số lượng/tồn kho, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [DELETE /api/warehouse/products/{product}](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:379) | [ProductController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:427) | Kiểm tra điều kiện rồi xóa sản phẩm và dữ liệu phụ thuộc được xử lý trong hàm. | nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi. |
| [PATCH /api/warehouse/products/{id}/status](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/Index.vue:390) | [ProductController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:445) | Đổi trạng thái hoạt động của sản phẩm sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến sản phẩm trên trang này có thể thay đổi. |

<a id="kho-resources-js-pages-warehouse-product-productdetail-vue"></a>

#### Trang Chi tiết sản phẩm — `Warehouse/Product/ProductDetail.vue`

File: [resources/js/Pages/Warehouse/Product/ProductDetail.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductDetail.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="kho-resources-js-pages-warehouse-product-productform-vue"></a>

#### Trang Biểu mẫu sản phẩm — `Warehouse/Product/ProductForm.vue`

File: [resources/js/Pages/Warehouse/Product/ProductForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [POST /api/warehouse/products](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:473) | [ProductController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:211) | Kiểm tra đầu vào và tạo sản phẩm mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, xử lý số lượng/tồn kho. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/warehouse/products/{product}](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:471) | [ProductController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:318) | Kiểm tra và cập nhật sản phẩm hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào, xử lý số lượng/tồn kho. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |
| [GET\|HEAD /api/warehouse/categories/select](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:499) | [CategoryController::select()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:82) | Lấy danh sách danh mục sản phẩm rút gọn để dùng trong danh sách chọn/biểu mẫu. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/warehouse/categories/{category}](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:499) | [CategoryController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/CategoryController.php:118) | Lấy chi tiết một danh mục sản phẩm kèm các quan hệ cần cho màn hình xem/sửa. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [GET\|HEAD /api/warehouse/units/select](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:500) | [UnitController::select()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:35) | Lấy danh sách đơn vị tính rút gọn để dùng trong danh sách chọn/biểu mẫu. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/warehouse/units/{unit}](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Product/ProductForm.vue:500) | [UnitController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:81) | Lấy chi tiết một đơn vị tính kèm các quan hệ cần cho màn hình xem/sửa. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |

<a id="kho-resources-js-pages-warehouse-slip-index-vue"></a>

#### Trang Phiếu kho — `Warehouse/Slip/Index.vue`

File: [resources/js/Pages/Warehouse/Slip/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/warehouses/all](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:722) | [WarehouseController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:159) | Lấy toàn bộ kho thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/warehouses/{warehouse}](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:722) | [WarehouseController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:171) | Lấy chi tiết một kho kèm các quan hệ cần cho màn hình xem/sửa. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [POST /api/warehouse/slips/{id}/confirm-delivery](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:491) | [WarehouseSlipController::confirmDelivery()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:531) | Xác nhận trạng thái giao hàng của phiếu xuất và cập nhật đơn liên quan. | dữ liệu hoặc hành động liên quan đến phiếu nhập/xuất kho trên trang này có thể thay đổi. |
| [GET\|HEAD /api/warehouse/slips/shipping/partners](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:389) | [WarehouseSlipController::shippingPartners()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:579) | Lấy danh sách đối tác vận chuyển dùng khi giao hàng. | dữ liệu hoặc hành động liên quan đến phiếu nhập/xuất kho trên trang này có thể thay đổi. |
| [POST /api/warehouse/slips/shipping/partners](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:658) | [WarehouseSlipController::storeShippingPartner()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:585) | Kiểm tra dữ liệu và tạo nhanh đối tác vận chuyển. | dữ liệu hoặc hành động liên quan đến phiếu nhập/xuất kho trên trang này có thể thay đổi. |
| [PUT /api/warehouse/slips/{id}/shipping](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:673) | [WarehouseSlipController::assignShipping()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:600) | Gán đối tác vận chuyển và thông tin giao hàng cho phiếu xuất. | dữ liệu hoặc hành động liên quan đến phiếu nhập/xuất kho trên trang này có thể thay đổi. |
| [POST /api/warehouse/slips/{id}/request-delivery-return](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:533) | [WarehouseSlipController::requestDeliveryReturn()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:640) | Ghi nhận yêu cầu hoàn hàng giao thất bại và chuyển sang luồng xử lý hoàn. | dữ liệu hoặc hành động liên quan đến phiếu nhập/xuất kho trên trang này có thể thay đổi. |
| [POST /api/warehouse/slips/{id}/receive-delivery-return](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:566) | [WarehouseSlipController::receiveDeliveryReturn()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:700) | Xác nhận kho đã nhận lại hàng hoàn để chờ kế toán duyệt. | dữ liệu hoặc hành động liên quan đến phiếu nhập/xuất kho trên trang này có thể thay đổi. |
| [POST /api/warehouse/slips/{id}/accountant-approve-delivery-return](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:598) | [WarehouseSlipController::accountantApproveDeliveryReturn()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:720) | Kế toán duyệt hàng hoàn và ghi nhận biến động tồn kho tương ứng. | dữ liệu hoặc hành động liên quan đến phiếu nhập/xuất kho trên trang này có thể thay đổi. |
| [POST /api/warehouse/slips/{id}/approve](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:423) | [WarehouseSlipController::approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:818) | Duyệt phiếu nhập/xuất kho, cập nhật trạng thái và kích hoạt bước nghiệp vụ tiếp theo. Hàm còn chạy trong giao dịch cơ sở dữ liệu, gửi thông báo. | nút duyệt/gửi/từ chối, trạng thái hiển thị và khả năng chứng từ đi tiếp sang bước nghiệp vụ sau sẽ thay đổi. |
| [POST /api/warehouse/slips/{id}/accountant-approve](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:456) | [WarehouseSlipController::accountantApprove()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:862) | Kế toán duyệt phiếu kho, cập nhật tồn kho/công nợ và hoàn tất trạng thái liên quan. | nút duyệt/gửi/từ chối, trạng thái hiển thị và khả năng chứng từ đi tiếp sang bước nghiệp vụ sau sẽ thay đổi. |
| [POST /api/warehouse/slips/{id}/reject](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Index.vue:630) | [WarehouseSlipController::reject()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:1031) | Từ chối phiếu nhập/xuất kho, ghi lý do/trạng thái và thông báo bên liên quan nếu có. Hàm còn gửi thông báo. | nút duyệt/gửi/từ chối, trạng thái hiển thị và khả năng chứng từ đi tiếp sang bước nghiệp vụ sau sẽ thay đổi. |

<a id="kho-resources-js-pages-warehouse-slip-purchasecreate-vue"></a>

#### Trang tạo phiếu nhập — `Warehouse/Slip/Purchasecreate.vue`

File: [resources/js/Pages/Warehouse/Slip/Purchasecreate.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/warehouses/all](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:405) | [WarehouseController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:159) | Lấy toàn bộ kho thuộc công ty hiện tại ở dạng rút gọn. | dữ liệu đơn khởi tạo phiếu, kho được chọn, lượng đã nhập/còn được nhập và nút lưu phiếu có thể thay đổi. |
| [GET\|HEAD /api/warehouses/{warehouse}](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:405) | [WarehouseController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:171) | Lấy chi tiết một kho kèm các quan hệ cần cho màn hình xem/sửa. | dữ liệu đơn khởi tạo phiếu, kho được chọn, lượng đã nhập/còn được nhập và nút lưu phiếu có thể thay đổi. |
| [GET\|HEAD /api/warehouse/slips](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:396) | [WarehouseSlipController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:32) | Lấy danh sách phiếu nhập/xuất kho thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. | dữ liệu đơn khởi tạo phiếu, kho được chọn, lượng đã nhập/còn được nhập và nút lưu phiếu có thể thay đổi. |
| [POST /api/warehouse/slips](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:456) | [WarehouseSlipController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:182) | Kiểm tra đầu vào và tạo phiếu nhập/xuất kho mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, chạy trong giao dịch cơ sở dữ liệu, xử lý số lượng/tồn kho. | dữ liệu đơn khởi tạo phiếu, kho được chọn, lượng đã nhập/còn được nhập và nút lưu phiếu có thể thay đổi. |
| [POST /api/warehouse/slips/{id}/approve](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:264) | [WarehouseSlipController::approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:818) | Duyệt phiếu nhập/xuất kho, cập nhật trạng thái và kích hoạt bước nghiệp vụ tiếp theo. Hàm còn chạy trong giao dịch cơ sở dữ liệu, gửi thông báo. | dữ liệu đơn khởi tạo phiếu, kho được chọn, lượng đã nhập/còn được nhập và nút lưu phiếu có thể thay đổi. |
| [POST /api/warehouse/slips/{id}/reject](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Purchasecreate.vue:296) | [WarehouseSlipController::reject()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:1031) | Từ chối phiếu nhập/xuất kho, ghi lý do/trạng thái và thông báo bên liên quan nếu có. Hàm còn gửi thông báo. | dữ liệu đơn khởi tạo phiếu, kho được chọn, lượng đã nhập/còn được nhập và nút lưu phiếu có thể thay đổi. |

<a id="kho-resources-js-pages-warehouse-slip-salecreate-vue"></a>

#### Trang tạo phiếu xuất — `Warehouse/Slip/Salecreate.vue`

File: [resources/js/Pages/Warehouse/Slip/Salecreate.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Salecreate.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/warehouse/slips](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Salecreate.vue:352) | [WarehouseSlipController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:32) | Lấy danh sách phiếu nhập/xuất kho thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. | dữ liệu đơn khởi tạo phiếu, kho được chọn, lượng đã xuất/còn được xuất và nút lưu phiếu có thể thay đổi. |
| [POST /api/warehouse/slips](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Salecreate.vue:418) | [WarehouseSlipController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:182) | Kiểm tra đầu vào và tạo phiếu nhập/xuất kho mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, chạy trong giao dịch cơ sở dữ liệu, xử lý số lượng/tồn kho. | dữ liệu đơn khởi tạo phiếu, kho được chọn, lượng đã xuất/còn được xuất và nút lưu phiếu có thể thay đổi. |
| [POST /api/warehouse/slips/{id}/approve](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Salecreate.vue:509) | [WarehouseSlipController::approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:818) | Duyệt phiếu nhập/xuất kho, cập nhật trạng thái và kích hoạt bước nghiệp vụ tiếp theo. Hàm còn chạy trong giao dịch cơ sở dữ liệu, gửi thông báo. | dữ liệu đơn khởi tạo phiếu, kho được chọn, lượng đã xuất/còn được xuất và nút lưu phiếu có thể thay đổi. |
| [POST /api/warehouse/slips/{id}/reject](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/Salecreate.vue:546) | [WarehouseSlipController::reject()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:1031) | Từ chối phiếu nhập/xuất kho, ghi lý do/trạng thái và thông báo bên liên quan nếu có. Hàm còn gửi thông báo. | dữ liệu đơn khởi tạo phiếu, kho được chọn, lượng đã xuất/còn được xuất và nút lưu phiếu có thể thay đổi. |

<a id="kho-resources-js-pages-warehouse-slip-slipdetail-vue"></a>

#### Trang Chi tiết phiếu kho — `Warehouse/Slip/SlipDetail.vue`

File: [resources/js/Pages/Warehouse/Slip/SlipDetail.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/SlipDetail.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/warehouse/slips/{slip}](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Slip/SlipDetail.vue:229) | [WarehouseSlipController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:114) | Lấy chi tiết một phiếu nhập/xuất kho kèm các quan hệ cần cho màn hình xem/sửa. Hàm còn xử lý số lượng/tồn kho, quy đổi/kiểm tra tiền tệ. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |

<a id="kho-resources-js-pages-warehouse-transfer-index-vue"></a>

#### Trang Chuyển kho — `Warehouse/Transfer/Index.vue`

File: [resources/js/Pages/Warehouse/Transfer/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/warehouses/all](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:366) | [WarehouseController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:159) | Lấy toàn bộ kho thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/warehouses/{warehouse}](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:366) | [WarehouseController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:171) | Lấy chi tiết một kho kèm các quan hệ cần cho màn hình xem/sửa. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [GET\|HEAD /api/warehouse/transfers](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:285) | [WarehouseTransferController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseTransferController.php:31) | Lấy danh sách phiếu chuyển kho thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn phân trang kết quả. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [POST /api/warehouse/transfers](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:326) | [WarehouseTransferController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseTransferController.php:39) | Kiểm tra đầu vào và tạo phiếu chuyển kho mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, xử lý số lượng/tồn kho. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [POST /api/warehouse/transfers/{id}/approve](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:347) | [WarehouseTransferController::approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseTransferController.php:102) | Duyệt phiếu chuyển kho, cập nhật trạng thái và kích hoạt bước nghiệp vụ tiếp theo. Hàm còn chạy trong giao dịch cơ sở dữ liệu, xử lý số lượng/tồn kho. | nút duyệt/gửi/từ chối, trạng thái hiển thị và khả năng chứng từ đi tiếp sang bước nghiệp vụ sau sẽ thay đổi. |
| [POST /api/warehouse/transfers/{id}/cancel](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:359) | [WarehouseTransferController::cancel()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseTransferController.php:172) | Hủy phiếu chuyển kho hợp lệ và hoàn tác dữ liệu/trạng thái liên quan nếu có. | nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi. |
| [GET\|HEAD /api/warehouse/products](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Transfer/Index.vue:312) | [ProductController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/ProductController.php:51) | Lấy danh sách sản phẩm thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn xử lý số lượng/tồn kho, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |

<a id="kho-resources-js-pages-warehouse-unit-index-vue"></a>

#### Trang Đơn vị tính — `Warehouse/Unit/Index.vue`

File: [resources/js/Pages/Warehouse/Unit/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Unit/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/warehouse/units](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Unit/Index.vue:154) | [UnitController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:19) | Lấy danh sách đơn vị tính thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [PATCH /api/warehouse/units/{id}/status](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Unit/Index.vue:227) | [UnitController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/UnitController.php:148) | Đổi trạng thái hoạt động của đơn vị tính sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến đơn vị tính trên trang này có thể thay đổi. |

<a id="kho-resources-js-pages-warehouse-unit-unitform-vue"></a>

#### Trang Biểu mẫu đơn vị tính — `Warehouse/Unit/UnitForm.vue`

File: [resources/js/Pages/Warehouse/Unit/UnitForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/Unit/UnitForm.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="kho-resources-js-pages-warehouse-warehousedetail-vue"></a>

#### Trang Chi tiết kho — `Warehouse/WarehouseDetail.vue`

File: [resources/js/Pages/Warehouse/WarehouseDetail.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/WarehouseDetail.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/warehouses/{warehouse}/detail](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/WarehouseDetail.vue:343) | [WarehouseController::detail()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:283) | Lấy hồ sơ chi tiết kho kèm các quan hệ và số liệu nghiệp vụ liên quan. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |

<a id="kho-resources-js-pages-warehouse-warehouseform-vue"></a>

#### Trang Biểu mẫu kho — `Warehouse/WarehouseForm.vue`

File: [resources/js/Pages/Warehouse/WarehouseForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/WarehouseForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [POST /api/warehouses](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/WarehouseForm.vue:215) | [WarehouseController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:114) | Kiểm tra đầu vào và tạo kho mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, chạy trong giao dịch cơ sở dữ liệu. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/warehouses/{warehouse}](vscode://file/D:/clone/project-base/resources/js/Pages/Warehouse/WarehouseForm.vue:211) | [WarehouseController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:180) | Kiểm tra và cập nhật kho hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào, chạy trong giao dịch cơ sở dữ liệu. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |


### Controller và Function

- **Controller:** [WarehouseController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-warehousecontroller), [WarehouseSlipController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-warehouseslipcontroller), [WarehouseTransferController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-warehousetransfercontroller), [InventoryMovementController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-inventorymovementcontroller), [ProductController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-productcontroller), [CategoryController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-categorycontroller), [UnitController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-unitcontroller)
- **Chi tiết Function, validation và ảnh hưởng:** [Mở chỉ mục Function toàn dự án](PROJECT_FUNCTION_INDEX.md).
- **Database:** [Mở Model, bảng và migration của phân hệ](PROJECT_DATABASE_INDEX.md#kho).

<a id="kho-service"></a>

### Service

| Service | Viết gì? | Hàm công khai |
| --- | --- | --- |
| [InventoryMovementService](vscode://file/D:/clone/project-base/app/Services/InventoryMovementService.php:1) | Ghi biến động tăng/giảm/chuyển tồn kho và liên kết chứng từ nguồn. | [record()](vscode://file/D:/clone/project-base/app/Services/InventoryMovementService.php:11) |
| [StockService](vscode://file/D:/clone/project-base/app/Services/StockService.php:1) | Truy vấn và cập nhật tồn kho. | [applySlip()](vscode://file/D:/clone/project-base/app/Services/StockService.php:10) |
| [OrderQuantityValidationService](vscode://file/D:/clone/project-base/app/Services/OrderQuantityValidationService.php:1) | Kiểm tra số lượng đặt/nhập/xuất so với tồn và lượng đã xử lý. | [validate()](vscode://file/D:/clone/project-base/app/Services/OrderQuantityValidationService.php:10) |
| [CodeGeneratorService](vscode://file/D:/clone/project-base/app/Services/CodeGeneratorService.php:1) | Sinh mã chứng từ/mã đối tượng theo công ty. | [generate()](vscode://file/D:/clone/project-base/app/Services/CodeGeneratorService.php:9) |
| [NotificationService](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:1) | Tạo, phân phối và quản lý thông báo nội bộ. | [create()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:17)<br>[createForUsers()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:47)<br>[createForCustomerAccount()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:68)<br>[createForPermission()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:92)<br>[createForRole()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:149)<br>[createForHigherRoleUsers()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:188)<br>[createForCompany()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:227)<br>[getUserNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:246)<br>[getUserNotificationsByCategory()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:268)<br>[markAsRead()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:310)<br>[markAllAsRead()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:326)<br>[delete()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:339)<br>[getUnreadCount()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:353)<br>[getUnreadCountByCategory()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:366)<br>[deleteOldNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:396)<br>[getCompanyNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:405) |

<a id="kho-model-test"></a>

### Mô hình và kiểm thử

- **Mô hình chính:** [Warehouse](vscode://file/D:/clone/project-base/app/Models/Warehouse.php:1), [WarehouseSlip](vscode://file/D:/clone/project-base/app/Models/WarehouseSlip.php:1), [WarehouseSlipItem](vscode://file/D:/clone/project-base/app/Models/WarehouseSlipItem.php:1), [WarehouseTransfer](vscode://file/D:/clone/project-base/app/Models/WarehouseTransfer.php:1), [InventoryMovement](vscode://file/D:/clone/project-base/app/Models/InventoryMovement.php:1), [Product](vscode://file/D:/clone/project-base/app/Models/Product.php:1), [Category](vscode://file/D:/clone/project-base/app/Models/Category.php:1), [Unit](vscode://file/D:/clone/project-base/app/Models/Unit.php:1).
- **Kiểm thử liên quan:** [InventoryAccountingFlowTest.php](vscode://file/D:/clone/project-base/tests/Feature/InventoryAccountingFlowTest.php:1), [InventoryLifecycleEndToEndTest.php](vscode://file/D:/clone/project-base/tests/Feature/InventoryLifecycleEndToEndTest.php:1), [WarehouseFilterTest.php](vscode://file/D:/clone/project-base/tests/Feature/WarehouseFilterTest.php:1).

<a id="k-to-an-v-a-c-ong-n"></a>

## Kế toán và công nợ

Tài khoản, ngân hàng, tiền tệ, giao dịch, công nợ, đối soát COD và báo cáo lãi lỗ.

### Các trang trong phân hệ


<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-accountledger-index-vue"></a>

#### Trang Sổ cái tài khoản — `Accountant/AccountLedger/Index.vue`

File: [resources/js/Pages/Accountant/AccountLedger/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/AccountLedger/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/accountant/accounts/all](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/AccountLedger/Index.vue:194) | [AccountController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:69) | Lấy toàn bộ tài khoản kế toán thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/accountant/accounts/{account}](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/AccountLedger/Index.vue:194) | [AccountController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:131) | Lấy chi tiết một tài khoản kế toán kèm các quan hệ cần cho màn hình xem/sửa. Hàm còn quy đổi/kiểm tra tiền tệ. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [GET\|HEAD /api/accountant/account-ledgers](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/AccountLedger/Index.vue:170) | [Accountant\AccountLedgerController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/Accountant/AccountLedgerController.php:11) | Lấy danh sách sổ cái tài khoản thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn phân trang kết quả, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-account-accountform-vue"></a>

#### Trang Biểu mẫu tài khoản — `Accountant/Account/AccountForm.vue`

File: [resources/js/Pages/Accountant/Account/AccountForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/AccountForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [POST /api/accountant/accounts](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/AccountForm.vue:273) | [AccountController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:81) | Kiểm tra đầu vào và tạo tài khoản kế toán mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, quy đổi/kiểm tra tiền tệ. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/accountant/accounts/{account}](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/AccountForm.vue:267) | [AccountController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:139) | Kiểm tra và cập nhật tài khoản kế toán hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào, quy đổi/kiểm tra tiền tệ. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |
| [GET\|HEAD /api/accountant/banks](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/AccountForm.vue:295) | [BankController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/BankController.php:16) | Lấy danh sách ngân hàng thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn phân trang kết quả. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/accountant/currencies](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/AccountForm.vue:294) | [CurrencyController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:61) | Lấy danh sách tiền tệ và tỷ giá thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn phân trang kết quả, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-account-index-vue"></a>

#### Trang Tài khoản — `Accountant/Account/Index.vue`

File: [resources/js/Pages/Accountant/Account/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/accountant/accounts](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/Index.vue:281) | [AccountController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:12) | Lấy danh sách tài khoản kế toán thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn phân trang kết quả, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [PATCH /api/accountant/accounts/{account}/toggle-status](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Account/Index.vue:270) | [AccountController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:216) | Đổi trạng thái hoạt động của tài khoản kế toán sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến tài khoản kế toán trên trang này có thể thay đổi. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-bank-bankform-vue"></a>

#### Trang Biểu mẫu ngân hàng — `Accountant/Bank/BankForm.vue`

File: [resources/js/Pages/Accountant/Bank/BankForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Bank/BankForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [POST /api/accountant/banks](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Bank/BankForm.vue:146) | [BankController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/BankController.php:65) | Kiểm tra đầu vào và tạo ngân hàng mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, chạy trong giao dịch cơ sở dữ liệu. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-bank-index-vue"></a>

#### Trang Ngân hàng — `Accountant/Bank/Index.vue`

File: [resources/js/Pages/Accountant/Bank/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Bank/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/accountant/banks](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Bank/Index.vue:213) | [BankController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/BankController.php:16) | Lấy danh sách ngân hàng thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn phân trang kết quả. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [PATCH /api/accountant/banks/{bank}/toggle-status](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Bank/Index.vue:207) | [BankController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/BankController.php:134) | Đổi trạng thái hoạt động của ngân hàng sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến ngân hàng trên trang này có thể thay đổi. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-codreconciliation-index-vue"></a>

#### Trang Đối soát COD — `Accountant/CodReconciliation/Index.vue`

File: [resources/js/Pages/Accountant/CodReconciliation/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/CodReconciliation/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/accountant/cod-reconciliations](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/CodReconciliation/Index.vue:414) | [CodReconciliationController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CodReconciliationController.php:16) | Lấy danh sách đối soát COD thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn phân trang kết quả, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [POST /api/accountant/cod-reconciliations](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/CodReconciliation/Index.vue:448) | [CodReconciliationController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CodReconciliationController.php:56) | Kiểm tra đầu vào và tạo đối soát COD mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [POST /api/accountant/cod-reconciliations/partners](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/CodReconciliation/Index.vue:426) | [CodReconciliationController::storePartner()](vscode://file/D:/clone/project-base/app/Http/Controllers/CodReconciliationController.php:77) | Thực hiện nghiệp vụ “store Partner” cho đối soát COD. Bao gồm: kiểm tra dữ liệu đầu vào. | dữ liệu hoặc hành động liên quan đến đối soát COD trên trang này có thể thay đổi. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-currency-curencyratehistory-vue"></a>

#### Trang Lịch sử tỷ giá — `Accountant/Currency/CurencyRateHistory.vue`

File: [resources/js/Pages/Accountant/Currency/CurencyRateHistory.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/CurencyRateHistory.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/accountant/currencies/{currency}/rates](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/CurencyRateHistory.vue:135) | [CurrencyController::rates()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:194) | Thực hiện nghiệp vụ “rates” cho tiền tệ và tỷ giá. Bao gồm: quy đổi/kiểm tra tiền tệ. | dữ liệu hoặc hành động liên quan đến tiền tệ và tỷ giá trên trang này có thể thay đổi. |
| [POST /api/accountant/currencies/{currency}/rates](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/CurencyRateHistory.vue:145) | [CurrencyController::storeRate()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:206) | Thực hiện nghiệp vụ “store Rate” cho tiền tệ và tỷ giá. Bao gồm: kiểm tra dữ liệu đầu vào, quy đổi/kiểm tra tiền tệ. | dữ liệu hoặc hành động liên quan đến tiền tệ và tỷ giá trên trang này có thể thay đổi. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-currency-currencyform-vue"></a>

#### Trang Biểu mẫu tiền tệ — `Accountant/Currency/CurrencyForm.vue`

File: [resources/js/Pages/Accountant/Currency/CurrencyForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/CurrencyForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [POST /api/accountant/currencies](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/CurrencyForm.vue:224) | [CurrencyController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:94) | Kiểm tra đầu vào và tạo tiền tệ và tỷ giá mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, quy đổi/kiểm tra tiền tệ. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/accountant/currencies/{currency}](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/CurrencyForm.vue:220) | [CurrencyController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:123) | Kiểm tra và cập nhật tiền tệ và tỷ giá hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào, chạy trong giao dịch cơ sở dữ liệu, quy đổi/kiểm tra tiền tệ. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-currency-index-vue"></a>

#### Trang Tiền tệ — `Accountant/Currency/Index.vue`

File: [resources/js/Pages/Accountant/Currency/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/accountant/currencies](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/Index.vue:253) | [CurrencyController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:61) | Lấy danh sách tiền tệ và tỷ giá thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn phân trang kết quả, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [PATCH /api/accountant/currencies/{currency}/toggle-status](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Currency/Index.vue:280) | [CurrencyController::toggleStatus()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:247) | Đổi trạng thái hoạt động của tiền tệ và tỷ giá sau khi kiểm tra quyền/điều kiện. | dữ liệu hoặc hành động liên quan đến tiền tệ và tỷ giá trên trang này có thể thay đổi. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-customer-customerdetail-vue"></a>

#### Trang Chi tiết khách hàng — `Accountant/Customer/CustomerDetail.vue`

File: [resources/js/Pages/Accountant/Customer/CustomerDetail.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Customer/CustomerDetail.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-customer-index-vue"></a>

#### Trang Khách hàng — `Accountant/Customer/Index.vue`

File: [resources/js/Pages/Accountant/Customer/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Customer/Index.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-dashboard-vue"></a>

#### Trang Bảng điều khiển — `Accountant/Dashboard.vue`

File: [resources/js/Pages/Accountant/Dashboard.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Dashboard.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-report-profitloss-vue"></a>

#### Trang Báo cáo lãi lỗ — `Accountant/Report/ProfitLoss.vue`

File: [resources/js/Pages/Accountant/Report/ProfitLoss.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Report/ProfitLoss.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/accountant/profit-loss-report](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Report/ProfitLoss.vue:249) | [Accountant\ProfitLossReportController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/Accountant/ProfitLossReportController.php:15) | Lấy danh sách báo cáo lãi lỗ thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn kiểm tra dữ liệu đầu vào, xử lý số lượng/tồn kho, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-supplier-index-vue"></a>

#### Trang Nhà cung cấp — `Accountant/Supplier/Index.vue`

File: [resources/js/Pages/Accountant/Supplier/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Supplier/Index.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-transactioncategory-index-vue"></a>

#### Trang Loại giao dịch — `Accountant/TransactionCategory/Index.vue`

File: [resources/js/Pages/Accountant/TransactionCategory/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/TransactionCategory/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/accountant/transaction-categories](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/TransactionCategory/Index.vue:203) | [TransactionCategoryController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionCategoryController.php:22) | Lấy danh sách loại giao dịch thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn phân trang kết quả. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [PUT /api/accountant/transaction-categories/{transactionCategory}](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/TransactionCategory/Index.vue:238) | [TransactionCategoryController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionCategoryController.php:82) | Kiểm tra và cập nhật loại giao dịch hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |
| [DELETE /api/accountant/transaction-categories/{transactionCategory}](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/TransactionCategory/Index.vue:253) | [TransactionCategoryController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionCategoryController.php:103) | Kiểm tra điều kiện rồi xóa loại giao dịch và dữ liệu phụ thuộc được xử lý trong hàm. | nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-transactioncategory-transactioncategoryform-vue"></a>

#### Trang Biểu mẫu loại giao dịch — `Accountant/TransactionCategory/TransactionCategoryForm.vue`

File: [resources/js/Pages/Accountant/TransactionCategory/TransactionCategoryForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/TransactionCategory/TransactionCategoryForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [POST /api/accountant/transaction-categories](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/TransactionCategory/TransactionCategoryForm.vue:216) | [TransactionCategoryController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionCategoryController.php:68) | Kiểm tra đầu vào và tạo loại giao dịch mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào. | dữ liệu gửi lên khi tạo mới, lỗi kiểm tra dữ liệu và dữ liệu xuất hiện sau khi lưu sẽ thay đổi. |
| [PUT /api/accountant/transaction-categories/{transactionCategory}](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/TransactionCategory/TransactionCategoryForm.vue:212) | [TransactionCategoryController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionCategoryController.php:82) | Kiểm tra và cập nhật loại giao dịch hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào. | giá trị biểu mẫu được lưu, lỗi kiểm tra dữ liệu và dữ liệu tải lại sau khi sửa sẽ thay đổi. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-transaction-index-vue"></a>

#### Trang Giao dịch — `Accountant/Transaction/Index.vue`

File: [resources/js/Pages/Accountant/Transaction/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/accountant/accounts/all](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:468) | [AccountController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:69) | Lấy toàn bộ tài khoản kế toán thuộc công ty hiện tại ở dạng rút gọn. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/accountant/accounts/{account}](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:468) | [AccountController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:131) | Lấy chi tiết một tài khoản kế toán kèm các quan hệ cần cho màn hình xem/sửa. Hàm còn quy đổi/kiểm tra tiền tệ. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |
| [GET\|HEAD /api/accountant/currencies](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:470) | [CurrencyController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/CurrencyController.php:61) | Lấy danh sách tiền tệ và tỷ giá thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn phân trang kết quả, quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/accountant/transactions](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:420) | [TransactionController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:24) | Lấy danh sách giao dịch kế toán thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn quy đổi/kiểm tra tiền tệ. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [POST /api/accountant/transactions/{transaction}/approve](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:380) | [TransactionController::approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:267) | Duyệt giao dịch kế toán, cập nhật trạng thái và kích hoạt bước nghiệp vụ tiếp theo. | nút duyệt/gửi/từ chối, trạng thái hiển thị và khả năng chứng từ đi tiếp sang bước nghiệp vụ sau sẽ thay đổi. |
| [POST /api/accountant/transactions/{transaction}/reject](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:328) | [TransactionController::reject()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:281) | Từ chối giao dịch kế toán, ghi lý do/trạng thái và thông báo bên liên quan nếu có. Hàm còn kiểm tra dữ liệu đầu vào. | nút duyệt/gửi/từ chối, trạng thái hiển thị và khả năng chứng từ đi tiếp sang bước nghiệp vụ sau sẽ thay đổi. |
| [DELETE /api/accountant/transactions/{transaction}](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:351) | [TransactionController::destroy()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:325) | Kiểm tra điều kiện rồi xóa giao dịch kế toán và dữ liệu phụ thuộc được xử lý trong hàm. | nút hủy/xóa, điều kiện được phép thao tác và trạng thái/dòng dữ liệu sau thao tác sẽ thay đổi. |
| [GET\|HEAD /api/accountant/transaction-categories](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/Index.vue:469) | [TransactionCategoryController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionCategoryController.php:22) | Lấy danh sách loại giao dịch thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn phân trang kết quả. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-transaction-transactiondetail-vue"></a>

#### Trang Chi tiết giao dịch — `Accountant/Transaction/TransactionDetail.vue`

File: [resources/js/Pages/Accountant/Transaction/TransactionDetail.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionDetail.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/accountant/transactions/{transaction}](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionDetail.vue:416) | [TransactionController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:221) | Lấy chi tiết một giao dịch kế toán kèm các quan hệ cần cho màn hình xem/sửa. Hàm còn quy đổi/kiểm tra tiền tệ. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |

<a id="k-to-an-v-a-c-ong-n-resources-js-pages-accountant-transaction-transactionform-vue"></a>

#### Form giao dịch kế toán — `Accountant/Transaction/TransactionForm.vue`

File: [resources/js/Pages/Accountant/Transaction/TransactionForm.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD /api/accountant/accounts/all](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:916) | [AccountController::all()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:69) | Lấy toàn bộ tài khoản kế toán thuộc công ty hiện tại ở dạng rút gọn. | danh sách đơn có thể chọn, số tiền còn phải thu/trả và dữ liệu dùng để lập giao dịch có thể thay đổi. |
| [GET\|HEAD /api/accountant/accounts/{account}](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:916) | [AccountController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/AccountController.php:131) | Lấy chi tiết một tài khoản kế toán kèm các quan hệ cần cho màn hình xem/sửa. Hàm còn quy đổi/kiểm tra tiền tệ. | danh sách đơn có thể chọn, số tiền còn phải thu/trả và dữ liệu dùng để lập giao dịch có thể thay đổi. |
| [POST /api/accountant/transactions](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1441) | [TransactionController::store()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:78) | Kiểm tra đầu vào và tạo giao dịch kế toán mới cùng dữ liệu liên quan. Hàm còn kiểm tra dữ liệu đầu vào, quy đổi/kiểm tra tiền tệ. | danh sách đơn có thể chọn, số tiền còn phải thu/trả và dữ liệu dùng để lập giao dịch có thể thay đổi. |
| [GET\|HEAD /api/accountant/transactions/exchange-rate](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1297) | [TransactionController::exchangeRate()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:140) | Lấy tỷ giá phù hợp giữa tiền tệ giao dịch và tiền tệ công ty. | danh sách đơn có thể chọn, số tiền còn phải thu/trả và dữ liệu dùng để lập giao dịch có thể thay đổi. |
| [PUT /api/accountant/transactions/{transaction}](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1439) | [TransactionController::update()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:159) | Kiểm tra và cập nhật giao dịch kế toán hiện có sau khi kiểm tra phạm vi/quyền sửa. Hàm còn kiểm tra dữ liệu đầu vào, quy đổi/kiểm tra tiền tệ. | danh sách đơn có thể chọn, số tiền còn phải thu/trả và dữ liệu dùng để lập giao dịch có thể thay đổi. |
| [GET\|HEAD /api/accountant/transactions/{transaction}](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1297) | [TransactionController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:221) | Lấy chi tiết một giao dịch kế toán kèm các quan hệ cần cho màn hình xem/sửa. Hàm còn quy đổi/kiểm tra tiền tệ. | danh sách đơn có thể chọn, số tiền còn phải thu/trả và dữ liệu dùng để lập giao dịch có thể thay đổi. |
| [GET\|HEAD /api/accountant/transactions/order-outstanding](vscode://file/D:/clone/project-base/resources/js/Pages/Accountant/Transaction/TransactionForm.vue:1351) | [TransactionController::orderOutstanding()](vscode://file/D:/clone/project-base/app/Http/Controllers/TransactionController.php:301) | Tính số tiền còn phải thu/phải trả của đơn để lập giao dịch. | danh sách đơn có thể chọn, số tiền còn phải thu/trả và dữ liệu dùng để lập giao dịch có thể thay đổi. |


### Controller và Function

- **Controller:** [AccountController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-accountcontroller), [Accountant\AccountLedgerController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-accountant-accountledgercontroller), [Accountant\ProfitLossReportController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-accountant-profitlossreportcontroller), [BankController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-bankcontroller), [CurrencyController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-currencycontroller), [TransactionController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-transactioncontroller), [TransactionCategoryController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-transactioncategorycontroller), [CodReconciliationController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-codreconciliationcontroller)
- **Chi tiết Function, validation và ảnh hưởng:** [Mở chỉ mục Function toàn dự án](PROJECT_FUNCTION_INDEX.md).
- **Database:** [Mở Model, bảng và migration của phân hệ](PROJECT_DATABASE_INDEX.md#k-to-an-v-a-c-ong-n).

<a id="k-to-an-v-a-c-ong-n-service"></a>

### Service

| Service | Viết gì? | Hàm công khai |
| --- | --- | --- |
| [AccountBalanceService](vscode://file/D:/clone/project-base/app/Services/AccountBalanceService.php:1) | Tính, cập nhật và xây dựng lại số dư tài khoản kế toán. | [rebuild()](vscode://file/D:/clone/project-base/app/Services/AccountBalanceService.php:15)<br>[increase()](vscode://file/D:/clone/project-base/app/Services/AccountBalanceService.php:48)<br>[decrease()](vscode://file/D:/clone/project-base/app/Services/AccountBalanceService.php:62)<br>[getBalance()](vscode://file/D:/clone/project-base/app/Services/AccountBalanceService.php:80) |
| [LedgerService](vscode://file/D:/clone/project-base/app/Services/LedgerService.php:1) | Truy vấn và trình bày sổ cái/bút toán tài khoản. | [record()](vscode://file/D:/clone/project-base/app/Services/LedgerService.php:19) |
| [TransactionService](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:1) | Tạo, duyệt, từ chối giao dịch và ghi nhận số dư/công nợ liên quan. | [create()](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:45)<br>[update()](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:118)<br>[delete()](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:160)<br>[approve()](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:191)<br>[reject()](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:259)<br>[salesOrderOutstanding()](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:1020)<br>[purchaseOrderOutstanding()](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:1029) |
| [TransactionCategoryService](vscode://file/D:/clone/project-base/app/Services/TransactionCategoryService.php:1) | Quản lý quy tắc nghiệp vụ của loại giao dịch. | [paginate()](vscode://file/D:/clone/project-base/app/Services/TransactionCategoryService.php:25)<br>[getActive()](vscode://file/D:/clone/project-base/app/Services/TransactionCategoryService.php:35)<br>[find()](vscode://file/D:/clone/project-base/app/Services/TransactionCategoryService.php:43)<br>[create()](vscode://file/D:/clone/project-base/app/Services/TransactionCategoryService.php:53)<br>[update()](vscode://file/D:/clone/project-base/app/Services/TransactionCategoryService.php:75)<br>[delete()](vscode://file/D:/clone/project-base/app/Services/TransactionCategoryService.php:105) |
| [CurrencyService](vscode://file/D:/clone/project-base/app/Services/CurrencyService.php:1) | Quản lý logic tiền tệ và lịch sử tỷ giá. | [getCompanyCurrency()](vscode://file/D:/clone/project-base/app/Services/CurrencyService.php:9)<br>[convertByCurrency()](vscode://file/D:/clone/project-base/app/Services/CurrencyService.php:16)<br>[symbol()](vscode://file/D:/clone/project-base/app/Services/CurrencyService.php:31)<br>[code()](vscode://file/D:/clone/project-base/app/Services/CurrencyService.php:36) |
| [CompanyCurrencyService](vscode://file/D:/clone/project-base/app/Services/CompanyCurrencyService.php:1) | Lấy tiền tệ công ty và tính tỷ giá quy đổi. | [rate()](vscode://file/D:/clone/project-base/app/Services/CompanyCurrencyService.php:11)<br>[toBase()](vscode://file/D:/clone/project-base/app/Services/CompanyCurrencyService.php:30) |
| [CustomerDebtService](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:1) | Ghi nhận và tính công nợ phải thu khách hàng. | [createFromSalesOrder()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:23)<br>[createFromWarehouseSlip()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:46)<br>[createDebt()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:110)<br>[receivePayment()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:140)<br>[receiveOpeningDebtPayment()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:166)<br>[getOpeningDebtBalance()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:188)<br>[receiveAdvance()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:198)<br>[refundAdvance()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:213)<br>[getAdvanceBalance()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:228)<br>[refundToCustomer()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:248)<br>[getBalance()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:281)<br>[getHistory()](vscode://file/D:/clone/project-base/app/Services/CustomerDebtService.php:292) |
| [SupplierDebtService](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:1) | Ghi nhận và tính công nợ phải trả nhà cung cấp. | [createFromWarehouseSlip()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:27)<br>[applyAdvanceToOrder()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:59)<br>[createDebt()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:131)<br>[getDebtBalance()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:155)<br>[getOutstandingBalance()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:167)<br>[getOpeningDebtBalance()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:173)<br>[getAdvanceBalance()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:182)<br>[paySupplier()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:220)<br>[payOpeningDebt()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:245)<br>[receiveFromSupplier()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:274)<br>[advanceSupplier()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:298)<br>[refundAdvance()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:318)<br>[getBalance()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:345)<br>[getHistory()](vscode://file/D:/clone/project-base/app/Services/SupplierDebtService.php:353) |
| [CodReconciliationService](vscode://file/D:/clone/project-base/app/Services/CodReconciliationService.php:1) | Tạo và xử lý đối soát tiền thu hộ COD. | [reconcile()](vscode://file/D:/clone/project-base/app/Services/CodReconciliationService.php:16) |

<a id="k-to-an-v-a-c-ong-n-model-test"></a>

### Mô hình và kiểm thử

- **Mô hình chính:** [Account](vscode://file/D:/clone/project-base/app/Models/Account.php:1), [Bank](vscode://file/D:/clone/project-base/app/Models/Bank.php:1), [Currency](vscode://file/D:/clone/project-base/app/Models/Currency.php:1), [CurrencyRate](vscode://file/D:/clone/project-base/app/Models/CurrencyRate.php:1), [Transaction](vscode://file/D:/clone/project-base/app/Models/Transaction.php:1), [TransactionCategory](vscode://file/D:/clone/project-base/app/Models/TransactionCategory.php:1), [CustomerDebt](vscode://file/D:/clone/project-base/app/Models/CustomerDebt.php:1), [SupplierDebt](vscode://file/D:/clone/project-base/app/Models/SupplierDebt.php:1), [CodReconciliation](vscode://file/D:/clone/project-base/app/Models/CodReconciliation.php:1).
- **Kiểm thử liên quan:** [CodReconciliationTest.php](vscode://file/D:/clone/project-base/tests/Feature/CodReconciliationTest.php:1), [DebtFlowEndToEndTest.php](vscode://file/D:/clone/project-base/tests/Feature/DebtFlowEndToEndTest.php:1), [DebtSummaryTest.php](vscode://file/D:/clone/project-base/tests/Feature/DebtSummaryTest.php:1), [DemoAccountPageSmokeTest.php](vscode://file/D:/clone/project-base/tests/Feature/DemoAccountPageSmokeTest.php:1), [InventoryAccountingFlowTest.php](vscode://file/D:/clone/project-base/tests/Feature/InventoryAccountingFlowTest.php:1), [OpeningBalanceCurrencySnapshotTest.php](vscode://file/D:/clone/project-base/tests/Feature/OpeningBalanceCurrencySnapshotTest.php:1), [PurchaseToPaymentEndToEndTest.php](vscode://file/D:/clone/project-base/tests/Feature/PurchaseToPaymentEndToEndTest.php:1), [TransactionCategoryCompanyIsolationTest.php](vscode://file/D:/clone/project-base/tests/Feature/TransactionCategoryCompanyIsolationTest.php:1), [TransactionFlowTest.php](vscode://file/D:/clone/project-base/tests/Feature/TransactionFlowTest.php:1), [CompanyCurrencyServiceTest.php](vscode://file/D:/clone/project-base/tests/Unit/CompanyCurrencyServiceTest.php:1), [DebtCalculationTest.php](vscode://file/D:/clone/project-base/tests/Unit/DebtCalculationTest.php:1).

<a id="bng-diu-khin-nht-k-y-v-a-th-ong-b-ao"></a>

## Bảng điều khiển, nhật ký và thông báo

Tổng hợp bảng điều khiển, nhật ký hoạt động, thông báo và phân quyền kênh thời gian thực.

### Các trang trong phân hệ


<a id="bng-diu-khin-nht-k-y-v-a-th-ong-b-ao-resources-js-pages-auditlog-auditlogdetail-vue"></a>

#### Trang Chi tiết nhật ký hoạt động — `AuditLog/AuditLogDetail.vue`

File: [resources/js/Pages/AuditLog/AuditLogDetail.vue](vscode://file/D:/clone/project-base/resources/js/Pages/AuditLog/AuditLogDetail.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="bng-diu-khin-nht-k-y-v-a-th-ong-b-ao-resources-js-pages-auditlog-index-vue"></a>

#### Trang Nhật ký hoạt động — `AuditLog/Index.vue`

File: [resources/js/Pages/AuditLog/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/AuditLog/Index.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD //](vscode://file/D:/clone/project-base/resources/js/Pages/AuditLog/Index.vue:209) | [DashboardController::landing()](vscode://file/D:/clone/project-base/app/Http/Controllers/DashboardController.php:25) | Thực hiện nghiệp vụ “landing” cho bảng điều khiển. | dữ liệu hoặc hành động liên quan đến bảng điều khiển trên trang này có thể thay đổi. |
| [GET\|HEAD /api/audit-logs](vscode://file/D:/clone/project-base/resources/js/Pages/AuditLog/Index.vue:202) | [AuditLogController::index()](vscode://file/D:/clone/project-base/app/Http/Controllers/AuditLogController.php:13) | Lấy danh sách nhật ký hoạt động thuộc phạm vi được phép, áp dụng bộ lọc/tìm kiếm được gửi lên. Hàm còn kiểm tra dữ liệu đầu vào. | danh sách, bộ lọc, phân trang hoặc dữ liệu lựa chọn có thể hiển thị thiếu/thừa/sai giá trị. |
| [GET\|HEAD /api/audit-logs/{auditLog}](vscode://file/D:/clone/project-base/resources/js/Pages/AuditLog/Index.vue:184) | [AuditLogController::show()](vscode://file/D:/clone/project-base/app/Http/Controllers/AuditLogController.php:109) | Lấy chi tiết một nhật ký hoạt động kèm các quan hệ cần cho màn hình xem/sửa. | các trường chi tiết, quan hệ và số liệu trong modal/trang chi tiết có thể thay đổi hoặc không tải được. |

<a id="bng-diu-khin-nht-k-y-v-a-th-ong-b-ao-resources-js-pages-dashboard-vue"></a>

#### Trang Bảng điều khiển — `DashBoard.vue`

File: [resources/js/Pages/DashBoard.vue](vscode://file/D:/clone/project-base/resources/js/Pages/DashBoard.vue:1)

| API gọi tại | Controller/function xử lý | Function làm gì? | Khi sửa sẽ ảnh hưởng gì trên trang? |
| --- | --- | --- | --- |
| [GET\|HEAD //](vscode://file/D:/clone/project-base/resources/js/Pages/DashBoard.vue:1) | [DashboardController::landing()](vscode://file/D:/clone/project-base/app/Http/Controllers/DashboardController.php:25) | Thực hiện nghiệp vụ “landing” cho bảng điều khiển. | dữ liệu hoặc hành động liên quan đến bảng điều khiển trên trang này có thể thay đổi. |
| [GET\|HEAD /api/dashboard/overview](vscode://file/D:/clone/project-base/resources/js/Pages/DashBoard.vue:1205) | [DashboardController::overview()](vscode://file/D:/clone/project-base/app/Http/Controllers/DashboardController.php:50) | Thực hiện nghiệp vụ “overview” cho bảng điều khiển. Bao gồm: quy đổi/kiểm tra tiền tệ. | chỉ số, tổng hợp và biểu đồ báo cáo có thể thay đổi. |
| [GET\|HEAD /api/dashboard/{module}](vscode://file/D:/clone/project-base/resources/js/Pages/DashBoard.vue:1205) | [DashboardController::module()](vscode://file/D:/clone/project-base/app/Http/Controllers/DashboardController.php:86) | Thực hiện nghiệp vụ “module” cho bảng điều khiển. Bao gồm: quy đổi/kiểm tra tiền tệ. | dữ liệu hoặc hành động liên quan đến bảng điều khiển trên trang này có thể thay đổi. |

<a id="bng-diu-khin-nht-k-y-v-a-th-ong-b-ao-resources-js-pages-home-vue"></a>

#### Trang chủ — `Home.vue`

File: [resources/js/Pages/Home.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Home.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.


### Controller và Function

- **Controller:** [DashboardController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-dashboardcontroller), [AuditLogController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-auditlogcontroller), [NotificationController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-notificationcontroller), [BroadcastController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-broadcastcontroller)
- **Chi tiết Function, validation và ảnh hưởng:** [Mở chỉ mục Function toàn dự án](PROJECT_FUNCTION_INDEX.md).
- **Database:** [Mở Model, bảng và migration của phân hệ](PROJECT_DATABASE_INDEX.md#bng-diu-khin-nht-k-y-v-a-th-ong-b-ao).

<a id="bng-diu-khin-nht-k-y-v-a-th-ong-b-ao-service"></a>

### Service

| Service | Viết gì? | Hàm công khai |
| --- | --- | --- |
| [DashboardService](vscode://file/D:/clone/project-base/app/Services/DashboardService.php:1) | Tổng hợp chỉ số hiệu suất và số liệu bảng điều khiển theo module/khoảng ngày. | [getOverview()](vscode://file/D:/clone/project-base/app/Services/DashboardService.php:14)<br>[getModuleOverview()](vscode://file/D:/clone/project-base/app/Services/DashboardService.php:57) |
| [ActivityLogService](vscode://file/D:/clone/project-base/app/Services/ActivityLogService.php:1) | Ghi nhật ký hoạt động cho các thao tác nghiệp vụ. | [log()](vscode://file/D:/clone/project-base/app/Services/ActivityLogService.php:9) |
| [NotificationService](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:1) | Tạo, phân phối và quản lý thông báo nội bộ. | [create()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:17)<br>[createForUsers()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:47)<br>[createForCustomerAccount()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:68)<br>[createForPermission()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:92)<br>[createForRole()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:149)<br>[createForHigherRoleUsers()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:188)<br>[createForCompany()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:227)<br>[getUserNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:246)<br>[getUserNotificationsByCategory()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:268)<br>[markAsRead()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:310)<br>[markAllAsRead()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:326)<br>[delete()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:339)<br>[getUnreadCount()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:353)<br>[getUnreadCountByCategory()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:366)<br>[deleteOldNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:396)<br>[getCompanyNotifications()](vscode://file/D:/clone/project-base/app/Services/NotificationService.php:405) |

<a id="bng-diu-khin-nht-k-y-v-a-th-ong-b-ao-model-test"></a>

### Mô hình và kiểm thử

- **Mô hình chính:** [ActivityLog](vscode://file/D:/clone/project-base/app/Models/ActivityLog.php:1), [Notification](vscode://file/D:/clone/project-base/app/Models/Notification.php:1).
- **Kiểm thử liên quan:** [AuditLogFeatureTest.php](vscode://file/D:/clone/project-base/tests/Feature/AuditLogFeatureTest.php:1), [ModuleDashboardTest.php](vscode://file/D:/clone/project-base/tests/Feature/ModuleDashboardTest.php:1), [NotificationFeatureTest.php](vscode://file/D:/clone/project-base/tests/Feature/NotificationFeatureTest.php:1), [NotificationRecipientsTest.php](vscode://file/D:/clone/project-base/tests/Feature/NotificationRecipientsTest.php:1), [BroadcastCompanyDataChangesMiddlewareTest.php](vscode://file/D:/clone/project-base/tests/Unit/BroadcastCompanyDataChangesMiddlewareTest.php:1), [NotificationCreatedTest.php](vscode://file/D:/clone/project-base/tests/Unit/NotificationCreatedTest.php:1).

<a id="x-ac-thc-v-a-api-d-ung-chung"></a>

## Xác thực và API dùng chung

Đăng nhập/đăng ký, mật khẩu, xác minh thư điện tử, tỉnh phường và các hàm hỗ trợ dùng chung.

### Các trang trong phân hệ

> Phân hệ này không có thư mục trang Vue riêng; xem tuyến web/bộ điều khiển để xác định giao diện do khung phần mềm cung cấp.


### Controller và Function

- **Controller:** [Auth\AuthenticatedSessionController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-auth-authenticatedsessioncontroller), [Auth\ConfirmablePasswordController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-auth-confirmablepasswordcontroller), [Auth\EmailVerificationNotificationController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-auth-emailverificationnotificationcontroller), [Auth\EmailVerificationPromptController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-auth-emailverificationpromptcontroller), [Auth\GoogleController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-auth-googlecontroller), [Auth\NewPasswordController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-auth-newpasswordcontroller), [Auth\PasswordController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-auth-passwordcontroller), [Auth\PasswordResetLinkController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-auth-passwordresetlinkcontroller), [Auth\RegisteredUserController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-auth-registeredusercontroller), [Auth\UpdatePhoneController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-auth-updatephonecontroller), [Auth\VerifyEmailController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-auth-verifyemailcontroller), [AddressController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-addresscontroller), [ProvinceController](PROJECT_FUNCTION_INDEX.md#app-http-controllers-provincecontroller), [Controller](PROJECT_FUNCTION_INDEX.md#app-http-controllers-controller)
- **Chi tiết Function, validation và ảnh hưởng:** [Mở chỉ mục Function toàn dự án](PROJECT_FUNCTION_INDEX.md).
- **Database:** [Mở Model, bảng và migration của phân hệ](PROJECT_DATABASE_INDEX.md#x-ac-thc-v-a-api-d-ung-chung).

<a id="x-ac-thc-v-a-api-d-ung-chung-service"></a>

### Service

| Service | Viết gì? | Hàm công khai |
| --- | --- | --- |
| — | Phân hệ chủ yếu dùng controller hoặc service của khung phần mềm. | — |

<a id="x-ac-thc-v-a-api-d-ung-chung-model-test"></a>

### Mô hình và kiểm thử

- **Mô hình chính:** [User](vscode://file/D:/clone/project-base/app/Models/User.php:1), [Province](vscode://file/D:/clone/project-base/app/Models/Province.php:1), [Ward](vscode://file/D:/clone/project-base/app/Models/Ward.php:1).
- **Kiểm thử liên quan:** [AuthenticationTest.php](vscode://file/D:/clone/project-base/tests/Feature/Auth/AuthenticationTest.php:1), [PasswordConfirmationTest.php](vscode://file/D:/clone/project-base/tests/Feature/Auth/PasswordConfirmationTest.php:1), [PasswordResetTest.php](vscode://file/D:/clone/project-base/tests/Feature/Auth/PasswordResetTest.php:1), [PasswordUpdateTest.php](vscode://file/D:/clone/project-base/tests/Feature/Auth/PasswordUpdateTest.php:1), [RegistrationTest.php](vscode://file/D:/clone/project-base/tests/Feature/Auth/RegistrationTest.php:1).

<a id="hng-dn-v-a-t-ai-liu"></a>

## Hướng dẫn và tài liệu

Trang hướng dẫn sử dụng và các trang tài liệu/nội dung dùng chung của hệ thống.

### Các trang trong phân hệ


<a id="hng-dn-v-a-t-ai-liu-resources-js-pages-guide-index-vue"></a>

#### Trang Hướng dẫn — `Guide/Index.vue`

File: [resources/js/Pages/Guide/Index.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Guide/Index.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="hng-dn-v-a-t-ai-liu-resources-js-pages-document-vue"></a>

#### Trang Tài liệu — `Document.vue`

File: [resources/js/Pages/Document.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Document.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.

<a id="hng-dn-v-a-t-ai-liu-resources-js-pages-page-vue"></a>

#### Trang nội dung — `Page.vue`

File: [resources/js/Pages/Page.vue](vscode://file/D:/clone/project-base/resources/js/Pages/Page.vue:1)

> Chưa tìm thấy lời gọi API trực tiếp trong tệp này. Trang có thể chỉ dựng giao diện, nhận thuộc tính từ tuyến web hoặc gọi dữ liệu qua thành phần/kho trạng thái/hàm tái sử dụng.


### Controller và Function

- **Controller:** Không có Controller riêng.
- **Chi tiết Function, validation và ảnh hưởng:** [Mở chỉ mục Function toàn dự án](PROJECT_FUNCTION_INDEX.md).
- **Database:** [Mở Model, bảng và migration của phân hệ](PROJECT_DATABASE_INDEX.md#hng-dn-v-a-t-ai-liu).

<a id="hng-dn-v-a-t-ai-liu-service"></a>

### Service

| Service | Viết gì? | Hàm công khai |
| --- | --- | --- |
| — | Phân hệ chủ yếu dùng controller hoặc service của khung phần mềm. | — |

<a id="hng-dn-v-a-t-ai-liu-model-test"></a>

### Mô hình và kiểm thử

- **Mô hình chính:** Chưa ánh xạ mô hình riêng..
- **Kiểm thử liên quan:** Chưa tìm thấy kiểm thử theo tên phân hệ; kiểm tra chỉ mục hàm để tìm kiểm thử gián tiếp..

## Lưu ý khi tra cứu

- “Trực tiếp trong hàm” nghĩa là quy tắc kiểm tra đang nằm trong bộ điều khiển; sửa quy tắc sẽ ảnh hưởng trực tiếp biểu mẫu/API tương ứng.
- `FormRequest` là lớp yêu cầu kiểm tra dữ liệu tách riêng; cần mở lớp này trước khi sửa bộ điều khiển.
- Service kiểm tra dữ liệu thường chứa quy tắc nghiệp vụ chéo như tồn kho/số lượng, không thay thế việc kiểm tra định dạng đầu vào.
- Một service có thể xuất hiện ở nhiều phân hệ vì đó là thành phần phụ thuộc dùng chung.
