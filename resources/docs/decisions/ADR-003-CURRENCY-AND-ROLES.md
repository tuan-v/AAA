# ADR-003: Chính sách tiền tệ và vai trò mặc định

- Trạng thái: Chấp nhận
- Ngày: 2026-07-28

## Quyết định

- VND là tiền cơ sở, tỷ giá luôn bằng 1 và không được sửa.
- Ngoại tệ được thay đổi tỷ giá; mọi thay đổi được lưu lịch sử và không sửa hồi tố chứng từ.
- Không khởi tạo role chung `HR` và `Manager`; dùng role nghiệp vụ và permission cụ thể.
- Tạo nhân sự không phát sinh phiếu duyệt.

## Hệ quả

Seeder, validation và giao diện không được phụ thuộc vào hai role đã bỏ. Quyền truy cập phải kiểm tra bằng permission. Lịch sử tỷ giá là nguồn truy vết thay đổi ngoại tệ.
