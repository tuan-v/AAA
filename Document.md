# ĐẶC TẢ YÊU CẦU NGHIỆP VỤ

## Phần mềm quản trị doanh nghiệp nội bộ

| Thuộc tính | Nội dung |
|---|---|
| Nguồn yêu cầu | `Yêu cầu và nghiệp vụ phần mềm quản trị cho doanh nghiệp.xlsx` |
| Phạm vi | Quản lý, Mua hàng, Kho, Kế toán, Bán hàng |
| Đối tượng sử dụng | Chủ doanh nghiệp, quản lý, nhân sự mua hàng, kho, bán hàng và kế toán |
| Mục tiêu | Liên thông đơn hàng, hàng hóa, tồn kho, công nợ và dòng tiền trong một hệ thống |

> Tài liệu này là đặc tả nghiệp vụ được chuẩn hóa từ workbook yêu cầu gốc. Các quy tắc đã được chốt trong quá trình triển khai được ghi rõ là “Quy tắc bắt buộc”. Các nội dung chưa có quyết định cuối cùng được ghi là “Cần xác nhận”, không được tự suy diễn khi phát triển.

---

## 1. Mục tiêu và phạm vi hệ thống

Hệ thống gồm năm phân hệ liên thông:

| Phân hệ | Phạm vi chính |
|---|---|
| Quản lý | Nhân sự, tài khoản đăng nhập, vai trò, quyền và nhật ký hoạt động |
| Mua hàng | Nhà cung cấp, sản phẩm, danh mục, đơn vị tính và đơn mua hàng |
| Kho | Kho, tồn kho, phiếu nhập/xuất, chuyển kho và lịch sử biến động tồn |
| Kế toán | Tiền tệ, tỷ giá, tài khoản/quỹ, giao dịch tiền và công nợ hai chiều |
| Bán hàng | Khách hàng, đơn bán hàng và tổng quan bán hàng |

Luồng liên phân hệ tổng quát:

```text
Đơn mua được duyệt ──> Phiếu nhập được duyệt ──> Tăng tồn + tăng công nợ NCC
                                                   │
                                                   └──> Sổ biến động tồn

Đơn bán được duyệt ──> Phiếu xuất được duyệt ──> Giảm tồn + tăng công nợ KH
                                                   │
                                                   └──> Sổ biến động tồn

Thanh toán NCC ──> Giảm số dư tài khoản/quỹ + giảm công nợ NCC + ghi sổ tài khoản
Thu tiền KH    ──> Tăng số dư tài khoản/quỹ + giảm công nợ KH  + ghi sổ tài khoản
```

### 1.1 Ngoài phạm vi hiện tại

Các chức năng sau chưa thuộc phạm vi bắt buộc của giai đoạn hiện tại, chỉ triển khai sau khi có đặc tả bổ sung:

- Hóa đơn điện tử và tích hợp cơ quan thuế.
- Kế toán kép, hệ thống tài khoản kế toán và báo cáo tài chính chuẩn mực.
- Sản xuất, định mức nguyên vật liệu và kế hoạch sản xuất.
- Tiền lương, chấm công và bảo hiểm.
- Chứng từ trả hàng, đảo chứng từ và điều chỉnh sau duyệt.
- Quy trình duyệt nhiều cấp.

---

## 2. Thuật ngữ và nguyên tắc chung

### 2.1 Thuật ngữ

| Thuật ngữ | Ý nghĩa |
|---|---|
| PO | Đơn mua hàng gửi nhà cung cấp |
| SO | Đơn bán hàng cho khách hàng |
| Phiếu nhập | Chứng từ ghi nhận hàng thực tế đi vào kho |
| Phiếu xuất | Chứng từ ghi nhận hàng thực tế đi ra khỏi kho |
| Công nợ phải trả | Số tiền doanh nghiệp còn phải trả nhà cung cấp |
| Công nợ phải thu | Số tiền khách hàng còn phải trả doanh nghiệp |
| Tiền cơ sở | Đơn vị tiền dùng để tổng hợp giá trị trong một công ty |
| Tỷ giá chứng từ | Tỷ giá được chụp tại ngày chứng từ và không thay đổi về sau |
| Giá vốn bình quân | Giá trị tồn hiện tại chia cho số lượng tồn hiện tại tại từng kho |

### 2.2 Quy tắc bắt buộc

1. Mọi dữ liệu nghiệp vụ phải thuộc đúng công ty hiện tại; người dùng không được xem hoặc thao tác dữ liệu của công ty khác.
2. Tài khoản bị khóa không được đăng nhập hoặc tiếp tục sử dụng hệ thống.
3. Mọi hành động nghiệp vụ phải được kiểm tra quyền ở backend, không chỉ ẩn nút trên giao diện.
4. Chứng từ ở trạng thái chờ xử lý có thể sửa; chứng từ đã duyệt không được sửa hoặc xóa trực tiếp.
5. Duyệt PO/SO **không phát sinh công nợ**; chỉ đưa đơn sang phân hệ kho để thực hiện.
6. Công nợ chỉ phát sinh khi phiếu nhập/xuất được duyệt và chỉ tính theo số lượng thực tế trên phiếu.
7. Phiếu chờ duyệt không được làm thay đổi tồn kho, công nợ hoặc giá vốn.
8. Giao dịch tiền chờ duyệt không được làm thay đổi số dư, sổ tài khoản hoặc công nợ.
9. Mọi cập nhật liên quan đồng thời đến tồn kho, công nợ hoặc số dư phải nằm trong một database transaction; lỗi ở một bước phải rollback toàn bộ.
10. Tất cả mã nghiệp vụ phải duy nhất trong phạm vi công ty và sinh an toàn khi có nhiều yêu cầu đồng thời.
11. Danh mục đã được sử dụng không được xóa; chỉ được khóa/mở khóa theo quyền.
12. Mọi số tiền quy đổi phải lưu cả giá trị nguyên tệ, tỷ giá chứng từ và giá trị theo tiền cơ sở.

---

## 3. Vai trò và phân quyền

### 3.1 Nhóm vai trò nghiệp vụ

| Vai trò tham chiếu | Phạm vi mặc định |
|---|---|
| Chủ doanh nghiệp/Quản trị | Toàn bộ dữ liệu và cấu hình của công ty |
| Quản lý nhân sự | Nhân sự, vai trò, quyền và audit log theo quyền được cấp |
| Quản lý mua hàng | NCC, danh mục mua, sản phẩm và PO |
| Quản lý kho | Kho, tồn, phiếu nhập/xuất và chuyển kho |
| Quản lý bán hàng | Khách hàng và SO |
| Kế toán | Tiền tệ, ngân hàng, tài khoản/quỹ, giao dịch và công nợ |

Vai trò trên là cấu hình khởi tạo, không phải danh sách cố định. Hệ thống phải cho phép tạo vai trò mới và gán quyền linh hoạt.

### 3.2 Nhóm hành động quyền

Mỗi tài nguyên cần tách quyền tối thiểu theo hành động:

- Xem danh sách.
- Xem chi tiết.
- Thêm.
- Sửa.
- Xóa khi chưa sử dụng.
- Khóa/mở khóa.
- Duyệt.
- Từ chối hoặc hủy.
- Xuất dữ liệu nếu có.

### 3.3 Nhật ký hoạt động

Audit log tối thiểu phải lưu:

- Công ty, người dùng và thời điểm.
- Tài nguyên, mã/ID bản ghi và hành động.
- Dữ liệu trước/sau đối với thay đổi quan trọng.
- Địa chỉ IP, user agent và đường dẫn truy cập nếu có.
- Kết quả thành công/thất bại và thông tin lỗi phù hợp.

Không lưu mật khẩu, token, cookie hoặc dữ liệu bí mật vào audit log.

---

## 4. Phân hệ Quản lý

### 4.1 Quản lý nhân sự

Thông tin tối thiểu:

- Họ tên.
- Tên đăng nhập.
- Email/SĐT nếu sử dụng.
- Mật khẩu được băm an toàn.
- Phòng ban, chức vụ.
- Vai trò/quyền.
- Trạng thái hoạt động hoặc bị khóa.

Chức năng:

- Danh sách, tìm kiếm và lọc trạng thái.
- Thêm, xem chi tiết và cập nhật.
- Khóa/mở khóa đăng nhập.
- Gán hoặc thay đổi vai trò trong giới hạn phân cấp quyền.

### 4.2 Phân quyền

- Xem danh sách quyền theo nhóm nghiệp vụ.
- Xem quyền hiện có của từng vai trò.
- Thêm/cập nhật vai trò.
- Gán quyền cho vai trò và gán vai trò cho nhân sự.
- Người dùng không được tạo hoặc cấp vai trò cao hơn cấp quản trị của chính mình.
- Vai trò hệ thống không được xóa tùy tiện nếu đang được sử dụng.

### 4.3 Lưu vết hoạt động

- Lọc theo nhân sự, phân hệ, hành động, tài nguyên và khoảng thời gian.
- Xem chi tiết thay đổi trước/sau.
- Có thể truy ngược các thao tác liên quan đến cùng một chứng từ.

---

## 5. Danh mục dùng chung

### 5.1 Danh mục sản phẩm

- Hỗ trợ cấu trúc đa cấp và quan hệ cha–con.
- Không cho phép chọn chính danh mục làm cha hoặc tạo vòng lặp cây.
- Danh mục đã gắn sản phẩm không được xóa; được phép khóa/mở.

### 5.2 Đơn vị tính

Thông tin:

- Tên đơn vị.
- Trạng thái.
- Có cho phép số lượng thập phân hay không.

Quy tắc:

- Nếu không cho phép nhập lẻ, số lượng trên PO, SO và phiếu kho phải là số nguyên.
- Đơn vị đã sử dụng không được xóa.

### 5.3 Sản phẩm

Thông tin tối thiểu:

- Tên, SKU/mã sản phẩm.
- Danh mục, đơn vị tính.
- Loại sản phẩm: hàng hóa, vật tư hoặc dịch vụ.
- Mô tả, hình ảnh nếu có.
- Trạng thái.
- Giá mua gần nhất, giá bán mặc định chỉ mang tính tham chiếu.

Quy tắc:

- SKU duy nhất trong công ty.
- Sản phẩm đã có trong đơn hàng, phiếu kho hoặc tồn kho không được xóa.
- Sản phẩm bị khóa không được thêm vào chứng từ mới.
- Dịch vụ không làm thay đổi tồn kho; cần đặc tả hóa đơn riêng trước khi cho phép phát sinh công nợ dịch vụ.

---

## 6. Phân hệ Mua hàng

### 6.1 Nhà cung cấp

Thông tin:

- Mã NCC sinh tự động, tên.
- SĐT, email.
- Tiền tệ giao dịch mặc định.
- Địa chỉ chi tiết.
- Công nợ đầu kỳ và tạm ứng đầu kỳ nếu có.
- Ghi chú, trạng thái.

Chức năng:

- Danh sách, tìm kiếm, lọc, chi tiết.
- Thêm, sửa và khóa/mở.
- Tạo PO từ NCC.
- Trang chi tiết hiển thị đơn mua gần đây và lịch sử công nợ.

### 6.2 Đơn mua hàng

Thông tin đầu đơn:

- Mã PO tự động.
- NCC, tiền tệ và tỷ giá chứng từ.
- Ngày tạo, ngày dự kiến nhận.
- Ghi chú, người tạo, người duyệt và thời điểm duyệt.

Thông tin dòng hàng:

- Sản phẩm, số lượng, đơn vị.
- Đơn giá nguyên tệ.
- VAT (%).
- Thành tiền trước VAT, VAT và tổng tiền.
- Đơn giá quy đổi theo tiền cơ sở.

Trạng thái chuẩn:

| Trạng thái | Ý nghĩa | Cho phép |
|---|---|---|
| `pending` | Chờ xử lý | Sửa, xóa hoặc hủy theo quyền |
| `approved` | Đã duyệt, chưa nhập | Tạo phiếu nhập |
| `partial` | Đã nhập một phần | Tạo phiếu nhập phần còn lại |
| `completed` | Đã nhập đủ | Chỉ xem |
| `cancelled` | Đã hủy trước thực hiện | Chỉ xem |

Quy tắc:

- Chỉ PO `pending` được sửa.
- PO phải có ít nhất một dòng hàng và số lượng lớn hơn 0.
- Duyệt PO không tăng tồn và không tăng công nợ.
- Không được tạo tổng số lượng phiếu nhập chờ duyệt + đã duyệt vượt số lượng PO.
- Giá trị dashboard phải phân biệt nguyên tệ và tiền cơ sở.

### 6.3 Tổng quan mua hàng

- Tổng số PO, NCC và sản phẩm.
- Tổng giá trị PO theo tiền cơ sở.
- PO gần đây và trạng thái nhập.
- Top NCC theo giá trị thực nhập hoặc giá trị PO; giao diện phải ghi rõ đang dùng tiêu chí nào.
- Thao tác nhanh theo quyền.

---

## 7. Phân hệ Bán hàng

### 7.1 Khách hàng

Thông tin:

- Mã KH tự động, tên.
- Email, SĐT.
- Tiền tệ giao dịch mặc định.
- Công nợ và tiền nhận trước đầu kỳ nếu có.
- Tỉnh/thành, xã/phường và địa chỉ chi tiết.
- Trạng thái.

Chức năng:

- Danh sách, lọc, thêm, sửa và khóa/mở.
- Xem chi tiết thông tin, công nợ, đơn hàng và lịch sử công nợ.
- Tạo SO trực tiếp từ danh sách hoặc trang chi tiết khách hàng.

### 7.2 Đơn bán hàng

Thông tin đầu đơn:

- Mã SO tự động.
- Khách hàng, tiền tệ và tỷ giá chứng từ.
- Địa chỉ giao hàng.
- Ngày dự kiến giao, ghi chú.
- Người tạo, người duyệt và thời điểm duyệt.

Thông tin dòng hàng:

- Sản phẩm, số lượng, đơn vị.
- Đơn giá nguyên tệ, VAT (%).
- Thành tiền trước VAT, VAT và tổng tiền.
- Đơn giá và thành tiền quy đổi theo tiền cơ sở.

Trạng thái và quy tắc tương tự PO, thay “nhập” bằng “xuất”. Ngoài ra:

- Khi lập SO phải hiển thị tồn khả dụng theo kho hoặc tổng kho.
- Kiểm tra tồn lúc lập đơn chỉ mang tính cảnh báo/giữ chỗ theo chính sách; kiểm tra bắt buộc lần cuối khi duyệt phiếu xuất.
- Không cho phép tồn kho âm.
- Duyệt SO không giảm tồn và không tăng công nợ.
- Công nợ chỉ tăng theo hàng thực xuất khi phiếu xuất được duyệt.
- Một SO được phép xuất nhiều lần cho tới khi đủ.

### 7.3 Tổng quan bán hàng

- Tổng số SO, khách hàng và sản phẩm.
- Tổng giá trị SO theo tiền cơ sở.
- SO gần đây và trạng thái giao hàng.
- Top khách hàng theo giá trị thực xuất hoặc giá trị SO; phải hiển thị rõ tiêu chí.
- Thao tác nhanh theo quyền.

---

## 8. Phân hệ Kho

### 8.1 Quản lý kho

Thông tin:

- Mã kho, tên kho.
- Tỉnh/thành, xã/phường, địa chỉ chi tiết.
- Trạng thái.
- Tổng số lượng và tổng giá trị tồn.

Quy tắc:

- Kho đã phát sinh tồn hoặc chứng từ không được xóa.
- Kho bị khóa không được dùng cho chứng từ mới.
- Có thể cập nhật thông tin mô tả của kho đã sử dụng nhưng không được làm mất lịch sử.

### 8.2 Danh sách đơn chờ xử lý kho

- Hiển thị PO/SO ở trạng thái `approved` hoặc `partial`.
- Hiển thị số lượng đặt, đã thực hiện, đang nằm trên phiếu chờ duyệt và còn có thể tạo phiếu.
- PO tạo phiếu nhập; SO tạo phiếu xuất.

### 8.3 Phiếu nhập/xuất

Thông tin:

- Mã phiếu, loại nhập/xuất.
- PO/SO nguồn.
- Kho thực hiện.
- Sản phẩm và số lượng.
- Ghi chú.
- Người tạo/duyệt, thời gian tạo/duyệt.
- Trạng thái `pending`, `approved` hoặc `rejected`.

Luồng duyệt phiếu nhập:

1. Khóa bản ghi phiếu và tồn kho liên quan.
2. Kiểm tra phiếu vẫn ở trạng thái `pending`.
3. Kiểm tra số lượng không vượt phần còn lại của PO.
4. Tăng số lượng tồn và tăng giá trị tồn theo đơn giá quy đổi đã chụp trên dòng PO/phiếu.
5. Ghi sổ biến động tồn `import`.
6. Tăng công nợ NCC theo giá trị thực nhập gồm VAT của dòng thực nhập.
7. Đánh dấu phiếu `approved` và cập nhật PO thành `partial` hoặc `completed`.
8. Nếu bất kỳ bước nào lỗi, rollback toàn bộ.

Luồng duyệt phiếu xuất:

1. Khóa bản ghi phiếu và tồn kho liên quan.
2. Kiểm tra phiếu vẫn ở trạng thái `pending`.
3. Kiểm tra tồn kho đủ và số lượng không vượt phần còn lại của SO.
4. Tính giá vốn bình quân tại kho ngay trước khi xuất.
5. Giảm số lượng và giá trị tồn theo giá vốn bình quân.
6. Ghi sổ biến động tồn `export`.
7. Tăng công nợ KH theo giá bán và VAT của lượng thực xuất.
8. Đánh dấu phiếu `approved` và cập nhật SO thành `partial` hoặc `completed`.
9. Nếu bất kỳ bước nào lỗi, rollback toàn bộ.

### 8.4 Chuyển kho nội bộ

- Phiếu gồm kho nguồn, kho đích, danh sách sản phẩm, số lượng, ghi chú, người tạo/duyệt.
- Kho nguồn và kho đích phải khác nhau và cùng công ty.
- Chỉ phiếu `pending` được duyệt hoặc hủy.
- Khi duyệt, khóa tồn ở cả hai kho và kiểm tra kho nguồn đủ hàng.
- Số lượng và giá trị giảm tại kho nguồn phải đúng bằng số lượng và giá trị tăng tại kho đích.
- Giá trị chuyển sử dụng giá vốn bình quân tại kho nguồn tại thời điểm duyệt.
- Ghi hai dòng sổ: `transfer_out` tại kho nguồn và `transfer_in` tại kho đích.
- Chuyển kho không phát sinh công nợ.

### 8.5 Sổ biến động tồn

Mỗi bút toán kho phải lưu:

- Công ty, kho, sản phẩm và loại biến động.
- Số lượng, đơn giá vốn và tổng giá trị biến động.
- Số lượng/giá trị trước và sau biến động.
- Chứng từ tham chiếu và người thực hiện.
- Thời điểm ghi nhận.

Sổ biến động là nguồn truy vết; bảng tồn hiện tại là số tổng hợp. Không cho sửa/xóa trực tiếp bút toán đã ghi.

### 8.6 Tổng quan kho

- Nhập–xuất–tồn theo tháng/năm.
- Danh sách kho và tổng giá trị từng kho.
- Lịch sử nhập/xuất/chuyển gần nhất.
- Bộ lọc theo kho, sản phẩm, loại biến động và thời gian.

---

## 9. Phân hệ Kế toán

### 9.1 Tiền tệ và tỷ giá

Thông tin tiền tệ:

- Tên, mã ISO hoặc mã nội bộ, ký hiệu.
- Trạng thái.
- Có thuộc danh sách tiền tệ công ty sử dụng hay không.

Quy tắc tỷ giá:

1. Mỗi công ty có đúng một tiền cơ sở; tỷ giá của tiền cơ sở luôn bằng `1`.
2. Tỷ giá ngoại tệ được lưu riêng theo công ty và ngày hiệu lực.
3. Tỷ giá áp dụng là bản ghi gần nhất có ngày hiệu lực không lớn hơn ngày chứng từ.
4. Khi tạo/cập nhật chứng từ chờ xử lý, hệ thống chụp tỷ giá vào chứng từ.
5. Cập nhật tỷ giá mới không được làm thay đổi PO, SO hoặc giao dịch đã lưu trước đó.
6. Tỷ giá phải lớn hơn 0.
7. Giá trị tiền cơ sở = giá trị nguyên tệ × tỷ giá chứng từ.
8. Giá trị tiền được làm tròn 2 chữ số ở cấp tổng; tỷ giá lưu tối thiểu 8 chữ số thập phân để hạn chế sai số trung gian.

### 9.2 Ngân hàng

- Danh mục ngân hàng được sử dụng.
- Thông tin tối thiểu: mã, tên, trạng thái.
- Ngân hàng đã gắn tài khoản không được xóa; chỉ được khóa.

### 9.3 Tài khoản và quỹ

Thông tin:

- Mã, tên.
- Loại: ngân hàng hoặc tiền mặt.
- Tiền tệ.
- Số dư đầu kỳ và số dư hiện tại.
- Với tài khoản ngân hàng: ngân hàng và số tài khoản.
- Trạng thái.

Quy tắc:

- Số dư hiện tại phải đối chiếu được từ số dư đầu kỳ và sổ tài khoản.
- Tài khoản bị khóa không được dùng cho giao dịch mới.
- Không cho phép chi vượt số dư trừ khi công ty bật chính sách thấu chi; mặc định không cho phép.

### 9.4 Loại giao dịch

- Phân loại giao dịch để báo cáo.
- Xác định chiều tiền vào/ra/chuyển nội bộ.
- Chỉ xóa khi chưa được sử dụng; nếu đã dùng thì khóa.

### 9.5 Giao dịch tiền

Loại chính:

- Thu tiền.
- Chi tiền.
- Chuyển tiền nội bộ giữa hai tài khoản/quỹ.

Thông tin:

- Mã giao dịch, ngày giao dịch.
- Loại giao dịch và phương thức thanh toán.
- Số tiền nguyên tệ, tiền tệ, tỷ giá và số tiền cơ sở.
- Tài khoản nguồn/đích.
- Khách hàng hoặc NCC nếu có.
- SO/PO liên quan nếu có.
- Nội dung, người tạo, người duyệt hoặc từ chối.

Trạng thái:

| Trạng thái | Tác động số liệu |
|---|---|
| `pending` | Không tác động |
| `approved` | Cập nhật số dư, sổ tài khoản và công nợ |
| `rejected` | Không tác động; lưu lý do từ chối |

Luồng duyệt:

1. Khóa giao dịch và các tài khoản liên quan.
2. Kiểm tra trạng thái, quan hệ công ty, tiền tệ và số tiền.
3. Kiểm tra tài khoản nguồn đủ số dư.
4. Cập nhật số dư nguồn/đích.
5. Ghi sổ tài khoản với số dư trước/sau.
6. Nếu là thu/chi công nợ, ghi bút toán giảm công nợ tương ứng.
7. Không cho thanh toán vượt số còn phải thu/trả; phần vượt chỉ được xử lý thành tiền nhận trước/tạm ứng khi nghiệp vụ đó được chọn rõ.
8. Đánh dấu `approved`; lỗi ở bất kỳ bước nào phải rollback toàn bộ.

### 9.6 Công nợ khách hàng

Công thức tổng quát:

```text
Công nợ phải thu hiện tại
= Công nợ đầu kỳ
+ Công nợ từ lượng hàng thực xuất đã duyệt
- Tiền khách hàng đã thanh toán
- Khoản hoàn/điều chỉnh giảm hợp lệ
```

Trang danh sách/chi tiết phải có:

- Tổng công nợ và công nợ hiện tại.
- Tổng đã thanh toán, còn phải thu và tiền nhận trước nếu có.
- Danh sách SO với giá trị đã thực hiện, đã thanh toán và còn lại.
- Lịch sử công nợ gồm tiền tệ, nguyên tệ, tiền cơ sở, SO và phiếu xuất liên quan.
- Bộ lọc tiền tệ và thời gian; số tổng hợp toàn công ty phải quy về tiền cơ sở.

### 9.7 Công nợ nhà cung cấp

Công thức tổng quát:

```text
Công nợ phải trả hiện tại
= Công nợ đầu kỳ
+ Công nợ từ lượng hàng thực nhập đã duyệt
- Tiền đã thanh toán NCC
- Khoản hoàn/điều chỉnh giảm hợp lệ
```

Yêu cầu hiển thị tương tự công nợ khách hàng, thay SO/phiếu xuất bằng PO/phiếu nhập và tách riêng tạm ứng NCC.

### 9.8 Tổng quan kế toán

- Tổng tiền các tài khoản theo tiền cơ sở.
- Tổng công nợ phải thu và phải trả.
- Biểu đồ biến động công nợ đầu vào/đầu ra.
- Biểu đồ dòng tiền vào/ra và số dư.
- Mười giao dịch gần nhất.
- Mọi chỉ số tổng hợp đa tiền tệ phải dùng tỷ giá chứng từ hoặc quy tắc báo cáo được ghi rõ, không cộng trực tiếp các nguyên tệ khác nhau.

---

## 10. Quy tắc trạng thái và bất biến dữ liệu

### 10.1 Bất biến tồn kho

- `quantity >= 0`.
- `stock_value >= 0`.
- Khi `quantity = 0`, `stock_value` phải về 0 trong sai số làm tròn cho phép.
- Tổng giá trị trước và sau chuyển kho phải bằng nhau trong sai số tối đa 0,01 đơn vị tiền cơ sở.
- Mỗi lần thay đổi tồn phải có một bút toán sổ tương ứng.

### 10.2 Bất biến công nợ

- Một phiếu kho chỉ được phát sinh công nợ một lần.
- Một giao dịch được duyệt chỉ được ghi giảm công nợ một lần.
- Tổng công nợ phải đối chiếu được từ công nợ đầu kỳ và lịch sử bút toán.
- Duyệt lại do double-click hoặc request lặp không được tạo bút toán trùng.

### 10.3 Bất biến tiền

- Một giao dịch chỉ được ghi sổ một lần.
- Chuyển khoản nội bộ không làm thay đổi tổng tài sản quy đổi nếu không xét phí/chênh lệch tỷ giá.
- Số dư tài khoản phải khớp số dư đầu kỳ cộng tổng bút toán sổ.

---

## 11. Tìm kiếm, lọc và báo cáo

Mọi trang danh sách lớn phải hỗ trợ:

- Phân trang phía server và giới hạn kích thước trang.
- Tìm kiếm theo mã/tên phù hợp.
- Lọc trạng thái và khoảng ngày.
- Lọc theo đối tượng nghiệp vụ chính.
- Sắp xếp có kiểm soát, không cho truyền trực tiếp tên cột tùy ý vào SQL.
- Xuất dữ liệu nếu người dùng có quyền.

Các báo cáo phải ghi rõ:

- Kỳ báo cáo và múi giờ.
- Đơn vị tiền hiển thị.
- Dữ liệu theo ngày chứng từ hay ngày duyệt.
- Tiêu chí tính top/tổng.

---

## 12. Yêu cầu phi chức năng

### 12.1 Bảo mật

- Mật khẩu phải được hash bằng cơ chế chuẩn của framework.
- Chống CSRF cho web session và giới hạn tần suất đăng nhập/API.
- Validate mọi khóa ngoại theo công ty hiện tại.
- Không trả stack trace hoặc lỗi database cho người dùng cuối.
- File upload phải kiểm tra loại, kích thước và quyền truy cập.

### 12.2 Toàn vẹn và đồng thời

- Dùng transaction và row lock khi duyệt phiếu, chuyển kho và giao dịch tiền.
- Dùng unique constraint cho mã công ty, bút toán idempotency và các quan hệ không được trùng.
- Sử dụng decimal trong database; không dùng float làm nguồn lưu tiền.

### 12.3 Hiệu năng

- Danh sách phải phân trang và tránh N+1 query.
- Các cột lọc phổ biến phải có index phù hợp.
- Dashboard phải giới hạn khoảng thời gian và có thể cache số tổng hợp khi dữ liệu lớn.

### 12.4 Khả năng truy vết

- Mỗi số tổng hợp quan trọng phải truy ngược được đến chứng từ và bút toán nguồn.
- Không xóa cứng chứng từ đã phát sinh số liệu.

---

## 13. Tiêu chí nghiệm thu liên phân hệ

### 13.1 Mua hàng đến thanh toán

1. Tạo và duyệt PO: tồn kho và công nợ chưa đổi.
2. Tạo phiếu nhập một phần: tồn kho và công nợ chưa đổi khi phiếu còn `pending`.
3. Duyệt phiếu: tồn tăng đúng lượng thực nhập, giá trị tồn tăng theo tiền cơ sở, công nợ NCC tăng đúng giá trị gồm VAT.
4. Nhập phần còn lại: PO chuyển `completed`, không thể nhập vượt.
5. Tạo và duyệt thanh toán: số dư tài khoản giảm, sổ tài khoản có bút toán, công nợ NCC giảm đúng số tiền cơ sở.

### 13.2 Bán hàng đến thu tiền

1. Tạo và duyệt SO: tồn kho và công nợ chưa đổi.
2. Tạo phiếu xuất một phần: chưa tác động khi phiếu `pending`.
3. Duyệt phiếu: tồn và giá trị tồn giảm theo giá vốn bình quân; công nợ KH tăng theo giá bán thực xuất gồm VAT.
4. Không cho duyệt nếu kho thiếu hàng hoặc xuất vượt SO.
5. Duyệt thu tiền: số dư tài khoản tăng, ghi sổ và công nợ KH giảm.

### 13.3 Chuyển kho

1. Phiếu chờ duyệt không đổi tồn.
2. Duyệt phiếu giảm đúng lượng/giá trị kho nguồn và tăng đúng lượng/giá trị kho đích.
3. Tổng giá trị hai kho không đổi.
4. Có đủ hai bút toán chuyển ra/chuyển vào và không phát sinh công nợ.

### 13.4 Tỷ giá

1. Tiền cơ sở luôn có tỷ giá 1.
2. Chứng từ ngoại tệ chọn đúng tỷ giá gần nhất theo ngày chứng từ.
3. Thêm tỷ giá cho ngày sau không làm đổi chứng từ cũ.
4. Hai công ty có thể dùng tỷ giá khác nhau cho cùng tiền tệ và cùng ngày.

### 13.5 Phân quyền và cô lập dữ liệu

1. Người không có quyền bị từ chối ở API dù gọi trực tiếp.
2. Người dùng công ty A không thể đọc/sửa/duyệt dữ liệu công ty B bằng cách thay ID.
3. Tài khoản bị khóa không đăng nhập được.
4. Hành động duyệt, từ chối và khóa/mở có audit log.

---

## 14. Các quyết định cần xác nhận trước giai đoạn tiếp theo

1. Người tạo chứng từ có được tự duyệt chứng từ của mình không?
2. Có cần duyệt nhiều cấp theo hạn mức tiền hoặc loại chứng từ không?
3. Chính sách giữ chỗ tồn kho khi duyệt SO là gì?
4. Quy tắc hủy đơn đã nhập/xuất một phần và chứng từ đảo tương ứng.
5. Luồng trả hàng mua, trả hàng bán, kiểm kê và điều chỉnh tồn.
6. Luồng dịch vụ không đi qua kho nhưng vẫn phát sinh công nợ.
7. Chính sách chiết khấu dòng hàng/toàn đơn, phí vận chuyển và phân bổ VAT.
8. Chính sách chênh lệch tỷ giá khi thanh toán khác ngày ghi nhận công nợ.
9. Khóa kỳ kho/kế toán và quyền nhập chứng từ lùi ngày.
10. Quy tắc làm tròn chi tiết cho từng tiền tệ.
11. Có cho phép thấu chi tài khoản hoặc công nợ âm hay không?
12. Định dạng mã tự động cho KH, NCC, PO, SO, phiếu kho, giao dịch và chuyển kho.

Các mục trên không được tự triển khai theo giả định nếu có thể làm thay đổi số liệu tài chính hoặc quy trình kiểm soát nội bộ.

---

## 15. Ma trận truy vết nguồn yêu cầu

| Phần tài liệu | Sheet nguồn |
|---|---|
| Mục tiêu và 5 phân hệ | Mô tả chung |
| Nhân sự, vai trò, quyền, audit | Module-Quản Lý |
| NCC, danh mục, đơn vị, sản phẩm, PO | Module-Mua hàng |
| Kho, phiếu nhập/xuất, trạng thái thực hiện, dashboard | Module-Kho |
| Tiền tệ, ngân hàng, tài khoản, giao dịch, công nợ | Module-Kế Toán |
| Khách hàng, SO, VAT, tồn và dashboard | Module-Bán hàng |
| Công nợ theo lượng thực nhập/xuất | Mô tả chung + Module-Kho + quyết định nghiệp vụ đã chốt |
| Sổ tồn, giá vốn chuyển kho và tỷ giá theo công ty | Quy tắc toàn vẹn đã chốt trong quá trình triển khai |
