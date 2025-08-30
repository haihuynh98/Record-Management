# Hướng Dẫn Thiết Lập Cron Job - Nhắc Nhở Xử Lý Hồ Sơ

## 1. Kiểm Tra Command Đã Hoạt Động

### Test command thủ công:
```bash
# Chạy command một lần để test
php artisan profile:remind-process

# Xem danh sách tất cả commands
php artisan list | grep profile

# Xem chi tiết command
php artisan help profile:remind-process
```

## 2. Kiểm Tra Laravel Scheduler

### Xem danh sách scheduled tasks:
```bash
# Xem tất cả scheduled tasks
php artisan schedule:list

# Chạy tất cả scheduled tasks một lần (for testing)
php artisan schedule:run
```

## 3. Thiết Lập Cron Job Trên Server

### Phương pháp 1: Sử dụng Laravel Scheduler (Khuyến nghị)

#### Bước 1: Thêm vào crontab của server
```bash
# Mở crontab editor
crontab -e

# Thêm dòng này vào cuối file (thay đổi đường dẫn cho phù hợp)
* * * * * cd /Users/harihuynh/HariHuynh/TigerProjects/Record-Management && php artisan schedule:run >> /dev/null 2>&1
```

#### Bước 2: Kiểm tra crontab đã được thêm
```bash
# Xem crontab hiện tại
crontab -l
```

### Phương pháp 2: Chạy Laravel Scheduler như một service

#### Sử dụng `schedule:work` (Laravel 8+)
```bash
# Chạy scheduler như một daemon process
php artisan schedule:work

# Hoặc chạy trong background
nohup php artisan schedule:work > /dev/null 2>&1 &
```

### Phương pháp 3: Cron job trực tiếp (Không khuyến nghị)

```bash
# Thêm vào crontab để chạy mỗi 5 phút
*/5 * * * * cd /Users/harihuynh/HariHuynh/TigerProjects/Record-Management && php artisan profile:remind-process >> /dev/null 2>&1
```

## 4. Kiểm Tra Hoạt Động

### Kiểm tra log Laravel:
```bash
# Xem log realtime
tail -f storage/logs/laravel.log

# Xem log của ngày hôm nay
tail -100 storage/logs/laravel.log | grep "profile:remind-process"
```

### Kiểm tra cron log (trên Linux/macOS):
```bash
# Xem cron log
tail -f /var/log/cron

# Hoặc trên macOS
tail -f /var/log/system.log | grep cron
```

## 5. Troubleshooting

### Vấn đề thường gặp:

#### 1. Command không chạy
```bash
# Kiểm tra PHP path
which php

# Kiểm tra quyền file
ls -la artisan

# Chạy với full path
/usr/bin/php /full/path/to/project/artisan profile:remind-process
```

#### 2. Cron không hoạt động
```bash
# Kiểm tra cron service đang chạy
sudo service cron status

# Restart cron service
sudo service cron restart

# Kiểm tra syntax crontab
crontab -l
```

#### 3. Permission issues
```bash
# Đảm bảo user có quyền chạy
whoami

# Kiểm tra quyền thư mục
ls -la storage/logs/
chmod 755 storage/logs/
```

## 6. Monitoring & Logging

### Thêm logging chi tiết:
```bash
# Tạo log file riêng cho cron
*/5 * * * * cd /path/to/project && php artisan profile:remind-process >> storage/logs/cron-remind.log 2>&1
```

### Kiểm tra performance:
```bash
# Xem thời gian chạy command
time php artisan profile:remind-process
```

## 7. Production Setup

### Cho môi trường production:

#### 1. Sử dụng supervisor (khuyến nghị)
```ini
# /etc/supervisor/conf.d/laravel-scheduler.conf
[program:laravel-scheduler]
process_name=%(program_name)s
command=php /path/to/project/artisan schedule:work
directory=/path/to/project
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/scheduler.log
```

#### 2. Restart supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-scheduler
```

## 8. Verification Commands

### Kiểm tra toàn bộ hệ thống:
```bash
# 1. Test command
php artisan profile:remind-process

# 2. Check scheduler
php artisan schedule:list

# 3. Run scheduler once
php artisan schedule:run

# 4. Check crontab
crontab -l

# 5. Check logs
tail -20 storage/logs/laravel.log
```

## 9. Cấu Hình Hiện Tại

### Command được cấu hình:
- **Command**: `profile:remind-process`
- **Tần suất**: Mỗi 5 phút (`everyFiveMinutes()`)
- **Options**: `withoutOverlapping()`, `runInBackground()`
- **File cấu hình**: `app/Console/Kernel.php`

### Logic hoạt động:
1. Tìm hồ sơ status = 0 (chờ xử lý) trong ngày hôm nay
2. Priority user: gửi sau 15 phút
3. User thường: gửi sau 2 giờ  
4. Đã gửi rồi: gửi lại sau 15 phút
5. Cập nhật `last_notification_sent_at` sau khi gửi thành công

## 10. Quick Start

### Để bắt đầu nhanh:
```bash
# 1. Test command
php artisan profile:remind-process

# 2. Thêm vào crontab
crontab -e
# Thêm dòng: * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1

# 3. Kiểm tra sau 5 phút
tail -f storage/logs/laravel.log
```

**Lưu ý**: Thay `/path/to/project` bằng đường dẫn thực tế đến project của bạn: `/Users/harihuynh/HariHuynh/TigerProjects/Record-Management`
