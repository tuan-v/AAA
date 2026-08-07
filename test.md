# Mục lục — `SalesOrderController.php`

> Mỗi mục: **Chức năng** — việc nó làm | **Ảnh hưởng khi sửa** — sửa hàm này có thể tác động tới đâu.

## Danh sách function

> Cột trái: nhảy vào **file code thật trong VS Code**. Cột phải: nhảy tới **đoạn mô tả** trong tài liệu này.

| Function               | Mở trong VS Code                                                                                  | Xem mô tả                                                   |
| ---------------------- | ------------------------------------------------------------------------------------------------- | ----------------------------------------------------------- |
| `companyId()`          | [dòng 23](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:23)   | [mô tả](#companyid-private-helper)                          |
| `getCompanyCurrency()` | [dòng 33](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:33)   | [mô tả](#getcompanycurrency-private-helper)                 |
| `index()`              | [dòng 40](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:40)   | [mô tả](#indexrequest-request)                              |
| `warehouseIndex()`     | [dòng 149](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:149) | [mô tả](#warehouseindexrequest-request)                     |
| `availableForExport()` | [dòng 245](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:245) | [mô tả](#availableforexportrequest-request)                 |
| `show()`               | [dòng 296](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:296) | [mô tả](#showid)                                            |
| `store()`              | [dòng 363](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:363) | [mô tả](#storerequest-request)                              |
| `update()`             | [dòng 562](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:562) | [mô tả](#updaterequest-request-id)                          |
| `submitForApproval()`  | [dòng 729](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:729) | [mô tả](#submitforapprovalid)                               |
| `approve()`            | [dòng 776](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:776) | [mô tả](#approveid-customerdebtservice-customerdebtservice) |
| `cancel()`             | [dòng 824](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:824) | [mô tả](#cancelrequest-request-id)                          |
| `destroy()`            | [dòng 903](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:903) | [mô tả](#destroyid)                                         |
| `stockOutData()`       | [dòng 923](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:923) | [mô tả](#stockoutdataid)                                    |

---

## `companyId()` (private helper)

📍 [Mở dòng 23 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:23)

- **Chức năng**: Lấy `company_id` của user hiện tại (ưu tiên `user->company_id`, fallback quan hệ `companies()`). Abort 403 nếu không có công ty.
- **Ảnh hưởng khi sửa**: Được gọi bởi `update`, `submitForApproval`, `approve`, `cancel`, `destroy`. Đổi logic lấy company_id ở đây sẽ đổi hành vi phân quyền/scoping dữ liệu ở **tất cả** các hàm đó cùng lúc.

## `getCompanyCurrency()` (private helper)

📍 [Mở dòng 33 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:33)

- **Chức năng**: Lấy đối tượng tiền tệ mặc định của công ty (qua `user->company` hoặc `companies()->first()`).
- **Ảnh hưởng khi sửa**: Dùng trong `index`, `warehouseIndex`, `show`, `stockOutData` để quy đổi giá hiển thị. Sửa sai ở đây → sai tỷ giá quy đổi hiển thị ở **toàn bộ danh sách và chi tiết đơn hàng**.

## `index(Request $request)`

📍 [Mở dòng 40 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:40)

- **Chức năng**: Danh sách đơn bán (trừ POS chưa thanh toán xong), hỗ trợ filter theo `status`, `transaction_eligible`, `search` (mã đơn), `customer_id`; quy đổi giá/VAT/phí ship/giảm giá về tiền tệ công ty; trả JSON phân trang.
- **Ảnh hưởng khi sửa**:
    - Đổi điều kiện `whereIn('status', ...)` → ảnh hưởng trang danh sách đơn bán FE hiển thị thiếu/thừa đơn.
    - Đổi công thức `displayPrice`/`vatAmount`/`total` → sai số liệu hiển thị (không ảnh hưởng dữ liệu gốc trong DB, chỉ là dữ liệu trả về).
    - Logic lọc POS (`sales_channel`) trùng với `warehouseIndex` — sửa một chỗ nên rà cả hai để tránh lệch hành vi.

## `warehouseIndex(Request $request)`

📍 [Mở dòng 149 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:149)

- **Chức năng**: Danh sách đơn bán phục vụ màn hình kho (chỉ đơn `approved/partial/completed`, loại trừ POS), tính thêm `warehouse_status` (đã xuất đủ/một phần/chưa xuất/đang hoàn hàng) dựa trên các phiếu xuất kho đã duyệt.
- **Ảnh hưởng khi sửa**:
    - Đổi logic gom `approvedExported` → sai trạng thái `warehouse_status`, ảnh hưởng trực tiếp màn hình xuất kho (nhân viên kho quyết định tạo phiếu xuất dựa vào status này).
    - Trùng lặp code quy đổi tiền tệ với `index()` và `stockOutData()` — nên cân nhắc tách hàm chung nếu sửa nhiều lần.

## `availableForExport(Request $request)`

📍 [Mở dòng 245 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:245)

- **Chức năng**: Với 1 đơn bán, trả về danh sách kho + tồn kho khả dụng của từng sản phẩm trong đơn (tồn kho trừ đi số đã "giữ chỗ" bởi các phiếu xuất `pending`).
- **Ảnh hưởng khi sửa**:
    - Đây là nguồn dữ liệu để FE chọn kho khi tạo phiếu xuất — sai công thức `available_quantity` (stock - reserved) có thể dẫn tới **xuất kho âm/vượt tồn**.
    - Không cập nhật DB, chỉ đọc — an toàn tương đối, nhưng sai số liệu ảnh hưởng nghiệp vụ thực tế.

## `show($id)`

📍 [Mở dòng 296 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:296)

- **Chức năng**: Chi tiết 1 đơn bán — tính `exported_quantity` từng dòng (đặc biệt xử lý riêng cho đơn POS), tính `amount`/`vat_amount`/`total_amount` từng dòng và tổng đơn, kèm quan hệ customer/currency/warehouseSlips/creator/approver/địa chỉ/coupon.
- **Ảnh hưởng khi sửa**:
    - Là API chính cho trang chi tiết đơn — sửa công thức tính tiền ảnh hưởng **toàn bộ hiển thị chi tiết**, không ảnh hưởng dữ liệu lưu trong DB.
    - Nhánh xử lý riêng `sales_channel === 'pos'` cho `exported_quantity` — sửa nhầm có thể làm sai số liệu tồn/đã xuất của đơn thường lẫn đơn POS.

## `store(Request $request)`

📍 [Mở dòng 363 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:363)

- **Chức năng**: Tạo đơn bán mới — validate input, kiểm tra quyền tạo từ lịch sử (`don_ban.tao_tu_lich_su`), validate tồn kho (`OrderQuantityValidationService`), tính tỷ giá (`CompanyCurrencyService`), tạo `SalesOrder` + `SalesOrderItem`, áp dụng coupon (`CouponService`), gửi thông báo cho accountant.
- **Ảnh hưởng khi sửa**:
    - Nằm trong transaction DB (`DB::beginTransaction`) — sửa sai có thể để lại dữ liệu nửa vời nếu quên rollback đúng chỗ.
    - Đổi rule validate → ảnh hưởng trực tiếp form tạo đơn ở FE (lỗi 422 mới xuất hiện hoặc mất kiểm tra).
    - Đổi công thức `companyUnitPrice`/`companyAmount`/`total` → sai dữ liệu **lưu vào DB** (khác với `index`/`show` chỉ sai hiển thị).
    - Gọi `CouponService::resolve` + `applyToOrder` — sửa logic ở đây cần đối chiếu với `update()` (đang gọi tương tự) và `cancel()`/`destroy()` (gọi `reverseForOrder`) để tránh lệch trạng thái coupon.

## `update(Request $request, $id)`

📍 [Mở dòng 562 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:562)

- **Chức năng**: Sửa đơn bán (chỉ khi status `draft`/`pending`) — validate, tính lại tỷ giá, xóa hết `SalesOrderItem` cũ rồi tạo lại, reverse + resolve + apply lại coupon, gửi thông báo.
- **Ảnh hưởng khi sửa**:
    - Chiến lược "xóa hết items rồi tạo lại" → nếu `SalesOrderItem` có quan hệ khác tham chiếu theo `id` (vd log, phiếu xuất kho tạm), sửa/xóa nhầm có thể phá vỡ liên kết đó.
    - Cùng rủi ro về công thức tính tiền như `store()` (ảnh hưởng dữ liệu DB thật).
    - Có `coupon_code` nhưng thiếu field `order_date`/nhiều field khác so với `store()` — nếu đồng bộ 2 hàm cần rà kỹ danh sách field khác biệt để tránh lệch hành vi giữa tạo và sửa.

## `submitForApproval($id)`

📍 [Mở dòng 729 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:729)

- **Chức năng**: Chuyển đơn từ `draft` → `pending`, ghi `submitted_at`, log hoạt động, gửi thông báo cho người có quyền `don_ban.duyet`.
- **Ảnh hưởng khi sửa**: Đơn giản, ít rủi ro — nhưng đổi điều kiện trạng thái đầu vào (`draft`) sẽ ảnh hưởng luồng duyệt đơn (vd cho phép gửi duyệt cả đơn đã `pending` sẽ gây trùng thông báo).

## `approve($id, CustomerDebtService $customerDebtService)`

📍 [Mở dòng 776 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:776)

- **Chức năng**: Duyệt đơn (`pending` → `approved`), redeem coupon (trừ kênh `storefront`), log hoạt động, thông báo cho người tạo đơn.
- **Ảnh hưởng khi sửa**:
    - Tham số `CustomerDebtService $customerDebtService` được inject nhưng **không dùng trong thân hàm** — nếu định bổ sung logic công nợ khi duyệt đơn thì đây là chỗ cần thêm; nếu không dùng nữa nên dọn dẹp tham số thừa.
    - Đổi điều kiện redeem coupon theo `sales_channel` → ảnh hưởng số liệu coupon đã dùng, cần đối chiếu với `cancel()` (gọi `reverseForOrder`) để coupon không bị lệch khi đơn được duyệt rồi hủy.

## `cancel(Request $request, $id)`

📍 [Mở dòng 824 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:824)

- **Chức năng**: Hủy đơn (`draft`/`pending`, chưa có phiếu xuất kho) — yêu cầu lý do, cập nhật status, reverse coupon, log, thông báo người tạo và (nếu là storefront) thông báo khách hàng.
- **Ảnh hưởng khi sửa**:
    - Điều kiện chặn hủy khi đã có `warehouse_slips_count > 0` — nới lỏng điều kiện này có rủi ro hủy đơn đã phát sinh xuất kho, gây lệch tồn kho.
    - Đổi logic `reverseForOrder` cần đối chiếu `approve()` (redeem) và `store/update` (resolve/apply) để coupon không bị cộng/trừ sai vòng đời.

## `destroy($id)`

📍 [Mở dòng 903 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:903)

- **Chức năng**: Xóa hẳn đơn (chỉ khi `draft`/`pending` và chưa có phiếu xuất kho) — xóa items rồi xóa order trong transaction.
- **Ảnh hưởng khi sửa**:
    - Không thấy `reverseForOrder` coupon được gọi ở đây (khác với `cancel()`) — nếu đơn đã áp coupon rồi bị xóa, coupon có thể không được hoàn lại; đây là điểm cần lưu ý nếu sửa logic coupon.
    - Là xóa cứng dữ liệu (không phải soft-cancel) — sửa điều kiện cho phép xóa cần rất cẩn trọng vì mất dữ liệu vĩnh viễn.

## `stockOutData($id)`

📍 [Mở dòng 923 trong VS Code](vscode://file/D:/clone/project-base/app/Http/Controllers/SalesOrderController.php:923)

- **Chức năng**: Dữ liệu phục vụ màn hình xuất kho cho 1 đơn — tính `exported_quantity` (tính cả phiếu `pending` lẫn `approved`, khác với `show()` chỉ tính `approved`), quy đổi tiền tệ, tính `can_export`/`export_block_reason` dựa trên `return_status` và `status`.
- **Ảnh hưởng khi sửa**:
    - Lưu ý: công thức tính `exported_quantity` ở đây **khác** với `show()` (gồm cả `pending`) — nếu đồng bộ logic giữa 2 hàm này cần chủ đích, không phải lỗi copy-paste.
    - Đổi điều kiện `can_export` → ảnh hưởng trực tiếp nút "Tạo phiếu xuất" trên FE có hiện/ẩn đúng hay không.

---

## Ghi chú chung (điểm trùng lặp cần lưu ý khi refactor)

- Công thức quy đổi tiền tệ (`unit_price * exchange_rate / companyCurrency->exchange_rate`) lặp lại ở `index`, `warehouseIndex`, `stockOutData` — nên tách thành 1 service/trait dùng chung nếu sửa nhiều lần để tránh sửa thiếu chỗ.
- Vòng đời coupon (`resolve` → `applyToOrder` → `redeemForOrder` → `reverseForOrder`) trải trên 4 hàm (`store`, `update`, `approve`, `cancel`) — sửa logic coupon ở bất kỳ hàm nào nên kiểm tra chéo cả 4.
- Logic loại trừ đơn POS (`sales_channel === 'pos'`) xuất hiện riêng lẻ ở `index`, `warehouseIndex`, `show` — không dùng chung 1 scope/query builder.
