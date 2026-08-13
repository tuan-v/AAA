# Chỉ mục tìm lỗi theo triệu chứng

> Sinh tự động ngày **12/08/2026**. Quay lại [mục lục module hợp nhất](../MODULE_INDEX.md).

## Mục lục tìm lỗi theo triệu chứng

> Chọn tiêu đề gần nhất với điều người dùng nhìn thấy. Mỗi mục đi từ dấu hiệu bên ngoài đến dữ liệu cần kiểm tra và file nên mở đầu tiên.
> Đây là tình huống chẩn đoán dựa trên mã nguồn, không mặc định là lỗi production đã được xác nhận.

### Tạo hoặc sửa đơn báo “số lượng không hợp lệ”

- **Người dùng thấy:** Form không lưu và API trả lỗi 422 tại trường số lượng.
- **Nguyên nhân thường gặp:** Sản phẩm dùng đơn vị không cho phép số lẻ (`allow_decimal = false`) nhưng dữ liệu gửi lên có số lượng thập phân.
- **Cách kiểm tra:** Đọc trường lỗi trong response 422 → kiểm tra `items.*.quantity` → mở quan hệ `Product → Unit` và xem `allow_decimal`.
- **Vị trí code:** [OrderQuantityValidationService::validate()](vscode://file/D:/clone/project-base/app/Services/OrderQuantityValidationService.php:10).

### Còn hàng nhưng không thể tạo phiếu xuất

- **Người dùng thấy:** Màn hình hiển thị còn tồn nhưng sản phẩm bị báo không đủ số lượng để xuất.
- **Nguyên nhân thường gặp:** Tồn khả dụng thấp hơn tồn thực tế do phiếu `pending` đang giữ chỗ, chọn sai kho hoặc đơn đã được xuất một phần.
- **Cách kiểm tra:** So sánh `WarehouseProductStock.quantity` với lượng đang giữ chỗ → kiểm tra đúng kho, sản phẩm, công ty và lượng đã xuất của đơn.
- **Vị trí code:** [availableForExport()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:245) · [stockOutData()](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:923).

### Đơn mua hoặc đơn bán đã duyệt nhưng Kho không thấy

- **Người dùng thấy:** Đơn hiện “Đã duyệt” ở Mua/Bán nhưng không xuất hiện trong danh sách chờ nhập/xuất kho.
- **Nguyên nhân thường gặp:** Trạng thái chưa thuộc `approved/partial`, đơn đã được xử lý hết, tài khoản thiếu quyền Kho hoặc đơn thuộc công ty khác.
- **Cách kiểm tra:** Xác nhận trạng thái → tính lượng còn phải nhập/xuất sau các phiếu hiện có → gọi endpoint danh sách chờ kho bằng đúng tài khoản và công ty.
- **Vị trí code:** [PurchaseOrderController](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:1) · [SalesOrderController](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:1) · [API đơn chờ Kho](vscode://file/D:/clone/project-base/routes/api.php:377).

### Phiếu kho đã duyệt nhưng tồn hoặc công nợ chưa đổi

- **Người dùng thấy:** Phiếu không còn chờ Kho nhưng số lượng tồn và công nợ vẫn giữ nguyên.
- **Nguyên nhân thường gặp:** Mới hoàn thành bước Kho xác nhận, chưa qua Kế toán duyệt; hoặc giao dịch cơ sở dữ liệu khi duyệt đã bị hoàn tác.
- **Cách kiểm tra:** Phân biệt trạng thái sau Kho xác nhận với Kế toán duyệt → tìm biến động kho (`InventoryMovement`) và công nợ theo phiếu nguồn.
- **Vị trí code:** [Kho xác nhận: approve()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:818) · [Kế toán duyệt: accountantApprove()](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:862) · [ADR-001](vscode://file/D:/clone/project-base/resources/docs/decisions/ADR-001-WAREHOUSE-ACCOUNTING-APPROVAL.md:1).

### Duyệt giao dịch xong nhưng công nợ không giảm

- **Người dùng thấy:** Giao dịch đã được duyệt và số dư có thể đã đổi, nhưng khoản phải thu/phải trả không giảm.
- **Nguyên nhân thường gặp:** Sai loại thu/chi, sai nhóm `THU_KH/CHI_NCC`, thiếu khách hàng/nhà cung cấp hoặc không gắn đúng đơn mua/đơn bán.
- **Cách kiểm tra:** Kiểm tra `type`, nhóm giao dịch, đối tượng và đơn liên kết → đối chiếu công nợ và `AccountLedger` theo cùng `transaction_id`.
- **Vị trí code:** [TransactionService::approve()](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:191) · [TransactionService::syncDebt()](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:850).

### Giá vốn xuất hoặc chuyển kho không giống giá bình quân

- **Người dùng thấy:** Giá vốn trên phiếu/biến động kho khác kết quả bình quân mà người kiểm tra tự tính.
- **Nguyên nhân thường gặp:** Đây có thể là đúng nghiệp vụ: hệ thống dùng giá nhập gần nhất và giá trị nhập gồm VAT, không dùng bình quân gia quyền.
- **Cách kiểm tra:** Tìm lần nhập gần nhất của sản phẩm trước thời điểm xuất/chuyển → đối chiếu `cost_price` và `cost_amount` trên phiếu và biến động kho.
- **Vị trí code:** [ADR-002](vscode://file/D:/clone/project-base/resources/docs/decisions/ADR-002-INVENTORY-COST.md:1) · [InventoryMovementService](vscode://file/D:/clone/project-base/app/Services/InventoryMovementService.php:1).

### Thay tỷ giá nhưng chứng từ cũ không đổi hoặc số liệu mới khác số liệu cũ

- **Người dùng thấy:** Cùng một ngoại tệ nhưng chứng từ tạo ở hai thời điểm có giá trị quy đổi khác nhau.
- **Nguyên nhân thường gặp:** Chứng từ lưu tỷ giá tại thời điểm tạo; thay tỷ giá hiện hành không sửa hồi tố chứng từ cũ. VND luôn có tỷ giá 1.
- **Cách kiểm tra:** So sánh tỷ giá hiện hành trong `CompanyCurrencyRate` với `exchange_rate` và giá trị quy đổi đã lưu trên từng chứng từ.
- **Vị trí code:** [ADR-003](vscode://file/D:/clone/project-base/resources/docs/decisions/ADR-003-CURRENCY-AND-ROLES.md:1) · [CurrencyService](vscode://file/D:/clone/project-base/app/Services/CurrencyService.php:1).

### Bán tại quầy hoặc cửa hàng trực tuyến tạo trùng đơn

- **Người dùng thấy:** Hai đơn có khách hàng, thời điểm, sản phẩm và tổng tiền gần như giống nhau.
- **Nguyên nhân thường gặp:** Người dùng bấm nhiều lần, trình duyệt gửi lại request, đơn nháp được thanh toán lại hoặc toàn bộ thao tác chưa nằm trong cùng transaction.
- **Cách kiểm tra:** Tìm các đơn cùng khách/phiên, thời điểm và tổng tiền → đối chiếu request trên Network → kiểm tra khóa chống gửi lặp và phạm vi transaction.
- **Vị trí code:** [PosController](vscode://file/D:/clone/project-base/app/Http/Controllers/PosController.php:1) · [StorefrontController](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:1).

### Mã giảm giá hợp lệ nhưng không áp dụng hoặc không được hoàn lại

- **Người dùng thấy:** Mã còn hiệu lực nhưng checkout từ chối, hoặc hủy đơn xong vẫn bị tính là đã sử dụng.
- **Nguyên nhân thường gặp:** Không đúng thời gian/kênh/khách được gán, vượt giới hạn sử dụng hoặc nhánh hủy chưa hoàn tác `CouponUsage`.
- **Cách kiểm tra:** Đối chiếu thời gian, kênh bán, khách hàng, giới hạn → tìm `CouponUsage` theo đơn → kiểm tra nhánh hủy của đúng nguồn Bán hàng/POS/Storefront.
- **Vị trí code:** [CouponService](vscode://file/D:/clone/project-base/app/Services/CouponService.php:1).

### Có thông báo nhưng màn hình không tự cập nhật

- **Người dùng thấy:** Thông báo đã xuất hiện hoặc đã có trong cơ sở dữ liệu, nhưng danh sách liên quan chỉ đổi sau khi tải lại trang.
- **Nguyên nhân thường gặp:** Queue/Reverb chưa chạy, kênh riêng bị từ chối, sai kênh công ty hoặc frontend chưa đăng ký listener.
- **Cách kiểm tra:** Xác nhận thông báo đã lưu → kiểm tra queue và Reverb → xác thực quyền vào channel → kiểm tra sự kiện đến trình duyệt rồi mới kiểm tra bước render.
- **Vị trí code:** [routes/channels.php](vscode://file/D:/clone/project-base/routes/channels.php:1) · [companyData.js](vscode://file/D:/clone/project-base/resources/js/realtime/companyData.js:1) · [useRealtimeRefresh.js](vscode://file/D:/clone/project-base/resources/js/composables/useRealtimeRefresh.js:1).

### Dashboard lệch số liệu so với màn hình chi tiết

- **Người dùng thấy:** Thẻ tổng hợp hoặc biểu đồ không bằng tổng các dòng người dùng đang xem.
- **Nguyên nhân thường gặp:** Hai màn hình dùng khác khoảng ngày, múi giờ, công ty, trạng thái hoặc điều kiện truy vấn.
- **Cách kiểm tra:** Cố định cùng `date_from`, `date_to`, công ty và trạng thái → lấy request của Dashboard → chạy lại điều kiện tương ứng trên danh sách chi tiết.
- **Vị trí code:** [DashboardService::getOverview()](vscode://file/D:/clone/project-base/app/Services/DashboardService.php:14) · [DashboardRepository](vscode://file/D:/clone/project-base/app/Repositories/DashboardRepository.php:1).

### Dữ liệu của công ty khác xuất hiện trên màn hình

- **Người dùng thấy:** Người dùng nhìn thấy bản ghi không thuộc công ty đang làm việc.
- **Nguyên nhân thường gặp:** Truy vấn thiếu `company_id`, quan hệ không được giới hạn theo công ty hoặc ID từ request chưa được xác minh quyền sở hữu.
- **Cách kiểm tra:** Lần từ Controller xuống truy vấn → kiểm tra trait/điều kiện công ty trên model và relation → tái hiện bằng hai công ty riêng. Xử lý như lỗi bảo mật.
- **Vị trí code:** [BelongsToCompany](vscode://file/D:/clone/project-base/app/Traits/BelongsToCompany.php:1) · [EnsureCompanyCreated](vscode://file/D:/clone/project-base/app/Http/Middleware/EnsureCompanyCreated.php:1).

### Mở được màn hình nhưng thao tác lại báo 403

- **Người dùng thấy:** Trang hiển thị bình thường nhưng nút lưu, duyệt, hủy hoặc xóa trả về HTTP 403.
- **Nguyên nhân thường gặp:** Route web cho phép mở trang nhưng API yêu cầu quyền mà vai trò hiện tại chưa có; hoặc cache quyền chưa được làm mới.
- **Cách kiểm tra:** Lấy endpoint trả 403 trong Network → đối chiếu middleware `permission:*` → kiểm tra quyền trực tiếp và quyền qua vai trò → làm mới cache rồi thử lại.
- **Vị trí code:** [nhóm API có xác thực/phân quyền](vscode://file/D:/clone/project-base/routes/api.php:44) · [HandleInertiaRequests](vscode://file/D:/clone/project-base/app/Http/Middleware/HandleInertiaRequests.php:1).

### Danh sách có dữ liệu nhưng lọc hoặc chuyển trang lại trống

- **Người dùng thấy:** Danh sách ban đầu có bản ghi; sau khi lọc, tìm kiếm hoặc chuyển trang thì không còn kết quả.
- **Nguyên nhân thường gặp:** Tên tham số giữa giao diện và Controller không khớp, URL còn bộ lọc cũ hoặc `page` vượt quá số trang sau khi lọc.
- **Cách kiểm tra:** Xem query string trong Network → so sánh với `$request` trong `index()` → đặt `page=1` → bỏ lần lượt từng bộ lọc để tìm điều kiện gây trống.
- **Vị trí code:** [WarehouseController](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseController.php:1) · [SalesOrderController](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:1).

### Số dư sai sau khi sửa hoặc duyệt lại giao dịch

- **Người dùng thấy:** Số dư ban đầu đúng nhưng tăng/giảm thêm sau thao tác lặp, hoặc không bằng tổng sổ tài khoản.
- **Nguyên nhân thường gặp:** Bút toán cũ chưa được đảo, yêu cầu duyệt được xử lý hai lần hoặc `current_balance` lệch với ledger.
- **Cách kiểm tra:** Lấy `transaction_id` → đếm bút toán liên quan → cộng ledger theo thời gian → so sánh `current_balance` → kiểm tra duyệt lặp có tạo tác động lần hai không.
- **Vị trí code:** [TransactionService](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:1) · [LedgerService](vscode://file/D:/clone/project-base/app/Services/LedgerService.php:1) · [AccountBalanceService](vscode://file/D:/clone/project-base/app/Services/AccountBalanceService.php:1).

### Khách đặt hàng thành công nhưng không thấy đơn trong tài khoản

- **Người dùng thấy:** Trang thành công trả mã đơn nhưng mục “Đơn hàng của tôi” không có đơn đó.
- **Nguyên nhân thường gặp:** Đơn được tạo khi chưa đăng nhập, thiếu `customer_account_id`, tài khoản không khớp snapshot khách hàng hoặc lịch sử đơn đang lọc sai cửa hàng.
- **Cách kiểm tra:** Tìm đơn theo mã → đối chiếu `company_id`, `customer_id`, `customer_account_id`, email và số điện thoại snapshot → gọi lịch sử đơn bằng đúng phiên và storefront.
- **Vị trí code:** [StorefrontController](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontController.php:1) · [StorefrontAccountController](vscode://file/D:/clone/project-base/app/Http/Controllers/StorefrontAccountController.php:1).

### Thao tác thành công nhưng không có nhật ký hoạt động

- **Người dùng thấy:** Dữ liệu đã thay đổi nhưng trang Nhật ký hoạt động không có bản ghi tương ứng.
- **Nguyên nhân thường gặp:** Endpoint không qua middleware `audit`, nhánh xử lý không gọi service ghi log, transaction bị hoàn tác hoặc thiếu ngữ cảnh người dùng/công ty.
- **Cách kiểm tra:** Xác nhận dữ liệu đã commit → kiểm tra middleware route → tìm lời gọi ghi log → lọc lại theo đúng công ty, người thao tác và thời gian.
- **Vị trí code:** [middleware audit của API](vscode://file/D:/clone/project-base/routes/api.php:44) · [ActivityLogService](vscode://file/D:/clone/project-base/app/Services/ActivityLogService.php:1) · [AuditLogController](vscode://file/D:/clone/project-base/app/Http/Controllers/AuditLogController.php:1).

## Luồng trạng thái và điểm dễ phát sinh lỗi

> Các luồng dưới đây được đối chiếu với trạng thái đang ghi trong Controller/Service. Xem thêm [Luồng nghiệp vụ hiện hành](vscode://file/D:/clone/project-base/resources/docs/BUSINESS_FLOWS.md:1).

- **Đơn bán:** `draft → pending → approved → partial → completed`; có thể sang `cancelled` khi còn `draft/pending`. Điểm kiểm tra: [gửi duyệt](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:729), [duyệt](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:776), [hủy](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:832).
- **Đơn mua:** `pending → approved → partial → completed`; có thể sang `cancelled` khi còn `pending`. Điểm kiểm tra: [duyệt](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:526), [hủy](vscode://file/D:/clone/project-base/app/Http/Controllers/PurchaseOrderController.php:578).
- **Phiếu kho:** bắt đầu `pending`; kho xác nhận rồi kế toán duyệt. Khi kế toán duyệt, đơn liên quan được tính lại thành `approved/partial/completed`. Điểm kiểm tra: [kho duyệt](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:818), [kế toán duyệt](vscode://file/D:/clone/project-base/app/Http/Controllers/WarehouseSlipController.php:862).
- **Giao dịch:** `pending → approved` hoặc `pending → rejected`. Chỉ bước duyệt mới ghi nhận số dư/công nợ liên quan. Điểm kiểm tra: [duyệt giao dịch](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:195), [từ chối giao dịch](vscode://file/D:/clone/project-base/app/Services/TransactionService.php:267).

### Thứ tự debug một chức năng

1. Mở dòng gọi trên **Màn hình/Component** và kiểm tra dữ liệu gửi lên, HTTP method, URL.
2. Mở **Controller/Function**, kiểm tra quyền, validation và trạng thái đầu vào.
3. Mở **Service/model được gọi**, kiểm tra transaction, thay đổi dữ liệu và quan hệ.
4. Đối chiếu trạng thái trước/sau với luồng ở trên và kiểm tra trang bị ảnh hưởng trực tiếp/gián tiếp.
5. Chạy **kiểm thử liên quan**; nếu tài liệu báo chưa phát hiện test thì cần bổ sung test tái hiện lỗi trước khi sửa.
