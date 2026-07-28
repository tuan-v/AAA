# 📖 API Documentation — Hệ thống ERP nội bộ

Tài liệu này hỗ trợ Frontend, Backend, QA và AI/Coding Assistant tra cứu API đang được triển khai trong repository. Danh sách được đối chiếu với `php artisan route:list --path=api` ngày 27/07/2026.

> Nguồn đúng cuối cùng: `routes/api.php` → method controller → validation/service/model. Nếu tài liệu lệch code, ưu tiên code và cập nhật lại tài liệu này.

---

## 📌 Thông tin chung

| Thuộc tính | Giá trị hiện tại |
|---|---|
| Base URL local | `http://127.0.0.1:8000/api` |
| Định dạng | `application/json`; upload dùng `multipart/form-data` |
| Web authentication | Laravel session cookie + Sanctum stateful + CSRF |
| Authorization | Spatie Permission tại từng route |
| Tenant | dữ liệu giới hạn theo `company_id` của user đăng nhập |
| Rate limit | middleware `throttle:api` |
| API version | chưa version hóa; hiện không có `/api/v1` |
| Route source | `routes/api.php` |
| FE HTTP client | Axios khởi tạo tại `resources/js/bootstrap.js` |

API hiện dành cho Vue/Inertia first-party web client. Không gửi Bearer token tự phát và không lưu token trong `localStorage`. Client ngoài web/mobile cần một contract xác thực/versioning riêng trước khi tích hợp.

### Xác thực từ web client

```text
GET /login → POST /login → Laravel tạo session cookie
                         ↓
Axios gửi cookie + X-XSRF-TOKEN
                         ↓
/api/* → auth:sanctum → permission middleware → controller
```

Route login/logout/password/OAuth nằm trong `routes/auth.php` và không mang prefix `/api`.

---

## 🎯 Response, lỗi và phân trang

Response hiện chưa có một wrapper duy nhất. FE phải đọc đúng contract của endpoint đang dùng; không giả định luôn có `success`, `code` hoặc `data`.

### Paginator Laravel thường gặp

```json
{
  "current_page": 1,
  "data": [],
  "first_page_url": "http://127.0.0.1:8000/api/example?page=1",
  "from": 1,
  "last_page": 5,
  "per_page": 10,
  "to": 10,
  "total": 42
}
```

### Lỗi validation Laravel

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Nội dung lỗi"]
  }
}
```

| HTTP status | Ý nghĩa |
|---|---|
| `200` | đọc/cập nhật/action thành công |
| `201` | tạo mới nếu controller áp dụng |
| `401` | chưa đăng nhập/session hết hạn |
| `403` | thiếu permission hoặc không được thao tác |
| `404` | ID không tồn tại/không thuộc company |
| `422` | validation hoặc state transition không hợp lệ |
| `429` | vượt rate limit |
| `500` | lỗi ngoài dự kiến; xem Laravel log |

Query danh sách thường hỗ trợ `page`, `per_page`, `search`, `status` và filter riêng từng module. Kiểm tra method `index()` tương ứng vì tên filter chưa hoàn toàn đồng nhất.

---

## 🗺️ Mục lục module API

1. [Dashboard và user hiện tại](#1-dashboard-và-user-hiện-tại)
2. [Nhân sự, phòng ban và chức vụ](#2-nhân-sự-phòng-ban-và-chức-vụ)
3. [Vai trò và quyền hạn](#3-vai-trò-và-quyền-hạn)
4. [Mua hàng](#4-mua-hàng)
5. [Bán hàng](#5-bán-hàng)
6. [Kho](#6-kho)
7. [Kế toán](#7-kế-toán)
8. [Thông báo và realtime](#8-thông-báo-và-realtime)
9. [Audit log](#9-audit-log)
10. [Danh mục dùng chung](#10-danh-mục-dùng-chung)
11. [Quy tắc tích hợp và kiểm thử](#11-quy-tắc-tích-hợp-và-kiểm-thử)

Ký hiệu endpoint:

- `{id}`, `{user}`, `{order}`... là route parameter.
- `permissionA|permissionB` nghĩa là có ít nhất một trong các quyền.
- Mọi endpoint dưới đây yêu cầu đăng nhập, trừ khi ghi rõ khác.

---

## 1. Dashboard và user hiện tại

| Method | Endpoint | Chức năng | Controller |
|---|---|---|---|
| `GET` | `/user` | user của session hiện tại | closure `routes/api.php` |
| `GET` | `/dashboard/overview` | tổng quan ERP | `DashboardController@overview` |
| `GET` | `/dashboard/{module}` | dashboard module | `DashboardController@module` |

`{module}` chỉ nhận `purchase`, `sale`, `warehouse`, `accountant`. FE gọi tại `Pages/DashBoard.vue` và `components/dashboard/ModuleDashboard.vue`.

---

## 2. Nhân sự, phòng ban và chức vụ

### 2.1 Người dùng/nhân sự

Base path thực tế là `/users/user`, không phải `/users`.

| Method | Endpoint | Chức năng | Permission | Controller |
|---|---|---|---|---|
| `GET` | `/users/user` | danh sách có phân trang/filter | `nhan_su.xem` | `API\UserController@index` |
| `POST` | `/users/user` | tạo nhân sự | `nhan_su.them` | `API\UserController@store` |
| `GET` | `/users/user/{id}` | chi tiết | `nhan_su.xem` | `API\UserController@show` |
| `PUT` | `/users/user/{id}` | cập nhật | `nhan_su.sua` | `API\UserController@update` |
| `DELETE` | `/users/user/{id}` | xóa | `nhan_su.xoa` | `API\UserController@destroy` |
| `GET` | `/users/roles` | selector role | `nhan_su.xem` | `API\UserController@role` |
| `PATCH` | `/users/{user}/status` | khóa/mở | `nhan_su.khoa` | `API\UserController@toggleStatus` |
| `PATCH` | `/users/{user}/approve` | duyệt yêu cầu | `nhan_su.duyet` | `API\UserController@approve` |
| `PATCH` | `/users/{user}/reject` | từ chối | `nhan_su.tu_choi` | `API\UserController@reject` |
| `PATCH` | `/users/{user}/resubmit` | yêu cầu gửi lại | `nhan_su.sua` | `API\UserController@resubmit` |

Payload tạo/cập nhật phải theo validation trong `API/UserController`. Các trường FE đang gửi được xây dựng tại `Pages/Manage/UserForm.vue`; role, department và position lấy từ các selector tương ứng. Workflow trạng thái có deadline hết hạn do scheduler tại `routes/console.php`.

### 2.2 Phòng ban

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/departments` | danh sách/search/pagination | `nhan_su.xem` |
| `POST` | `/departments` | tạo | `phong_ban.them` |
| `PUT` | `/departments/{department}` | cập nhật | `nhan_su.sua` |
| `DELETE` | `/departments/{department}` | xóa | `nhan_su.xoa` |
| `GET` | `/departments/all` | selector đầy đủ | `nhan_su.xem` |
| `GET` | `/departments/managers` | danh sách manager khả dụng | `nhan_su.xem` |

Controller: `DepartmentController`. FE: `Pages/Manage/Department/Index.vue`.

### 2.3 Chức vụ

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/positions` | danh sách có phân trang | `nhan_su.xem` |
| `POST` | `/positions` | tạo | `chuc_vu.them` |
| `PUT` | `/positions/{position}` | cập nhật | `nhan_su.sua` |
| `DELETE` | `/positions/{position}` | xóa | `nhan_su.xoa` |
| `GET` | `/positions/all?department_id={id}` | selector theo phòng ban | `nhan_su.xem` |

Controller: `PositionController`. FE: `Pages/Manage/Position/Index.vue` và `UserForm.vue`.

---

## 3. Vai trò và quyền hạn

### 3.1 Role

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/roles` | danh sách role | `vai_tro.xem` |
| `POST` | `/roles` | tạo role và gán quyền | `vai_tro.them` |
| `PUT` | `/roles/{id}` | cập nhật role/quyền | `vai_tro.sua` |
| `DELETE` | `/roles/{id}` | xóa role | `vai_tro.xoa` |

### 3.2 Permission

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/permissions` | danh sách phân trang | `quyen.xem` |
| `GET` | `/permissions/all` | toàn bộ quyền để gán role | `quyen.xem` |
| `POST` | `/permissions` | tạo quyền | `quyen.them` |
| `PUT` | `/permissions/{id}` | cập nhật | `quyen.sua` |
| `DELETE` | `/permissions/{id}` | xóa | `quyen.xoa` |

Controller: `RoleController`, `PermissionController`. Permission đặt theo dạng `module.hanh_dong`; nguồn seed là `database/seeders/PermissionSeeder.php` và `RolePermissionSeeder.php`.

---

## 4. Mua hàng

Base path: `/purchase`.

### 4.1 Nhà cung cấp

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/purchase/suppliers` | danh sách/filter/pagination | `nha_cung_cap.xem` |
| `GET` | `/purchase/suppliers/all` | selector | `nha_cung_cap.xem|giao_dich.them|giao_dich.sua` |
| `GET` | `/purchase/suppliers/{supplier}` | thông tin cơ bản | `nha_cung_cap.xem` |
| `GET` | `/purchase/suppliers/{id}/detail` | chi tiết, đơn và công nợ | `nha_cung_cap.xem_chi_tiet|cong_no_nha_cung_cap.xem_chi_tiet` |
| `POST` | `/purchase/suppliers` | tạo | `nha_cung_cap.them` |
| `PUT` | `/purchase/suppliers/{supplier}` | cập nhật | `nha_cung_cap.sua` |
| `DELETE` | `/purchase/suppliers/{supplier}` | xóa | `nha_cung_cap.xoa` |
| `PATCH` | `/purchase/suppliers/{id}/status` | khóa/mở | `nha_cung_cap.khoa` |

Controller: `SupplierController`. Form gửi thông tin nhận diện, liên hệ, địa chỉ và tiền tệ; xem payload thực tại `Pages/Purchase/Supplier/SupplierForm.vue`.

### 4.2 Danh mục, đơn vị và sản phẩm

Ba resource dùng cùng mẫu CRUD:

| Resource | Base endpoint | Controller | Permission prefix |
|---|---|---|---|
| Danh mục | `/purchase/categories` | `CategoryController` | `danh_muc_mua_hang` |
| Đơn vị | `/purchase/units` | `UnitController` | `don_vi_mua_hang` |
| Sản phẩm | `/purchase/products` | `ProductController` | `san_pham_mua_hang` |

| Method | Suffix | Chức năng | Permission action |
|---|---|---|---|
| `GET` | `/` | danh sách | `.xem` |
| `POST` | `/` | tạo | `.them` |
| `GET` | `/{resource}` | chi tiết | `.xem` |
| `PUT` | `/{resource}` | cập nhật | `.sua` |
| `DELETE` | `/{resource}` | xóa | `.xoa` |
| `PATCH` | `/{id}/status` | khóa/mở | `.khoa` |
| `GET` | `/select` | selector; category/unit | `.xem` |

Product upload ảnh dùng `multipart/form-data`. FE đặt payload tại `Purchase/Product/ProductForm.vue`; selector category/unit hỗ trợ `active_only=1`.

### 4.3 Đơn mua

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/purchase/orders` | danh sách/filter | `don_mua.xem|giao_dich.them|giao_dich.sua` |
| `POST` | `/purchase/orders` | tạo đơn | `don_mua.them` |
| `GET` | `/purchase/orders/{order}` | chi tiết | `don_mua.xem_chi_tiet|cong_no_nha_cung_cap.xem_chi_tiet` |
| `PUT` | `/purchase/orders/{order}` | cập nhật | `don_mua.sua` |
| `DELETE` | `/purchase/orders/{order}` | xóa | `don_mua.xoa` |
| `POST` | `/purchase/orders/{id}/approve` | duyệt, ghi nhận nghĩa vụ NCC | `don_mua.duyet` |
| `POST` | `/purchase/orders/{id}/cancel` | hủy | `don_mua.huy` |
| `GET` | `/purchase/orders/{id}/stock-in-data` | dữ liệu chuẩn bị nhập kho | `don_mua.xem_chi_tiet` |

Payload đơn gồm header (NCC, tiền tệ/tỷ giá, ngày, ghi chú...) và mảng items (product, unit, quantity, price, tax/discount tùy implementation). Contract chính xác nằm tại `PurchaseOrderController@store|update` và `Pages/Purchase/Order/PurchaseOrderForm.vue`.

Không gọi approve lặp. BE phải kiểm tra trạng thái hiện hành và transaction vì duyệt liên quan công nợ/thông báo.

---

## 5. Bán hàng

Base path: `/sale`.

### 5.1 Khách hàng

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/sale/customers` | danh sách/filter | `khach_hang.xem` |
| `GET` | `/sale/customers/all` | selector | `khach_hang.xem|giao_dich.them|giao_dich.sua` |
| `GET` | `/sale/customers/next-code` | sinh mã tiếp theo | `khach_hang.xem` |
| `GET` | `/sale/customers/{customer}` | thông tin cơ bản | `khach_hang.xem` |
| `GET` | `/sale/customers/{id}/detail` | chi tiết, đơn/công nợ | `khach_hang.xem|cong_no_khach_hang.xem_chi_tiet` |
| `POST` | `/sale/customers` | tạo | `khach_hang.them` |
| `PUT` | `/sale/customers/{customer}` | cập nhật | `khach_hang.sua` |
| `DELETE` | `/sale/customers/{customer}` | xóa | `khach_hang.xoa` |
| `PATCH` | `/sale/customers/{customer}/status` | khóa/mở | `khach_hang.khoa` |
| `POST` | `/sale/customers/{id}/quick-order` | tạo nhanh đơn bán | `don_ban.them` |

### 5.2 Đơn bán

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/sale/orders` | danh sách/filter | `don_ban.xem|giao_dich.them|giao_dich.sua` |
| `POST` | `/sale/orders` | tạo | `don_ban.them` |
| `GET` | `/sale/orders/{order}` | chi tiết | `don_ban.xem_chi_tiet|cong_no_khach_hang.xem_chi_tiet` |
| `PUT` | `/sale/orders/{order}` | cập nhật | `don_ban.sua` |
| `DELETE` | `/sale/orders/{order}` | xóa | `don_ban.xoa` |
| `POST` | `/sale/orders/{id}/approve` | duyệt/ghi nhận công nợ | `don_ban.duyet` |
| `POST` | `/sale/orders/{id}/cancel` | hủy | `don_ban.huy` |

Payload được xây tại `Pages/Sale/Order/SaleOrderForm.vue`; selector dùng `/products/for-select`, `/currencies/for-select`, customer detail và địa chỉ. Duyệt đơn có thể tạo/cập nhật công nợ khách và notification.

---

## 6. Kho

API kho có hai base path do lịch sử phát triển: `/warehouses` cho master kho và `/warehouse/*` cho nghiệp vụ.

### 6.1 Master kho

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/warehouses` | danh sách | `kho.xem` |
| `GET` | `/warehouses/all` | selector | `kho.xem|san_pham_mua_hang.xem` |
| `GET` | `/warehouses/{warehouse}` | thông tin cơ bản | `kho.xem` |
| `GET` | `/warehouses/{warehouse}/detail` | chi tiết và tồn | `kho.xem` |
| `POST` | `/warehouses` | tạo | `kho.them` |
| `PUT` | `/warehouses/{warehouse}` | cập nhật | `kho.sua` |
| `DELETE` | `/warehouses/{warehouse}` | xóa | `kho.xoa` |
| `PATCH` | `/warehouses/{warehouse}/status` | khóa/mở | `kho.khoa` |
| `PATCH` | `/warehouse/{id}/status` | alias status cũ | `kho.khoa` |

### 6.2 Master data theo ngữ cảnh kho

| Resource | Base endpoint | Controller | Permission prefix |
|---|---|---|---|
| Danh mục | `/warehouse/categories` | `CategoryController` | `danh_muc_kho` |
| Đơn vị | `/warehouse/units` | `UnitController` | `don_vi_kho` |
| Sản phẩm | `/warehouse/products` | `ProductController` | `san_pham_kho` |

Mẫu endpoint CRUD/status/select giống phần Mua hàng, nhưng permission theo kho.

### 6.3 Đơn chờ xử lý kho và tồn

| Method | Endpoint | Chức năng | Permission | Controller |
|---|---|---|---|---|
| `GET` | `/warehouse/orders` | đơn mua chờ nhập | `phieu_kho.xem` | `PurchaseOrderController@warehouseIndex` |
| `GET` | `/saleorders/warehouse` | đơn bán chờ xuất | `phieu_kho.xem` | `SalesOrderController@warehouseIndex` |
| `GET` | `/warehouse/orders/{id}/stock-in` | dữ liệu tạo phiếu nhập | `phieu_kho.them` | `PurchaseOrderController@stockInData` |
| `GET` | `/warehouse/orders/{id}/stock-out` | dữ liệu tạo phiếu xuất | `phieu_kho.them` | `SalesOrderController@stockOutData` |
| `GET` | `/available-for-export` | lượng đơn/sản phẩm có thể xuất | `phieu_kho.xem` | `SalesOrderController@availableForExport` |
| `GET` | `/warehouse/inventory` | tồn tổng hợp | `kho.xem` | `WarehouseInventoryController@index` |
| `GET` | `/warehouse/inventory-movements` | sổ biến động | `kho.xem` | `InventoryMovementController@index` |
| `GET` | `/warehouse/stocks` | tồn theo filter | `kho.xem` | `WarehouseController@getStocks` |

### 6.4 Phiếu nhập/xuất

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/warehouse/slips` | danh sách; filter `type=import|export` | `phieu_kho.xem` |
| `POST` | `/warehouse/slips` | tạo phiếu | `phieu_kho.them` |
| `GET` | `/warehouse/slips/{slip}` | chi tiết | `phieu_kho.xem_chi_tiet` |
| `PUT` | `/warehouse/slips/{slip}` | cập nhật | `phieu_kho.sua` |
| `POST` | `/warehouse/slips/{id}/approve` | duyệt và cập nhật tồn | `phieu_kho.duyet` |
| `POST` | `/warehouse/slips/{id}/reject` | từ chối | `phieu_kho.tu_choi` |

Tạo phiếu gửi loại phiếu, order liên quan, warehouse và items/quantity. Payload thực tế nằm tại `Slip/Purchasecreate.vue`, `Slip/Salecreate.vue` và `WarehouseSlipController`. Approve là action nhạy cảm, phải đảm bảo đủ tồn với phiếu xuất và không duyệt lặp.

### 6.5 Chuyển kho

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/warehouse/transfers` | danh sách | `chuyen_kho.xem` |
| `POST` | `/warehouse/transfers` | tạo | `chuyen_kho.them` |
| `POST` | `/warehouse/transfers/{id}/approve` | duyệt, chuyển tồn | `chuyen_kho.duyet` |
| `POST` | `/warehouse/transfers/{id}/cancel` | hủy | `chuyen_kho.huy` |

Payload tạo gồm kho nguồn, kho đích, ghi chú và items. Hai kho phải khác nhau; quantity phải hợp lệ và không vượt tồn khả dụng. Controller dùng `OrderQuantityValidationService`, `CodeGeneratorService`, `InventoryMovementService`.

---

## 7. Kế toán

Base path: `/accountant`.

### 7.1 Tiền tệ và tỷ giá

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/accountant/currencies` | danh sách | `tien_te.xem` |
| `GET` | `/accountant/currencies/all` | selector | `tien_te.xem` |
| `POST` | `/accountant/currencies` | tạo | `tien_te.them` |
| `GET` | `/accountant/currencies/{currency}` | chi tiết | `tien_te.xem` |
| `PUT` | `/accountant/currencies/{currency}` | cập nhật | `tien_te.sua` |
| `DELETE` | `/accountant/currencies/{currency}` | xóa | `tien_te.xoa` |
| `PATCH` | `/accountant/currencies/{currency}/toggle-status` | khóa/mở | `tien_te.khoa` |
| `GET` | `/accountant/currencies/{currency}/rates` | lịch sử tỷ giá | `tien_te.xem_lich_su` |
| `POST` | `/accountant/currencies/{currency}/rates` | thêm tỷ giá | `tien_te.sua` |

### 7.2 Ngân hàng

CRUD `/accountant/banks`, chi tiết `/{bank}`, status `PATCH /{bank}/toggle-status`; permission lần lượt `ngan_hang.xem|them|sua|xoa|khoa`. Upload logo dùng multipart. Controller: `BankController`.

### 7.3 Tài khoản tiền/quỹ và sổ

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET/POST` | `/accountant/accounts` | danh sách/tạo | `tai_khoan.xem|them` theo method |
| `GET` | `/accountant/accounts/all` | selector | `tai_khoan.xem` |
| `GET/PUT/DELETE` | `/accountant/accounts/{account}` | chi tiết/sửa/xóa | `tai_khoan.xem|sua|xoa` |
| `PATCH` | `/accountant/accounts/{account}/toggle-status` | khóa/mở | `tai_khoan.khoa` |
| `POST` | `/accountant/accounts/{id}/rebuild-balance` | dựng lại số dư từ sổ | `tai_khoan.sua` |
| `GET` | `/accountant/account-ledgers` | sổ nhiều tài khoản | `giao_dich.xem` |
| `GET` | `/accountant/accounts/{account}/ledger` | sổ một tài khoản | `giao_dich.xem` |

`rebuild-balance` là endpoint quản trị/sửa dữ liệu: không gọi từ UI thông thường nếu chưa xác định nguyên nhân lệch số dư.

### 7.4 Loại giao dịch

CRUD `/accountant/transaction-categories`, selector `GET /active`; permission prefix `loai_giao_dich` với action `xem|them|sua|xoa`. Controller dùng `TransactionCategoryService` và repository.

### 7.5 Giao dịch thu/chi

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/accountant/transactions` | danh sách/filter | `giao_dich.xem` |
| `POST` | `/accountant/transactions` | tạo | `giao_dich.them` |
| `GET` | `/accountant/transactions/order-outstanding` | order/công nợ còn phải thu/chi | `giao_dich.xem` |
| `GET` | `/accountant/transactions/{transaction}` | chi tiết | `giao_dich.xem` |
| `PUT` | `/accountant/transactions/{transaction}` | cập nhật | `giao_dich.sua` |
| `DELETE` | `/accountant/transactions/{transaction}` | xóa | `giao_dich.xoa` |
| `POST` | `/accountant/transactions/{transaction}/approve` | duyệt, cập nhật tiền/sổ/công nợ | `giao_dich.duyet` |
| `POST` | `/accountant/transactions/{transaction}/reject` | từ chối | `giao_dich.tu_choi` |

Payload được xây tại `TransactionForm.vue`: loại thu/chi, category, account, currency/rate, amount, transaction date, đối tượng customer/supplier và order liên quan tùy trường hợp. `TransactionService` là contract nghiệp vụ cuối cùng.

Ví dụ reject:

```json
{
  "reason": "Thông tin chứng từ chưa hợp lệ"
}
```

### 7.6 Công nợ và báo cáo

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/accountant/customers-debt` | tổng hợp phải thu | `cong_no_khach_hang.xem` |
| `GET` | `/accountant/customers-debt/{id}/detail` | chi tiết khách | `cong_no_khach_hang.xem_chi_tiet` |
| `GET` | `/accountant/suppliers-debt` | tổng hợp phải trả | `cong_no_nha_cung_cap.xem` |
| `GET` | `/accountant/suppliers-debt/{id}/detail` | chi tiết NCC | `cong_no_nha_cung_cap.xem_chi_tiet` |
| `GET` | `/accountant/profit-loss-report` | báo cáo lãi/lỗ theo filter kỳ | `giao_dich.xem` |

---

## 8. Thông báo và realtime

### Notification REST API

| Method | Endpoint | Chức năng |
|---|---|---|
| `GET` | `/notifications` | danh sách notification của user/company theo filter |
| `GET` | `/notifications/unread-count` | số chưa đọc |
| `POST` | `/notifications/{notification}/mark-as-read` | đánh dấu một thông báo |
| `POST` | `/notifications/mark-all-read` | đánh dấu tất cả |
| `DELETE` | `/notifications/{notification}` | xóa khỏi danh sách được phép |

Controller: `NotificationController` → `NotificationService`. FE: `components/Notifications/NotificationCenter.vue`.

### WebSocket

Private channels được authorize qua `/broadcasting/auth`, không phải REST resource:

```text
user.{id}.{sub}.notifications
company.{companyId}.{sub}.notifications
company.{companyId}.{id}.{sub}.notifications
company.{companyId}.{sub}.data
```

Client không tự chọn companyId tùy ý; `BroadcastController` đối chiếu user, company và subdomain. Events chính: `NotificationCreated`, `CompanyDataChanged`.

---

## 9. Audit log

| Method | Endpoint | Chức năng | Permission |
|---|---|---|---|
| `GET` | `/audit-logs` | danh sách/filter/pagination | `nhat_ky.xem` |
| `GET` | `/audit-logs/{auditLog}` | chi tiết before/after/context | `nhat_ky.xem` |
| `GET` | `/audit-logs/trace` | truy vết chuỗi hoạt động | `nhat_ky.xem` |

Controller: `AuditLogController`. Log được ghi bởi middleware/service và Spatie Activity Log; endpoint chỉ đọc.

---

## 10. Danh mục dùng chung

| Method | Endpoint | Chức năng | Quyền đáng chú ý |
|---|---|---|---|
| `GET` | `/products/for-select` | selector sản phẩm | `san_pham_mua_hang.xem|don_ban.xem|khach_hang.xem` |
| `GET` | `/currencies/for-select` | selector tiền tệ/tỷ giá | quyền NCC/đơn/khách/giao dịch phù hợp |
| `GET` | `/provinces` | tỉnh/thành | đăng nhập |
| `GET` | `/provinces/{province}/wards` | phường/xã | đăng nhập |
| `GET` | `/categories` | alias danh mục mua | `danh_muc_mua_hang.xem` |
| `GET` | `/units` | alias đơn vị mua | `don_vi_mua_hang.xem` |
| `PATCH` | `/units/{id}/status` | alias status đơn vị kho | `don_vi_kho.khoa` |
| `PATCH` | `/currencies/{currency}/toggle-status` | alias status tiền tệ | `tien_te.khoa` |
| `GET` | `/company/create` | dữ liệu/form tạo company | theo route hiện tại |

Các endpoint `/all` và `/for-select` chỉ dành cho selector. Không dùng thay endpoint phân trang cho bảng lớn nếu không cần toàn bộ dữ liệu.

---

## 11. Quy tắc tích hợp và kiểm thử

### Axios web client

```js
import axios from "axios";

const response = await axios.get("/api/purchase/orders", {
  params: { page: 1, per_page: 10, search: "PO" },
});
```

Không hard-code host trong Page. Dùng relative URL để session/CSRF đi cùng origin và cấu hình môi trường xử lý domain.

### Checklist khi gọi API

1. User đã đăng nhập và có đúng permission.
2. Dùng đúng base path (`/warehouses` khác `/warehouse`).
3. Với list, đọc `response.data.data` nếu endpoint trả Laravel paginator; xác minh bằng Network tab/controller.
4. Gửi `multipart/form-data` cho form có file; JSON cho phần còn lại.
5. Hiển thị lỗi `422.errors` theo field và xử lý `401/403/429` riêng.
6. Disable nút khi request đang chạy để tránh create/approve lặp.
7. Sau mutation, refresh list/detail đúng module hoặc chờ event realtime có chủ đích.
8. Không gửi/nhận `company_id` như nguồn tin cậy; BE lấy tenant từ session.

### Kiểm tra route và contract

```bash
php artisan route:list --path=api --except-vendor
php artisan test
php artisan test --filter=TransactionFlowTest
php artisan test --filter=InventoryLifecycleEndToEndTest
```

### Khi thêm endpoint mới

- Thêm route + `auth:sanctum` group + throttle + permission.
- Dùng Form Request/validation, tenant scope và API Resource/response contract đã chọn.
- Đặt business logic nhiều bảng trong service/transaction.
- Thêm audit, notification, realtime và queue nếu nghiệp vụ cần.
- Viết Feature test cho success, validation, permission, tenant isolation, state invalid và rollback.
- Thêm endpoint vào đúng module của tài liệu này và cập nhật `PROJECT_INDEX.md` nếu thay đổi kiến trúc.

---

## ⚠️ Những API không tồn tại trong phiên bản hiện tại

Để tránh nhầm với tài liệu định hướng hoặc hệ thống khác, repository hiện không có:

- `/api/v1/*`.
- Bearer-token login/refresh-token/device-session API.
- FCM device token API.
- Tasks, projects, attendance, vehicles, attachments API độc lập.
- Swagger/Scramble tại `/docs/api`.

Muốn hỗ trợ mobile/integration bên ngoài cần thiết kế versioned API riêng, không suy diễn từ session API trong tài liệu này.
