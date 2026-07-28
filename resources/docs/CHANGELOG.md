# Changelog nghiệp vụ

## 2026-07-28

- Bổ sung module hướng dẫn sử dụng tại `/guide`.
- Phiếu nhập/xuất chuyển sang hai bước: kho xác nhận rồi kế toán duyệt.
- Kế toán không được thao tác khi phiếu còn chờ kho; nhận thông báo sau khi kho xác nhận.
- Chỉ khi kế toán duyệt mới cập nhật tồn kho và công nợ.
- Giá trị nhập kho bao gồm VAT.
- Giá vốn xuất/chuyển kho dùng giá nhập gần nhất, không tính bình quân.
- VND bị khóa tỷ giá 1; ngoại tệ được đổi tỷ giá và lưu lịch sử thay đổi.
- Loại bỏ role khởi tạo `HR` và `Manager`.
- Nhân sự mới không còn luồng gửi/duyệt và hoạt động ngay khi tạo.
- Lịch sử thanh toán khách hàng/NCC hiển thị tài khoản, phương thức và đơn liên quan.
- Chi tiết và lịch sử giao dịch kế toán hiển thị PO/SO liên quan.

