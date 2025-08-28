# Hướng dẫn cấu hình hệ thống Telegram Notification

## Tổng quan

Hệ thống này bao gồm:
1. **Telegram Bot Notifications**: Gửi thông báo đến Telegram khi có thay đổi trạng thái hồ sơ
2. **Database Notifications**: Push notification trong Filament admin panel
3. **Scheduled Jobs**: Tự động kiểm tra hồ sơ chờ xử lý định kỳ

## Cấu hình Telegram Bot

### 1. Tạo Telegram Bot

1. Mở Telegram và tìm @BotFather
2. Gửi lệnh `/newbot`
3. Đặt tên cho bot và username
4. Lưu lại **Bot Token** được cung cấp

### 2. Lấy Chat ID

1. Thêm bot vào group/channel mà bạn muốn nhận thông báo
2. Gửi một tin nhắn trong group/channel
3. Truy cập: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
4. Tìm `chat_id` trong response JSON

### 3. Cấu hình Environment Variables

Thêm các biến môi trường sau vào file `.env`:

```env
# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here

# App URL (cần thiết cho các link trong notification)
APP_URL=http://localhost:8000
```

## Cấu hình Queue

### 1. Cấu hình Queue Driver

Trong file `.env`, đảm bảo queue được cấu hình:

```env
QUEUE_CONNECTION=database
```

### 2. Tạo bảng queue

```bash
php artisan queue:table
php artisan migrate
```

### 3. Chạy Queue Worker

```bash
php artisan queue:work
```

## Cấu hình Schedule

### 1. Thêm Cron Job

Thêm cron job sau vào server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Hoặc chạy thủ công

```bash
php artisan schedule:run
```

## Cấu hình Database Notifications

### 1. Tạo bảng notifications

```bash
php artisan notifications:table
php artisan migrate
```

### 2. Cấu hình Filament

Notifications đã được cấu hình tự động trong `AdminPanelProvider.php`.

## Cách hoạt động

### 1. Hồ sơ chờ xử lý

- **Người ưu tiên**: Sau 15 phút → gửi thông báo Telegram
- **Người thường**: Sau 2 giờ → gửi thông báo Telegram

### 2. Hồ sơ bị từ chối

- Gửi thông báo Telegram ngay lập tức
- Gửi push notification cho người tạo hồ sơ

### 3. Hồ sơ bị hủy

- Gửi thông báo Telegram ngay lập tức
- Gửi push notification cho người tạo hồ sơ

## Testing

### 1. Test Command

```bash
php artisan profiles:check-pending
```

### 2. Test Job

```bash
php artisan queue:work --once
```

### 3. Test Event

Tạo một hồ sơ và thay đổi trạng thái trong admin panel.

## Troubleshooting

### 1. Telegram không gửi được

- Kiểm tra bot token và chat ID
- Đảm bảo bot có quyền gửi tin nhắn trong group/channel
- Kiểm tra logs: `storage/logs/laravel.log`

### 2. Queue không hoạt động

- Kiểm tra queue connection trong `.env`
- Đảm bảo queue worker đang chạy
- Kiểm tra failed jobs: `php artisan queue:failed`

### 3. Schedule không chạy

- Kiểm tra cron job đã được cấu hình
- Chạy thủ công: `php artisan schedule:run`
- Kiểm tra logs để debug

## Files quan trọng

- `app/Services/TelegramService.php` - Service xử lý Telegram
- `app/Jobs/SendTelegramNotification.php` - Job gửi Telegram
- `app/Jobs/CheckPendingProfiles.php` - Job kiểm tra hồ sơ chờ xử lý
- `app/Events/ProfileStatusChanged.php` - Event khi thay đổi trạng thái
- `app/Listeners/HandleProfileStatusChange.php` - Listener xử lý event
- `app/Notifications/ProfileStatusNotification.php` - Database notification
- `app/Console/Commands/CheckPendingProfilesCommand.php` - Command kiểm tra
- `app/Console/Kernel.php` - Cấu hình schedule
- `config/telegram.php` - Cấu hình Telegram
- `config/filament-notifications.php` - Cấu hình Filament notifications
