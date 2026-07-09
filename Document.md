# DỰ ÁN: PHẦN MỀM QUẢN TRỊ DOANH NGHIỆP (ERP nội bộ)

> Tài liệu này tổng hợp toàn bộ yêu cầu nghiệp vụ từ file gốc, được viết lại theo dạng brief để đưa cho AI/Dev đọc và hiểu nhanh phạm vi dự án trước khi bắt tay code.

## 1. Tổng quan hệ thống

Phần mềm quản trị doanh nghiệp gồm 5 phân hệ (module) chính, liên kết chặt chẽ với nhau qua dòng chảy: **Mua hàng → Kho → Kế toán (công nợ, thu chi) ← Bán hàng**, cùng với module **Quản lý** làm nền tảng về người dùng, phân quyền và audit log.

| Phân hệ | Chức năng chính | Mô tả ngắn |
|---|---|---|
| Quản Lý | Nhân sự & Phân quyền | Quản lý tài khoản nhân viên, vai trò/quyền, audit log |
| Kế toán | Tài khoản & Quỹ | Quản lý tài khoản ngân hàng/quỹ tiền mặt, đa tiền tệ, lịch sử giao dịch, công nợ 2 chiều (KH & NCC) |
| Mua hàng | NCC & Đơn mua | Quản lý sản phẩm, nhà cung cấp, đơn mua hàng (PO) |
| Kho hàng | Tồn kho | Nhập/xuất kho theo đơn mua/bán, chuyển kho nội bộ, tự động cập nhật tồn kho & công nợ |
| Bán hàng | Khách hàng & Đơn bán | Quản lý khách hàng, đơn bán hàng (SO), công nợ khách hàng |

**Nguyên tắc luồng nghiệp vụ cốt lõi:**
- Đơn mua hàng (PO) được duyệt → sinh phiếu **nhập kho** → tăng tồn kho + tăng công nợ phải trả NCC.
- Đơn bán hàng (SO) được duyệt → sinh phiếu **xuất kho** → giảm tồn kho + tăng công nợ phải thu KH.
- Mọi giao dịch tiền (thanh toán/nhận thanh toán) đều gắn với tài khoản/quỹ, đối tượng (KH/NCC), và ảnh hưởng trực tiếp đến số dư + công nợ.
- Hệ thống hỗ trợ **đa tiền tệ** với tỷ giá quy đổi và lịch sử thay đổi tỷ giá.
- Danh mục dùng chung (sản phẩm, đơn vị tính, danh mục đa cấp) áp dụng theo quy tắc: **đã dùng thì không cho sửa/xóa, chỉ được khóa/mở**; chưa dùng thì cho sửa tự do.

---

## 2. Chi tiết từng module

### 2.1 Module Quản Lý

| # | Chức năng | Mô tả | Yêu cầu chi tiết |
|---|---|---|---|
| 1 | Quản lý nhân sự | Xem/quản lý danh sách tài khoản nhân sự, thêm mới, cập nhật, khóa đăng nhập | Thông tin: Tên, tên đăng nhập, mật khẩu, phòng ban, chức vụ, trạng thái |
| 2 | Phân quyền | Xem quyền theo vai trò, thêm/cập nhật vai trò, gán quyền cho tài khoản | Quyền quản lý chặt chẽ, cập nhật linh động theo vai trò (RBAC) |
| 3 | Lưu vết hoạt động | Audit log toàn hệ thống | Xem lịch sử truy cập + hành động của từng nhân sự |

### 2.2 Module Mua hàng

| # | Chức năng | Mô tả | Yêu cầu chi tiết |
|---|---|---|---|
| 1 | Quản lý NCC | Tên, SĐT, email, đơn vị tiền tệ, địa chỉ, công nợ đầu kỳ | List, thêm, sửa (chỉ khi chưa có đơn hàng gắn liền), tạo đơn ngay từ NCC |
| 2 | Quản lý đơn hàng (PO) | Mã, NCC, đơn vị tiền tệ, ngày dự kiến nhận, ghi chú, danh sách sản phẩm | List, sửa chỉ khi trạng thái "chờ xử lý", có duyệt/hủy đơn |
| 3 | Quản lý danh mục | Danh mục sản phẩm đa cấp | List, thêm, sửa (chỉ khi chưa dùng) → nếu đã dùng thì khóa/mở |
| 4 | Quản lý đơn vị tính | Tên, trạng thái, cho phép nhập lẻ hay không | List, thêm, sửa (chỉ khi chưa dùng) → nếu đã dùng thì khóa/mở |
| 5 | Quản lý sản phẩm | Tên, mã, danh mục, đơn vị, mô tả | List, thêm, sửa (chỉ khi chưa dùng) → nếu đã dùng thì khóa/mở |
| 6 | Tổng quan | Dashboard | Tổng số đơn, NCC, SP, tổng giá trị đơn hàng; đơn mua gần đây; thao tác nhanh; top NCC |

### 2.3 Module Kho

| # | Chức năng | Mô tả | Yêu cầu chi tiết |
|---|---|---|---|
| 1 | Quản lý kho | Tên, mã, địa chỉ | List, thêm, sửa (chỉ khi chưa dùng) → khóa/mở; hiển thị tổng giá trị SP trong kho |
| 2 | Quản lý danh mục | Danh mục đa cấp | Giống quy tắc trên |
| 3 | Quản lý đơn vị tính | Tên, trạng thái, nhập lẻ | Giống quy tắc trên |
| 4 | Quản lý sản phẩm | Tên, mã, danh mục, đơn vị, mô tả | Giống quy tắc trên |
| 5 | Danh sách đơn hàng (mua/bán) | Đơn được duyệt → hiển thị ở kho để xử lý | Tạo phiếu nhập (từ đơn mua)/phiếu xuất (từ đơn bán). Một phiếu có thể nhập/xuất **nhiều lần** (nhập/xuất một phần). Trạng thái: "Đầy đủ" / "Nhập, xuất một phần" |
| 6 | Phiếu nhập/xuất | Mã, đơn hàng, kho, nhân sự (tạo/duyệt), thời gian (tạo/duyệt), trạng thái, ghi chú | Khi duyệt phiếu → tự động **tăng/giảm tồn kho** theo loại phiếu (mua = tăng, bán = giảm) |
| 7 | Tổng quan | Dashboard kho | Biểu đồ nhập-xuất-tồn theo tháng/năm; danh sách kho + tổng giá trị; lịch sử nhập xuất gần đây |

### 2.4 Module Kế toán

| # | Chức năng | Mô tả | Yêu cầu chi tiết |
|---|---|---|---|
| 1 | Quản lý tiền tệ | Danh sách đơn vị tiền tệ, tỷ giá | Tên, mã, tỷ giá hiện tại, **lịch sử thay đổi tỷ giá**. Thêm/sửa (nếu chưa dùng)/khóa |
| 2 | Quản lý ngân hàng | Danh sách ngân hàng sử dụng | — |
| 3 | Quản lý tài khoản & quỹ | Tài khoản ngân hàng + quỹ tiền mặt | Tên, mã, đơn vị tiền tệ, loại, số dư hiện tại, số dư đầu kỳ. Loại "ngân hàng" cần thêm số TK + tên NH |
| 4 | Quản lý giao dịch | Xem toàn bộ lịch sử giao dịch | Bộ lọc theo tiền tệ, ngày, loại giao dịch... |
| 5 | Thanh toán / Nhận thanh toán | Tạo giao dịch tiền ra/vào | Dữ liệu: số tiền, tài khoản thực hiện, người thực hiện, loại giao dịch, nội dung, đối tượng (KH/NCC), đơn vị tiền tệ, đơn hàng liên quan. **Phải đảm bảo tài khoản nguồn/đích và số dư sau giao dịch chính xác** |
| 6 | Loại giao dịch | Phân loại giao dịch để tổng hợp báo cáo | Thêm mới; chỉ xóa được nếu chưa dùng |
| 7 | Quản lý công nợ khách hàng | Xem danh sách + công nợ KH | Bộ lọc đa tiền tệ. Chi tiết KH: tổng công nợ, công nợ hiện tại, lịch sử đơn hàng, lịch sử công nợ (tăng khi có đơn, giảm khi thanh toán) |
| 8 | Quản lý công nợ NCC | Xem danh sách + công nợ NCC | Tương tự công nợ KH nhưng chiều ngược lại (công nợ phải trả) |
| 9 | Tổng quan | Dashboard kế toán | Tổng tiền các tài khoản, tổng công nợ KH/NCC, biểu đồ biến động công nợ vào/ra, biểu đồ dòng tiền, 10 giao dịch gần nhất |

**Khái niệm công nợ (quan trọng):**
- **Công nợ đầu vào** (phải trả): phát sinh từ đơn mua hàng/dịch vụ với NCC.
- **Công nợ đầu ra** (phải thu): phát sinh từ đơn bán hàng/dịch vụ với KH.

### 2.5 Module Bán hàng

| # | Chức năng | Mô tả | Yêu cầu chi tiết |
|---|---|---|---|
| 1 | Quản lý khách hàng | Xem/quản lý danh sách KH, chi tiết, thêm/sửa/khóa, lọc | Thông tin: Tên, Mã KH (auto), Email, SĐT, đơn vị tiền tệ, công nợ đầu kỳ, địa chỉ (Tỉnh/Thành - Xã/Phường). Trang chi tiết: thông tin cá nhân, công nợ (tổng/đã thanh toán/còn phải thu), lịch sử đơn hàng, lịch sử công nợ. **Đề xuất nâng cao:** tạo đơn ngay từ list/detail KH |
| 2 | Quản lý đơn hàng (SO) | Mã, KH, đơn vị tiền tệ, địa chỉ giao chi tiết, ngày dự kiến giao, ghi chú, sản phẩm | Sửa chỉ khi "chờ xử lý"; có duyệt/hủy đơn. Thêm SP vào đơn cần: tên-mã, số lượng (kiểm tra tồn kho), VAT (%), đơn giá, thành tiền. Trang chi tiết đơn: đầy đủ thông tin, danh sách SP, lịch sử công nợ của đơn. **Đề xuất nâng cao:** tạo đơn từ đơn lịch sử |
| 3 | Tổng quan | Dashboard bán hàng | Tổng số đơn, KH, SP, tổng giá trị đơn hàng; đơn bán gần đây; thao tác nhanh; top KH mua hàng |

---

## 3. Phân chia công việc: Con người vs AI

### 3.1 Việc CON NGƯỜI cần làm (quyết định nghiệp vụ & quản lý dự án)

1. **Chốt nghiệp vụ còn mơ hồ** trước khi code, ví dụ:
   - Quy trình duyệt đơn: 1 cấp hay nhiều cấp? Ai có quyền duyệt PO/SO?
   - Quy tắc sinh mã tự động (mã KH, mã NCC, mã đơn, mã sản phẩm...) theo format nào?
   - Chính sách VAT: áp dụng theo sản phẩm hay theo đơn hàng? Có nhiều mức thuế suất không?
   - Xử lý khi hủy đơn đã có phiếu nhập/xuất một phần?
   - Chính sách làm tròn số khi quy đổi ngoại tệ.
   - Danh sách vai trò (role) cụ thể và ma trận quyền chi tiết cho từng vai trò.
2. **Cung cấp dữ liệu mẫu/dữ liệu khởi tạo**: danh mục ngân hàng, đơn vị tiền tệ ban đầu, cơ cấu phòng ban/chức vụ.
3. **Duyệt UI/UX**: xác nhận layout dashboard, các trường hiển thị ở từng trang danh sách/chi tiết.
4. **Test nghiệp vụ (UAT)**: kiểm thử các luồng liên module (Mua hàng → Kho → Kế toán; Bán hàng → Kho → Kế toán) để đảm bảo số liệu tự động cập nhật đúng.
5. **Quản lý phạm vi (scope)**: quyết định các "chức năng nâng cao đề xuất" (tạo đơn từ KH/NCC, tạo đơn từ lịch sử) có nằm trong giai đoạn 1 hay để sau.
6. **Bảo mật & hạ tầng**: chọn phương án lưu mật khẩu (hash), hosting, backup dữ liệu.

### 3.2 Việc AI/Dev có thể triển khai trực tiếp

1. **Thiết kế cơ sở dữ liệu (ERD)** cho toàn bộ 5 module dựa trên các thực thể đã liệt kê (Nhân sự, Vai trò, Quyền, NCC, KH, Sản phẩm, Danh mục, Đơn vị tính, Đơn mua/bán, Kho, Phiếu nhập/xuất, Tài khoản/Quỹ, Giao dịch, Loại giao dịch, Công nợ, Tiền tệ, Tỷ giá).
2. **Xây dựng API/backend** theo từng module với các nghiệp vụ CRUD + workflow duyệt/khóa-mở đã mô tả.
3. **Logic tự động**: 
   - Tự động tăng/giảm tồn kho khi duyệt phiếu nhập/xuất.
   - Tự động tăng/giảm công nợ khi đơn hàng được duyệt hoặc khi có giao dịch thanh toán.
   - Tự động cập nhật số dư tài khoản/quỹ sau mỗi giao dịch.
   - Tự động tính tỷ giá quy đổi khi báo cáo đa tiền tệ.
4. **Xây dựng giao diện (frontend)** cho các trang: danh sách, chi tiết, form thêm/sửa, dashboard tổng quan từng module.
5. **Xây dựng báo cáo/biểu đồ**: nhập-xuất-tồn theo tháng/năm, biến động công nợ, dòng tiền.
6. **Viết audit log middleware** để tự động ghi vết hành động của người dùng.
7. **Viết test case** dựa theo các yêu cầu/quy tắc nghiệp vụ đã liệt kê ở trên (đặc biệt các case "đã dùng thì khóa thay vì xóa").

---

## 4. Ghi chú khi đưa tài liệu này cho AI

- Đây là bản mô tả **nghiệp vụ (business requirement)**, chưa phải đặc tả kỹ thuật (technical spec) — AI cần hỏi lại các điểm ở mục 3.1 nếu người dùng chưa chốt.
- Toàn bộ hệ thống vận hành theo nguyên tắc **liên thông dữ liệu thời gian thực** giữa 4 module nghiệp vụ (Mua hàng, Kho, Kế toán, Bán hàng) qua module Kế toán làm trung tâm công nợ/dòng tiền.
- Khi AI thiết kế DB hoặc code, nên bắt đầu từ **module Quản lý** (user, role, permission) làm nền, sau đó đến **danh mục dùng chung** (sản phẩm, đơn vị tính, danh mục, tiền tệ), rồi mới đến **nghiệp vụ giao dịch** (đơn mua/bán, phiếu kho, giao dịch tiền).