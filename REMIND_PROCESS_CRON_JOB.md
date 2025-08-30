# Cron Job Nhắc Nhở Xử Lý Hồ Sơ

## Tổng quan
Cron job `profile:remind-process` được tạo để tự động nhắc nhở xử lý các hồ sơ đang chờ xử lý thông qua Telegram.

## Cấu hình
- **Command**: `php artisan profile:remind-process`
- **Tần suất chạy**: Mỗi 5 phút
- **Chạy background**: Có
- **Tránh chồng chéo**: Có (withoutOverlapping)

## Logic xử lý

### Điều kiện tìm kiếm hồ sơ
- Trạng thái: `status = 0` (đang chờ xử lý)
- Thời gian tạo: Trong ngày hôm nay
- Load kèm thông tin người tạo (`createdBy`)

### Case 1: Chưa từng gửi notification (`last_notification_sent_at = null`)

#### Người tạo là Priority User (`is_priority = true`)
- **Điều kiện**: Thời gian từ lúc tạo hồ sơ đến hiện tại ≥ 15 phút
- **Hành động**: Gửi thông báo nhắc nhở lần đầu

#### Người tạo không phải Priority User (`is_priority = false`)
- **Điều kiện**: Thời gian từ lúc tạo hồ sơ đến hiện tại ≥ 2 giờ
- **Hành động**: Gửi thông báo nhắc nhở lần đầu

### Case 2: Đã từng gửi notification (`last_notification_sent_at` có giá trị)
- **Điều kiện**: Thời gian từ lần gửi cuối cùng đến hiện tại ≥ 15 phút
- **Hành động**: Gửi thông báo nhắc nhở lại
- **Lưu ý**: Áp dụng cho tất cả hồ sơ, không phân biệt priority user

## Nội dung thông báo

### Thông tin hiển thị
- Mã hồ sơ
- ID nhân vật
- Người tạo
- Thời gian tạo
- Thời gian đã chờ (tính từ lúc tạo)
- Đánh dấu ưu tiên (nếu có)
- Phân biệt "LẦN ĐẦU" hoặc "NHẮC LẠI"

### Ví dụ thông báo
```
🔔 NHẮC NHỞ XỬ LÝ HỒ SƠ - LẦN ĐẦU 🔔

⭐ ƯU TIÊN ⭐

📋 Mã hồ sơ: #ABC123
👤 ID nhân vật: 12345
👨‍💼 Người tạo: john_doe
⏰ Thời gian tạo: 30/08/2025 10:30:00
⌛ Đã chờ: 2 giờ

💡 Hồ sơ này đang chờ xử lý, vui lòng kiểm tra và xử lý sớm nhất có thể!
```

## Cập nhật dữ liệu
- Sau khi gửi thành công, cột `last_notification_sent_at` sẽ được cập nhật với thời gian hiện tại
- Điều này đảm bảo logic tính toán cho lần gửi tiếp theo

## Logging
- Ghi log thành công/thất bại cho từng hồ sơ
- Log chi tiết lỗi nếu có vấn đề khi gửi thông báo
- Hiển thị thống kê tổng số thông báo đã gửi

## Chạy thủ công
```bash
# Chạy command một lần
php artisan profile:remind-process

# Xem danh sách scheduled tasks
php artisan schedule:list

# Chạy tất cả scheduled tasks (for testing)
php artisan schedule:run
```

## Lưu ý quan trọng
1. Command chỉ xử lý hồ sơ được tạo trong ngày hôm nay
2. Sử dụng `withoutOverlapping()` để tránh chạy đồng thời nhiều instance
3. Chạy trong background để không ảnh hưởng đến performance
4. Tự động cập nhật `last_notification_sent_at` sau khi gửi thành công
5. Có xử lý exception để tránh crash khi có lỗi với một hồ sơ cụ thể
