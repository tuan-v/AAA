# START HERE — Đưa dự án vào trạng thái có thể làm việc

Tài liệu này dành cho người mới nhận repository hoặc quay lại dự án sau một thời gian. Mục tiêu là giúp bạn **chạy được hệ thống, đăng nhập, xác định đúng phạm vi cần sửa và biết nơi tra cứu tiếp theo**.

Nếu tài liệu khác mã nguồn, ưu tiên theo thứ tự: route và middleware → migration → controller/service/model → giao diện Vue → test. Sau khi xác minh, cập nhật lại tài liệu bị lệch.

## 1. Chuẩn bị môi trường

Dự án sử dụng PHP 8.2+, Composer, Node.js/npm và một database được Laravel hỗ trợ. Cấu hình kết nối database, mail, queue và Reverb nằm trong `.env`.

Khi cài mới, tạo `.env` trước để kiểm tra cấu hình database rồi mới chạy script thiết lập:

```powershell
Copy-Item .env.example .env
# Cập nhật DB_* và các cấu hình môi trường cần thiết trong .env
composer run setup
```

`composer run setup` cài dependency PHP/JavaScript, tạo application key, chạy migration và build frontend. Script **không seed dữ liệu demo**.

Nếu không muốn dùng dữ liệu demo, chuyển thẳng sang bước chạy dự án. Nếu cần môi trường có sẵn công ty, người dùng và dữ liệu nghiệp vụ:

```powershell
php artisan db:seed
```

Seeder mặc định tạo tài khoản quản trị dành cho local/demo:

- Email: `admin@demo.vn`
- Mật khẩu: `12345678`

Không dùng tài khoản hoặc mật khẩu demo trong production.

## 2. Chạy dự án

Lệnh phát triển chuẩn của repository là:

```powershell
composer run dev
```

Lệnh này chạy đồng thời Laravel HTTP server, queue listener, Reverb và Vite. Không cần mở riêng `php artisan serve` hoặc `npm run dev` khi đã dùng lệnh trên.

Sau khi các tiến trình khởi động:

1. Mở URL Laravel hiển thị trong terminal, thông thường là `http://127.0.0.1:8000`.
2. Đăng nhập bằng tài khoản đã có hoặc tài khoản demo ở trên.
3. Đảm bảo tài khoản đã thuộc một công ty. Người dùng chưa có công ty sẽ phải hoàn tất `/company/create` trước khi vào dashboard.
4. Mở `/dashboard` và một phân hệ mà tài khoản có quyền truy cập.

Nếu một URL trả về `403`, kiểm tra permission của tài khoản trước khi kết luận màn hình hoặc route bị lỗi. Các trang nghiệp vụ đều có middleware quyền riêng.

## 3. Kiểm tra nhanh hệ thống

Chạy các kiểm tra nền trước khi bắt đầu sửa code:

```powershell
php artisan migrate:status
php artisan route:list --path=dashboard
php artisan test
npm run build
```

Nếu toàn bộ test quá lâu, trước tiên chạy file test gần nhất với module sẽ sửa, sau đó chạy đầy đủ trước khi bàn giao.

## 4. Chọn đúng nơi tra cứu

| Bạn đang cần làm gì? | Bắt đầu tại |
| --- | --- |
| Tìm màn hình, module, API hoặc backend liên quan | [`MODULE_INDEX.md`](MODULE_INDEX.md) |
| Tìm một controller/function, route, trang Vue hoặc test cụ thể | [`docs/PROJECT_FUNCTION_INDEX.md`](docs/PROJECT_FUNCTION_INDEX.md) |
| Tìm lỗi theo triệu chứng | [`docs/PROJECT_DEBUGGING_INDEX.md`](docs/PROJECT_DEBUGGING_INDEX.md) |
| Tra model, bảng và migration | [`docs/PROJECT_DATABASE_INDEX.md`](docs/PROJECT_DATABASE_INDEX.md) |
| Hiểu luồng mua, bán, kho, công nợ và giao dịch | [`resources/docs/BUSINESS_FLOWS.md`](resources/docs/BUSINESS_FLOWS.md) |
| Hiểu kiến trúc và luồng request tổng thể | [`PROJECT_INDEX.md`](PROJECT_INDEX.md) |
| Tra contract và danh mục API | [`API_DOCUMENTATION.md`](API_DOCUMENTATION.md) |
| Tra thuật ngữ nghiệp vụ | [`resources/docs/GLOSSARY.md`](resources/docs/GLOSSARY.md) |

Không cần đọc tuần tự tất cả tài liệu. Chọn tài liệu theo câu hỏi đang cần trả lời.

## 5. Cách lần theo một chức năng

Với một task giao diện hoặc nghiệp vụ, đi theo luồng:

```text
URL trang
→ routes/web.php
→ resources/js/Pages hoặc component Vue
→ axios endpoint
→ routes/api.php
→ Controller
→ Service/Repository
→ Model và migration
→ Feature/Unit test
```

Khi chỉ biết tên nghiệp vụ, bắt đầu từ `MODULE_INDEX.md`. Khi đã biết route, class hoặc method, dùng `docs/PROJECT_FUNCTION_INDEX.md` hoặc tìm trực tiếp bằng `rg`.

## 6. Bản đồ màn hình chính

| Phân hệ | URL chính | Quyền tiêu biểu |
| --- | --- | --- |
| Dashboard | `/`, `/dashboard` | Phụ thuộc tài khoản và công ty |
| Nhân sự và tổ chức | `/user`, `/departments`, `/positions` | `nhan_su.*` |
| Vai trò và quyền | `/role`, `/permission` | `vai_tro.*`, `quyen.*` |
| Mua hàng | `/purchase/*` | `don_mua.*`, `nha_cung_cap.*` và quyền danh mục |
| Bán hàng và POS | `/sale/*` | `don_ban.*`, `khach_hang.*`, `phieu_giam_gia.*` |
| Kho | `/warehouse/*` | `kho.*`, `phieu_kho.*`, `chuyen_kho.*` |
| Kế toán | `/accountant/*` | Quyền giao dịch, tài khoản, công nợ và đối soát tương ứng |
| Nhật ký | `/audit-logs` | `nhat_ky.xem` |
| Hướng dẫn trong ứng dụng | `/guide` | Cần đăng nhập và đã tạo công ty |
| Cửa hàng công khai | `/shop`, `/shop/{storefront_slug}` | Không dùng permission ERP cho các trang công khai |

Danh sách trên chỉ là lối vào. Route chi tiết và middleware hiện hành nằm trong `routes/web.php` và `routes/api.php`.

> Lưu ý: repository còn một số URL cũ hoặc trùng, tiêu biểu là `/users`. Khi sửa hoặc thêm liên kết, xác minh handler thực tế bằng `php artisan route:list` thay vì suy đoán từ tên URL.

## 7. Quy tắc phải kiểm tra khi sửa nghiệp vụ

- Query và dữ liệu nghiệp vụ phải được cô lập theo `company_id`.
- Frontend có thể ẩn thao tác theo permission, nhưng backend vẫn phải kiểm tra quyền.
- Chứng từ ở trạng thái trung gian không được cập nhật tồn kho, công nợ hoặc số dư trước bước duyệt có hiệu lực.
- Thao tác ghi nhiều bảng phải dùng database transaction và chống xử lý lặp.
- Chứng từ tiền tệ phải giữ tỷ giá tại thời điểm tạo/cập nhật; VND có tỷ giá 1.
- Nhập kho được ghi theo giá trị thực nhập gồm VAT; xuất và chuyển kho dùng giá nhập gần nhất theo nghiệp vụ hiện hành.
- Khi thay đổi trạng thái hoặc luồng duyệt, kiểm tra cả notification, realtime, audit log và test hồi quy.

Chi tiết và ngoại lệ của các quy tắc này nằm trong [`resources/docs/BUSINESS_FLOWS.md`](resources/docs/BUSINESS_FLOWS.md) và [`Document.md`](Document.md).

## 8. Trước khi bàn giao thay đổi

Chạy kiểm tra theo đúng phạm vi đã sửa, sau đó chạy bộ kiểm tra đầy đủ khi có thể:

```powershell
php artisan test --filter=TenTestLienQuan
php artisan route:list --path=prefix-lien-quan
npm run build

# Kiểm tra hồi quy toàn bộ
php artisan test
```

Xác nhận thêm nếu task có liên quan:

- permission và cô lập công ty;
- validation và HTTP status;
- migration, index và quan hệ dữ liệu;
- transaction, số dư, tồn kho hoặc công nợ;
- notification, realtime và audit log;
- tài liệu nghiệp vụ và test hồi quy.

Chỉ cập nhật [`resources/docs/CHANGELOG.md`](resources/docs/CHANGELOG.md) khi thay đổi hành vi nghiệp vụ đáng chú ý; không cần cập nhật changelog cho mọi chỉnh sửa nội bộ.
