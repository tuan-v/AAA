# ADR-002: Giá vốn dùng giá nhập gần nhất

- Trạng thái: Chấp nhận
- Ngày: 2026-07-28

## Quyết định

Giá trị nhập kho bao gồm VAT. Giá vốn xuất và chuyển kho dùng giá nhập của đơn mua gần nhất của sản phẩm, không tính bình quân gia quyền.

## Hệ quả

Mỗi lần nhập cần cập nhật giá mua gần nhất. Xuất và chuyển kho phải dùng cùng nguồn giá để sổ tồn nhất quán; test cũ về bình quân phải được cập nhật.

