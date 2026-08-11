# Chỉ mục tìm lỗi theo triệu chứng

> Sinh tự động ngày **11/08/2026**. Quay lại [mục lục module hợp nhất](../MODULE_INDEX.md).

## Mục lục tìm lỗi theo triệu chứng

> Knowledge Base theo nguyên nhân gốc. Chọn câu hỏi gần nhất với điều người dùng báo; chỉ mở chỉ mục endpoint đầy đủ nếu các bước này chưa khoanh vùng được lỗi.

### Vì sao tạo/sửa đơn báo “số lượng không hợp lệ”?

- **Nguyên nhân thường gặp:** sản phẩm dùng đơn vị `allow_decimal = false` nhưng payload gửi số lượng lẻ.
- **Cách check:** đọc field 422 → mở [OrderQuantityValidationService::validate()](vscode://file/D:/clone/project-base/app/Services/OrderQuantityValidationService.php:10) → kiểm tra `Product → Unit → allow_decimal` và `items.*.quantity`.
- **Lưu ý:** Service này không kiểm tra tồn kho. Nếu lỗi nói không đủ tồn hoặc không thể xuất, dùng câu hỏi kế tiếp.

### Vì sao còn hàng nhưng không thể tạo phiếu xuất?

- **Nguyên nhân thường gặp:** tồn khả dụng khác tồn thực tế vì phiếu xuất `pending` đang giữ chỗ, chọn sai kho hoặc đơn đã xuất một phần.
- **Cách check:** đối chiếu `WarehouseProductStock.quantity` với lượng giữ chỗ → mở [availableForExport()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:245) và [stockOutData()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:923) → kiểm tra warehouse/product/company và lượng đã xuất.

### Vì sao PO/SO đã duyệt nhưng Kho không thấy?

- **Nguyên nhân thường gặp:** trạng thái chưa đúng, đơn đã xử lý hết, loại đơn bị loại khỏi danh sách, permission kho hoặc scope công ty.
- **Cách check:** xác nhận trạng thái `approved/partial` → tính lượng còn nhập/xuất sau các phiếu hiện có → kiểm tra endpoint danh sách chờ kho bằng tài khoản role Kho.

### Vì sao phiếu đã duyệt nhưng tồn hoặc công nợ chưa đổi?

- **Nguyên nhân thường gặp:** mới hoàn thành bước kho xác nhận, chưa qua kế toán duyệt; hoặc transaction duyệt kế toán đã rollback.
- **Cách check:** phân biệt [approve() của kho](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:818) với [accountantApprove()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:862) → tìm `InventoryMovement` và debt theo phiếu nguồn.
- **Ràng buộc:** [ADR-001](vscode://file/D:/clone/project-base/resources/docs/decisions/ADR-001-WAREHOUSE-ACCOUNTING-APPROVAL.md:1) quy định chỉ kế toán duyệt mới cập nhật tồn, movement và công nợ.

### Vì sao duyệt giao dịch xong nhưng công nợ không giảm?

- **Nguyên nhân thường gặp:** sai `type` (`receipt/payment`), sai category (`THU_KH/CHI_NCC`), thiếu customer/supplier hoặc không gắn đúng PO/SO.
- **Cách check:** mở [TransactionService::approve()](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:191) và [syncDebt()](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:850) → kiểm tra type/category/đối tượng/đơn → đối chiếu debt và `AccountLedger` của cùng transaction.

### Vì sao giá vốn xuất/chuyển không giống giá bình quân?

- Đây có thể không phải bug. [ADR-002](vscode://file/D:/clone/project-base/resources/docs/decisions/ADR-002-INVENTORY-COST.md:1) quy định dùng giá nhập gần nhất; giá trị nhập gồm VAT, không dùng bình quân gia quyền.
- **Cách check:** lần từ sản phẩm đến đơn mua/phiếu nhập gần nhất và đối chiếu `cost_price`, `cost_amount` của phiếu/movement.

### Vì sao thay tỷ giá làm số liệu mới khác chứng từ cũ?

- [ADR-003](vscode://file/D:/clone/project-base/resources/docs/decisions/ADR-003-CURRENCY-AND-ROLES.md:1) yêu cầu VND luôn bằng 1; ngoại tệ có lịch sử và không sửa hồi tố chứng từ.
- **Cách check:** phân biệt tỷ giá hiện hành trong `CompanyCurrencyRate` với `exchange_rate`/giá trị base snapshot trên chứng từ.

## Luồng trạng thái và điểm dễ phát sinh lỗi

> Các luồng dưới đây được đối chiếu với trạng thái đang ghi trong Controller/Service. Xem thêm [Luồng nghiệp vụ hiện hành](vscode://file/D:/clone/project-base/resources/docs/BUSINESS_FLOWS.md:1).

- **Đơn bán:** `draft → pending → approved → partial → completed`; có thể sang `cancelled` khi còn `draft/pending`. Điểm kiểm tra: [gửi duyệt](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:729), [duyệt](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:776), [hủy](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:832).
- **Đơn mua:** `pending → approved → partial → completed`; có thể sang `cancelled` khi còn `pending`. Điểm kiểm tra: [duyệt](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:526), [hủy](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:578).
- **Phiếu kho:** bắt đầu `pending`; kho xác nhận rồi kế toán duyệt. Khi kế toán duyệt, đơn liên quan được tính lại thành `approved/partial/completed`. Điểm kiểm tra: [kho duyệt](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:818), [kế toán duyệt](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:862).
- **Giao dịch:** `pending → approved` hoặc `pending → rejected`. Chỉ bước duyệt mới ghi nhận số dư/công nợ liên quan. Điểm kiểm tra: [duyệt giao dịch](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:195), [từ chối giao dịch](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:267).

### Thứ tự debug một chức năng

1. Mở dòng gọi trên **Trang/Component** và kiểm tra payload, HTTP method, URL.
2. Mở **Controller/Function**, kiểm tra quyền, validation và trạng thái đầu vào.
3. Mở **Service/model được gọi**, kiểm tra transaction, thay đổi dữ liệu và quan hệ.
4. Đối chiếu trạng thái trước/sau với luồng ở trên và kiểm tra trang bị ảnh hưởng trực tiếp/gián tiếp.
5. Chạy **kiểm thử liên quan**; nếu tài liệu báo chưa phát hiện test thì cần bổ sung test tái hiện lỗi trước khi sửa.
