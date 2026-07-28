# ADR-001: Phiếu kho cần kho xác nhận và kế toán duyệt

- Trạng thái: Chấp nhận
- Ngày: 2026-07-28

## Bối cảnh

Kho chịu trách nhiệm kiểm tra hàng và số lượng; kế toán chịu trách nhiệm ghi nhận giá trị, công nợ và tác động số liệu chính thức. Một bước duyệt không thể hiện rõ trách nhiệm và cho phép kế toán thao tác quá sớm.

## Quyết định

Áp dụng luồng tạo phiếu → kho xác nhận → thông báo kế toán → kế toán duyệt/từ chối. Trước khi kho xác nhận, kế toán không được thao tác. Chỉ duyệt của kế toán mới cập nhật tồn, sổ biến động và công nợ.

## Hệ quả

Phải lưu trạng thái/thời điểm/người xác nhận của từng bước, kiểm tra permission ở backend, gửi thông báo và chống duyệt lặp trong database transaction.

