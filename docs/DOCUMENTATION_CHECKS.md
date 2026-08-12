# Kiểm tra sai lệch tài liệu

Bộ kiểm tra giúp phát hiện tài liệu hỏng hoặc không còn đồng bộ với mã nguồn mà không tự sửa nội dung hiện có.

## Kiểm tra nhanh

Chạy trước khi commit thay đổi tài liệu:

```bash
composer docs:check
```

Lệnh này chỉ đọc file và kiểm tra:

- file tài liệu bắt buộc có tồn tại;
- liên kết nội bộ và số dòng được liên kết còn hợp lệ;
- thẻ `<details>` và marker `GENERATED_*` đóng/mở cân bằng;
- các phần viết tay quan trọng trong `MODULE_INDEX.md` còn nguyên;
- các lỗi lặp từ đã biết như `nhân sự/nhân sự`.

## Kiểm tra sai lệch với mã nguồn

Chạy khi sửa route, Controller, Service, Model, migration, trang Vue hoặc bộ sinh tài liệu:

```bash
composer docs:check-drift
```

Chế độ này sao chép những nguồn cần thiết vào thư mục tạm, chạy bộ sinh tại đó và so sánh kết quả với tài liệu hiện tại. Tài liệu trong dự án không bị ghi lại. Thư mục tạm được xóa sau khi kiểm tra, kể cả khi có lỗi.

Nếu lệnh báo tài liệu bị lệch:

```bash
composer docs:generate
composer docs:check-drift
```

Sau khi sinh lại, hãy đọc `git diff` để chắc chắn phần thay đổi đúng với nghiệp vụ. Không sửa trực tiếp nội dung nằm giữa marker `GENERATED_*_START` và `GENERATED_*_END`; hãy sửa mã nguồn hoặc `docs/generate_project_function_index.php` rồi sinh lại.

## Kết quả lệnh

- Mã thoát `0`: tài liệu hợp lệ.
- Mã thoát `1`: có lỗi; thông báo chỉ rõ file, dòng và nguyên nhân.
- Bộ kiểm tra không tự động thay đổi tài liệu.

