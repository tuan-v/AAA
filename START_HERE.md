# START HERE — Bắt đầu hoặc quay lại dự án

Đây là điểm vào duy nhất dành cho thành viên mới và người quay lại dự án sau một thời gian. Nếu tài liệu khác mã nguồn, ưu tiên route, migration, implementation và test hiện tại; sau đó cập nhật lại tài liệu.

## Lộ trình 30 phút

1. Đọc [tổng quan và cách chạy dự án](resources/docs/ONBOARDING.md).
2. Xem [bản đồ module](MODULE_INDEX.md) để biết chức năng nằm ở đâu.
3. Đọc [luồng nghiệp vụ hiện hành](resources/docs/BUSINESS_FLOWS.md).
4. Tra [thuật ngữ](resources/docs/GLOSSARY.md) khi gặp PO, SO, phiếu kho, công nợ hoặc sổ tài khoản.
5. Xem [CHANGELOG](resources/docs/CHANGELOG.md) để biết hệ thống đã thay đổi gì gần đây.
6. Khi sửa code, đọc phần module tương ứng trong [PROJECT_INDEX](PROJECT_INDEX.md) và kiểm tra API bằng [API_DOCUMENTATION](API_DOCUMENTATION.md).

## Bản đồ nhanh

| Phân hệ | URL | Frontend | Backend chính |
|---|---|---|---|
| Quản trị | `/dashboard`, `/user`, `/role` | `resources/js/Pages/Manage` | User/Role/Permission controllers |
| Mua hàng | `/purchase` | `resources/js/Pages/Purchase` | `PurchaseOrderController`, services mua hàng |
| Bán hàng | `/sale` | `resources/js/Pages/Sale` | `SalesOrderController`, services bán hàng |
| Kho | `/warehouse` | `resources/js/Pages/Warehouse` | `WarehouseSlipController`, inventory services |
| Kế toán | `/accountant` | `resources/js/Pages/Accountant` | `TransactionController`, `TransactionService`, debt/ledger services |
| Hướng dẫn người dùng | `/guide` | `resources/js/Pages/Guide` | Inertia route trong `routes/web.php` |

## Quy tắc không được quên

- Mọi query nghiệp vụ phải cô lập theo `company_id`.
- Frontend ẩn thao tác theo permission; backend luôn phải kiểm tra permission lại.
- Chứng từ chờ duyệt không được tác động tồn, công nợ hoặc số dư.
- Phiếu nhập/xuất đi theo luồng: tạo → kho xác nhận → kế toán duyệt → cập nhật số liệu.
- VND có tỷ giá 1 và bị khóa; ngoại tệ có lịch sử thay đổi tỷ giá.
- Nhập kho ghi giá trị gồm VAT; xuất/chuyển dùng giá nhập gần nhất, không tính bình quân.
- Thao tác nhiều bảng phải nằm trong database transaction và chống duyệt lặp.

## Trước khi kết thúc một thay đổi

```powershell
php artisan route:list
php artisan test
npm run build
```

Kiểm tra thêm: permission, cô lập công ty, migration, thông báo/realtime, tài liệu nghiệp vụ và test hồi quy.

