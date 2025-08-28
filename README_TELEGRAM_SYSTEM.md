# Hệ thống Telegram Notification cho Record Management

## Tổng quan

Hệ thống này đã được triển khai để tự động gửi thông báo qua Telegram và push notification trong Filament admin panel khi có thay đổi trạng thái hồ sơ.

## Tính năng đã triển khai

### 1. Telegram Bot Notifications
- ✅ Gửi thông báo khi hồ sơ chờ xử lý quá thời gian
- ✅ Gửi thông báo khi hồ sơ bị từ chối
- ✅ Gửi thông báo khi hồ sơ bị hủy
- ✅ Hỗ trợ người dùng ưu tiên (15 phút) và thường (2 giờ)

### 2. Database Notifications (Filament)
- ✅ Push notification cho người tạo hồ sơ khi bị từ chối
- ✅ Push notification cho người tạo hồ sơ khi bị hủy
- ✅ Hiển thị trong admin panel

### 3. Scheduled Jobs
- ✅ Tự động kiểm tra hồ sơ chờ xử lý mỗi 5 phút
- ✅ Queue system để xử lý bất đồng bộ

## Cấu trúc hệ thống

### Services
- `app/Services/TelegramService.php` - Service xử lý việc gửi tin nhắn Telegram

### Jobs
- `app/Jobs/SendTelegramNotification.php` - Job gửi thông báo Telegram
- `app/Jobs/CheckPendingProfiles.php` - Job kiểm tra hồ sơ chờ xử lý

### Events & Listeners
- `app/Events/ProfileStatusChanged.php` - Event khi thay đổi trạng thái
- `app/Listeners/HandleProfileStatusChange.php` - Listener xử lý event

### Notifications
- `app/Notifications/ProfileStatusNotification.php` - Database notification cho Filament

### Commands
- `app/Console/Commands/CheckPendingProfilesCommand.php` - Command kiểm tra hồ sơ
- `app/Console/Commands/TestTelegramNotification.php` - Command test hệ thống

### Configuration
- `config/telegram.php` - Cấu hình Telegram Bot
- `config/filament-notifications.php` - Cấu hình Filament notifications
- `app/Console/Kernel.php` - Cấu hình schedule

## Cách hoạt động

### 1. Hồ sơ chờ xử lý
```
Người ưu tiên (is_priority = true):
- Sau 15 phút → Gửi Telegram notification

Người thường (is_priority = false):
- Sau 2 giờ → Gửi Telegram notification
```

### 2. Hồ sơ bị từ chối
```
Khi status thay đổi từ 0 → 2:
- Gửi Telegram notification ngay lập tức
- Gửi push notification cho người tạo hồ sơ
```

### 3. Hồ sơ bị hủy
```
Khi status thay đổi từ 2 → 3:
- Gửi Telegram notification ngay lập tức
- Gửi push notification cho người tạo hồ sơ
```

## Cấu hình cần thiết

### 1. Environment Variables
Thêm vào file `.env`:
```env
# Telegram Bot
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here

# App URL
APP_URL=http://localhost:8000

# Queue
QUEUE_CONNECTION=database
```

### 2. Tạo Telegram Bot
1. Tìm @BotFather trên Telegram
2. Gửi `/newbot`
3. Đặt tên và username cho bot
4. Lưu Bot Token

### 3. Lấy Chat ID
1. Thêm bot vào group/channel
2. Gửi tin nhắn trong group
3. Truy cập: `https://api.telegram.org/bot<TOKEN>/getUpdates`
4. Tìm `chat_id` trong response

## Testing

### 1. Test Telegram Notification
```bash
# Test pending notification
php artisan telegram:test pending

# Test rejected notification
php artisan telegram:test rejected

# Test cancelled notification
php artisan telegram:test cancelled
```

### 2. Test Check Pending Profiles
```bash
php artisan profiles:check-pending
```

### 3. Test Queue
```bash
# Chạy queue worker
php artisan queue:work

# Xem failed jobs
php artisan queue:failed
```

### 4. Test Schedule
```bash
# Chạy schedule thủ công
php artisan schedule:run
```

## Deployment

### 1. Production Setup
```bash
# Chạy migrations
php artisan migrate

# Cấu hình cron job
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

# Chạy queue worker (supervisor recommended)
php artisan queue:work --daemon
```

### 2. Supervisor Configuration
Tạo file `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path-to-project/storage/logs/worker.log
stopwaitsecs=3600
```

## Monitoring

### 1. Logs
- `storage/logs/laravel.log` - Application logs
- `storage/logs/worker.log` - Queue worker logs

### 2. Failed Jobs
```bash
# Xem failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### 3. Queue Status
```bash
# Xem queue status
php artisan queue:work --once
```

## Troubleshooting

### 1. Telegram không gửi được
- Kiểm tra bot token và chat ID
- Đảm bảo bot có quyền gửi tin nhắn
- Kiểm tra logs: `tail -f storage/logs/laravel.log`

### 2. Queue không hoạt động
- Kiểm tra queue connection
- Đảm bảo queue worker đang chạy
- Kiểm tra failed jobs

### 3. Schedule không chạy
- Kiểm tra cron job
- Chạy thủ công: `php artisan schedule:run`
- Kiểm tra logs

### 4. Database notifications không hiển thị
- Kiểm tra bảng notifications đã được tạo
- Đảm bảo User model có trait `Notifiable`
- Kiểm tra Filament configuration

## Files đã tạo/sửa đổi

### Tạo mới:
- `app/Services/TelegramService.php`
- `app/Jobs/SendTelegramNotification.php`
- `app/Jobs/CheckPendingProfiles.php`
- `app/Events/ProfileStatusChanged.php`
- `app/Listeners/HandleProfileStatusChange.php`
- `app/Notifications/ProfileStatusNotification.php`
- `app/Console/Commands/CheckPendingProfilesCommand.php`
- `app/Console/Commands/TestTelegramNotification.php`
- `app/Console/Kernel.php`
- `app/Providers/EventServiceProvider.php`
- `config/telegram.php`
- `config/filament-notifications.php`
- `resources/views/notifications/database-notifications-trigger.blade.php`

### Sửa đổi:
- `app/Models/Profile.php` - Thêm event listeners
- `app/Providers/Filament/AdminPanelProvider.php` - Cấu hình notifications
- `config/services.php` - Thêm cấu hình Telegram
- `bootstrap/app.php` - Đăng ký EventServiceProvider

## Kết luận

Hệ thống đã được triển khai đầy đủ với:
- ✅ Telegram notifications cho tất cả các trường hợp
- ✅ Database notifications cho Filament
- ✅ Scheduled jobs tự động
- ✅ Queue system bất đồng bộ
- ✅ Error handling và logging
- ✅ Testing commands

Chỉ cần cấu hình Telegram Bot và environment variables là có thể sử dụng ngay!
