# Luồng nghiệp vụ hiện hành

Tài liệu này mô tả hành vi đang áp dụng. Đặc tả đầy đủ nằm trong `Document.md`.

## Mua hàng → Nhập kho → Thanh toán

```text
Tạo PO → Duyệt PO → Tạo phiếu nhập → Kho xác nhận
→ Thông báo kế toán → Kế toán duyệt
→ Tăng tồn gồm VAT + tăng công nợ NCC
→ Tạo giao dịch chi gắn PO → Duyệt → giảm số dư và công nợ
```

## Bán hàng → Xuất kho → Thu tiền

```text
Tạo SO → Duyệt SO → Tạo phiếu xuất → Kho xác nhận
→ Thông báo kế toán → Kế toán duyệt
→ Giảm tồn theo giá nhập gần nhất + tăng công nợ khách hàng
→ Tạo giao dịch thu gắn SO → Duyệt → tăng số dư và giảm công nợ
```

Kế toán không được duyệt hoặc từ chối phiếu khi phiếu còn chờ kho xác nhận. PO/SO được duyệt chưa tự làm thay đổi tồn hoặc công nợ.

## Tỷ giá

- VND là tiền cơ sở, tỷ giá bằng 1 và bị khóa.
- Ngoại tệ được thay đổi tỷ giá kể cả khi đã sử dụng.
- Mỗi thay đổi tạo một bản ghi lịch sử theo ngày.
- Chứng từ lưu tỷ giá tại thời điểm tạo/cập nhật; tỷ giá mới không sửa hồi tố chứng từ cũ.

## Giá trị tồn kho

- Phiếu nhập được kế toán duyệt làm tăng tồn theo giá trị thực nhập gồm VAT.
- Giá vốn xuất và chuyển kho dùng giá nhập của đơn mua gần nhất.
- Không tính giá vốn bình quân và không cho tồn âm.

## Giao dịch và lịch sử thanh toán

- Giao dịch phải cho biết tiền mặt/chuyển khoản và tài khoản thu/chi.
- Giao dịch công nợ nên gắn PO/SO để truy vết khoản tiền thuộc đơn nào.
- Chi tiết giao dịch, sổ giao dịch và lịch sử thanh toán khách hàng/NCC hiển thị đơn liên quan.

## Nhân sự và vai trò

- Nhân sự hoạt động ngay sau khi tạo; không gửi phiếu và không cần duyệt.
- Không còn role khởi tạo `HR` và `Manager`.
- Phạm vi thao tác được quyết định bằng các role nghiệp vụ cụ thể và permission.

