# Chạy dự án bằng Docker và lấy tên miền tạm

## 1. Cài Docker trên Windows

Cài Docker Desktop, bật WSL 2 rồi khởi động Docker Desktop. Kiểm tra trong PowerShell:

```powershell
docker --version
docker compose version
```

## 2. Tạo cấu hình

Tại thư mục dự án:

```powershell
Copy-Item .env.docker.example .env.docker
docker run --rm php:8.3-cli-alpine php -r "echo 'base64:'.base64_encode(random_bytes(32));"
```

Sao chép kết quả `base64:...` vào `APP_KEY=` trong `.env.docker`. Database chuẩn của dự án là `demo_db`. Giữ `DB_DATABASE` giống `MYSQL_DATABASE`, `DB_USERNAME` giống `MYSQL_USER`, và `DB_PASSWORD` giống `MYSQL_PASSWORD`. Hãy đổi cả mật khẩu người dùng và mật khẩu root MySQL trước khi dùng cho dữ liệu thật.

File `.env.docker` là nguồn cấu hình của các container và đã được Git bỏ qua để tránh đưa khóa, mật khẩu lên repository. Service `db-init` chạy an toàn khi khởi động: tự tạo `demo_db` và cấp lại quyền nếu đang dùng volume MySQL cũ; không xóa database cũ và không cần `docker compose down -v`.

## 3. Khởi động

```powershell
docker compose up -d --build
docker compose ps
```

Mở cục bộ: `http://localhost:8080`.

## 4. Lấy URL HTTPS miễn phí

```powershell
docker compose logs tunnel
```

Tìm dòng chứa URL dạng:

```text
https://random-words.trycloudflare.com
```

Đây là URL tạm để gửi cho người khác truy cập. URL sẽ đổi nếu container tunnel được tạo lại và dịch vụ này chỉ dùng để demo/thử nghiệm.

Sau khi có URL, cập nhật `APP_URL` trong `.env.docker`, rồi chỉ tạo lại các container ứng dụng (không tạo lại tunnel):

```powershell
docker compose up -d --force-recreate app worker scheduler
```

## 5. Lệnh vận hành

```powershell
# Xem log
docker compose logs -f app worker tunnel

# Chạy seeder demo một lần nếu thật sự cần dữ liệu mẫu
docker compose exec app php artisan db:seed

# Dừng nhưng giữ dữ liệu
docker compose down

# Khởi động lại
docker compose up -d

# Sao lưu database
docker compose exec db sh -c 'mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"' > backup.sql
```

Không chạy `docker compose down -v` nếu muốn giữ database và file tải lên, vì tùy chọn `-v` xóa volume dữ liệu.

## 6. Khi dùng tên miền thật

Không dùng Quick Tunnel cho production. Hãy dùng VPS hoặc Cloudflare Named Tunnel, trỏ DNS tên miền thật, bật HTTPS, đặt `APP_URL=https://ten-mien.vn`, `SESSION_SECURE_COOKIE=true`, cấu hình SMTP và thay toàn bộ mật khẩu mặc định.
