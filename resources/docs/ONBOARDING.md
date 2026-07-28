# Onboarding dự án

## Dự án là gì?

Hệ thống ERP nội bộ gồm quản trị, mua hàng, bán hàng, kho và kế toán. Laravel cung cấp web/API; Vue 3 + Inertia hiển thị giao diện; dữ liệu được cô lập theo công ty và thao tác được kiểm soát bằng permission.

## Chạy local

Yêu cầu: PHP/Composer, Node.js/npm và database đúng cấu hình `.env`.

```powershell
composer install
npm install
php artisan migrate --seed
npm run dev
php artisan serve
```

Nếu project dùng queue hoặc realtime, chạy thêm tiến trình tương ứng theo cấu hình môi trường. Không đưa secret hoặc tài khoản thật vào tài liệu.

## Thứ tự đọc code khi nhận task

```text
Màn hình Vue
→ Axios endpoint
→ routes/api.php
→ Controller
→ Service/Repository
→ Model và migration
→ Feature test
```

Tìm nhanh bằng `rg`; xem route runtime bằng `php artisan route:list`.

## Bài tập làm quen đề xuất

1. Đăng nhập và đi qua năm phân hệ trên thanh điều hướng.
2. Tạo một PO, duyệt đơn, tạo phiếu nhập, kho xác nhận và kế toán duyệt.
3. Tạo một SO và thực hiện luồng xuất tương tự.
4. Tạo giao dịch thu/chi gắn với đơn và kiểm tra sổ giao dịch/công nợ.
5. Chạy một Feature test liên quan trước khi sửa một task nhỏ.

## Definition of Done

- Nghiệp vụ đúng và trạng thái trung gian không làm thay đổi số liệu.
- Backend kiểm tra permission và `company_id`.
- Có test cho luồng chính và lỗi quan trọng.
- PHP lint/test và frontend build thành công.
- Tài liệu, changelog và ADR được cập nhật nếu hành vi hoặc quyết định thay đổi.

