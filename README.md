# Fastcall CRM

Hệ thống quản lý hồ sơ (CRM) xây dựng trên **Laravel 12** và **Filament 3**, dùng cho nội bộ Fastcall/Talentway.

- **Admin panel:** `/admin`
- **Đăng nhập:** username + password (không dùng email để login)
- **Database:** MySQL
- **Queue:** database driver (Telegram notification, job nền)
- **Scheduler:** kiểm tra hồ sơ chờ xử lý & nhắc nhở xử lý hồ sơ

## Yêu cầu hệ thống

| Thành phần | Phiên bản |
|---|---|
| PHP | >= 8.2 (khuyến nghị 8.3+) |
| Composer | 2.x |
| Node.js | >= 18 |
| MySQL | >= 8.0 |
| PHP extensions | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo` |

## Cấu trúc chính

```
app/
├── Console/Commands/     # Artisan commands (super admin, cron, telegram test...)
├── Filament/
│   ├── Auth/             # Màn hình đăng nhập
│   ├── Pages/            # Dashboard, thống kê, cấu hình hệ thống
│   └── Resources/        # CRUD hồ sơ, user, role, game, VIP...
├── Models/
└── Services/             # Telegram, notification...
database/
├── migrations/
└── seeders/              # Roles, permissions, cấu hình mặc định
```

## Tính năng chính

- Quản lý hồ sơ: tạo, duyệt, từ chối, nộp lại, hủy, hỗ trợ, chờ duyệt
- Phân quyền theo role: `super_admin`, `admin`, `approver`, `creator` (Filament Shield + Spatie Permission)
- Thống kê dashboard & báo cáo theo ngày/người duyệt/giờ ngoài giờ
- Thông báo Telegram & notification trong admin panel
- Cấu hình hệ thống (chặn đăng nhập, v.v.)
- Quản lý phiên xem hồ sơ (viewing sessions)

## Cấu hình môi trường (`.env`)

Sao chép file mẫu và chỉnh sửa:

```bash
cp .env.example .env
```

Các biến quan trọng:

```env
APP_NAME="Fastcall CRM"
APP_ENV=local                    # production trên server thật
APP_DEBUG=true                   # false trên production
APP_URL=http://localhost:8000    # URL public của ứng dụng
APP_TIMEZONE=Asia/Ho_Chi_Minh

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fastcall-tailentway
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# Telegram (tùy chọn — cần cho thông báo Telegram)
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=
TELEGRAM_SUPPORT_CHAT_ID=
TELEGRAM_WAITING_CHAT_ID=
TELEGRAM_REMINDER_CHAT_ID=
TELEGRAM_DELAYED_NOTIFICATION_MINUTES=360

# Yêu cầu mật khẩu hồ sơ có ký tự đặc biệt
PROFILE_PASSWORD_REQUIRE_SPECIAL_CHAR=false
```

> Chi tiết cấu hình Telegram: xem [README_TELEGRAM_SYSTEM.md](README_TELEGRAM_SYSTEM.md) và [TELEGRAM_NOTIFICATION_SETUP.md](TELEGRAM_NOTIFICATION_SETUP.md).

---

## Deploy môi trường Development

### 1. Clone & cài dependency

```bash
git clone <repository-url> fastcall-tailentway
cd fastcall-tailentway

composer install
npm install
```

### 2. Cấu hình database

Tạo database MySQL (ví dụ `fastcall-tailentway`), sau đó cập nhật `.env` với thông tin kết nối.

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Migration

```bash
# Chạy toàn bộ migration
php artisan migrate

# Reset database (CHỈ dùng trên dev — xóa toàn bộ dữ liệu)
php artisan migrate:fresh
```

### 4. Seeder & tài khoản admin

Xem mục [Seeder](#seeder) bên dưới.

### 5. Chạy ứng dụng

**Cách 1 — một lệnh (khuyến nghị):**

```bash
composer dev
```

Lệnh này chạy đồng thời: `php artisan serve`, `queue:listen`, `pail` (log), `npm run dev` (Vite).

**Cách 2 — tách terminal:**

```bash
# Terminal 1 — web server
php artisan serve

# Terminal 2 — Vite (frontend assets)
npm run dev

# Terminal 3 — queue worker (cần cho Telegram/job)
php artisan queue:listen

# Terminal 4 — scheduler (dev, thay cho cron)
php artisan schedule:work
```

Truy cập: [http://localhost:8000/admin](http://localhost:8000/admin)

---

## Deploy môi trường Production

### 1. Chuẩn bị server

- Nginx (hoặc Apache) trỏ document root tới thư mục `public/`
- PHP-FPM >= 8.2
- MySQL
- Composer, Node.js (chỉ cần lúc build)
- Supervisor (khuyến nghị cho queue worker)
- Cron (bắt buộc cho scheduled tasks)

### 2. Deploy code

```bash
git pull origin main   # hoặc clone lần đầu

composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### 3. Cấu hình `.env` production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database production
DB_HOST=...
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

Nếu đứng sau reverse proxy / Cloudflare (HTTPS), đảm bảo `APP_URL` dùng `https://` và cấu hình trust proxy đúng (xem [laravel-https-proxy](https://laravel.com/docs/12.x/requests#configuring-trusted-proxies)).

### 4. Migration & seeder

```bash
# Migration (bắt buộc mỗi lần deploy có migration mới)
php artisan migrate --force

# Seeder — CHỈ chạy lần đầu hoặc khi thêm permission mới (xem mục Seeder)
php artisan db:seed --force
```

### 5. Tối ưu Laravel

```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

### 6. Phân quyền thư mục

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache   # user PHP-FPM trên server
```

### 7. Cron — Laravel Scheduler

Thêm vào crontab của user chạy web (thay `/path/to/fastcall-tailentway`):

```cron
* * * * * cd /path/to/fastcall-tailentway && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled tasks hiện tại:

| Command | Tần suất | Mô tả |
|---|---|---|
| `profiles:check-pending` | Mỗi 5 phút | Kiểm tra hồ sơ chờ xử lý, gửi Telegram |
| `profile:remind-process` | Mỗi 5 phút | Nhắc nhở xử lý hồ sơ mới |

Kiểm tra:

```bash
php artisan schedule:list
```

### 8. Queue Worker — Supervisor

Tạo `/etc/supervisor/conf.d/fastcall-worker.conf`:

```ini
[program:fastcall-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/fastcall-tailentway/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/fastcall-tailentway/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start fastcall-worker:*
```

### 9. Tạo Super Admin trên production

```bash
php artisan make:super-admin \
  --username=admin \
  --email=admin@your-domain.com \
  --password='your-secure-password'
```

### 10. Checklist sau deploy

- [ ] `php artisan migrate --force` thành công
- [ ] Seeder đã chạy (lần đầu)
- [ ] Super admin đã tạo
- [ ] `npm run build` đã chạy (assets frontend)
- [ ] Cron `schedule:run` hoạt động
- [ ] Queue worker đang chạy
- [ ] Đăng nhập được tại `/admin`
- [ ] Telegram gửi được (nếu bật)

---

## Migration

```bash
# Xem trạng thái migration
php artisan migrate:status

# Chạy migration mới
php artisan migrate

# Production (không hỏi xác nhận)
php artisan migrate --force

# Rollback 1 batch (cẩn thận trên production)
php artisan migrate:rollback

# Reset toàn bộ + seed lại (CHỈ dev)
php artisan migrate:fresh --seed
```

Dự án có **24 migration** — bao gồm users, profiles, permissions, jobs, notifications, system settings, viewing sessions, v.v.

---

## Seeder

### Seeder chạy qua `db:seed` (DatabaseSeeder)

Lệnh:

```bash
php artisan db:seed
# hoặc production:
php artisan db:seed --force
```

`DatabaseSeeder` thực hiện:

1. Tạo user mặc định dev: `admin` / `admin@example.com` / mật khẩu `123456`
2. Gọi các seeder sau:

| Seeder | Mục đích |
|---|---|
| `PermissionSeeder` | **Bắt buộc** — tạo roles (`super_admin`, `admin`, `approver`, `creator`) và permissions |
| `RoleSeeder` | Gán role `admin` cho user `admin@example.com` |
| `DashboardChartPermissionSeeder` | Quyền widget dashboard |
| `AwaitingApprovalProfilesPermissionSeeder` | Quyền trang hồ sơ chờ duyệt |
| `ProfileApprovedByStatisticsPermissionSeeder` | Quyền thống kê theo người duyệt |
| `ProfileApprovedByDateStatisticsPermissionSeeder` | Quyền thống kê theo ngày/người duyệt |
| `OffHoursStatisticsPermissionSeeder` | Quyền thống kê ngoài giờ |
| `OffHoursDateStatisticsPermissionSeeder` | Quyền thống kê ngoài giờ theo ngày |
| `OffHoursApprovedByStatisticsPermissionSeeder` | Quyền thống kê ngoài giờ theo người duyệt |
| `OffHoursApprovedByDateStatisticsPermissionSeeder` | Quyền thống kê ngoài giờ theo ngày/người duyệt |
| `ViewingSessionsPermissionSeeder` | Quyền quản lý phiên xem hồ sơ |

> User mặc định từ seeder có role **`admin`**, không phải `super_admin`. Trên production nên tạo super admin riêng (xem bên dưới) và **đổi/xóa** tài khoản dev mặc định.

### Seeder bổ sung (chạy riêng — khuyến nghị lần đầu cài)

Các seeder này **không** nằm trong `DatabaseSeeder`, cần chạy thủ công sau `db:seed`:

```bash
php artisan db:seed --class=SystemSettingsSeeder
php artisan db:seed --class=SystemConfigurationPermissionSeeder
php artisan db:seed --class=ViewSupportProfilesPermissionSeeder
php artisan db:seed --class=ResolveSupportPermissionSeeder
php artisan db:seed --class=AwaitingApprovalProfileResourcePermissionSeeder
php artisan db:seed --class=ProfileDateStatisticsPermissionSeeder
```

| Seeder | Mục đích |
|---|---|
| `SystemSettingsSeeder` | Cài đặt mặc định (trạng thái chặn đăng nhập) |
| `SystemConfigurationPermissionSeeder` | Quyền trang cấu hình hệ thống |
| `ViewSupportProfilesPermissionSeeder` | Quyền xem hồ sơ hỗ trợ |
| `ResolveSupportPermissionSeeder` | Quyền xử lý hồ sơ hỗ trợ |
| `AwaitingApprovalProfileResourcePermissionSeeder` | Quyền resource hồ sơ chờ duyệt |
| `ProfileDateStatisticsPermissionSeeder` | Quyền thống kê hồ sơ theo ngày |

**Lệnh gộp lần đầu cài đặt:**

```bash
php artisan migrate
php artisan db:seed
php artisan db:seed --class=SystemSettingsSeeder
php artisan db:seed --class=SystemConfigurationPermissionSeeder
php artisan db:seed --class=ViewSupportProfilesPermissionSeeder
php artisan db:seed --class=ResolveSupportPermissionSeeder
php artisan db:seed --class=AwaitingApprovalProfileResourcePermissionSeeder
php artisan db:seed --class=ProfileDateStatisticsPermissionSeeder
```

### Seeder không khuyến nghị chạy mặc định

| Seeder | Ghi chú |
|---|---|
| `ShieldSeeder` | Sinh từ Filament Shield, có thể **trùng/conflict** với `PermissionSeeder`. Chỉ dùng khi regenerate shield và biết rõ mục đích. |

### Khi nào cần chạy lại seeder?

| Tình huống | Hành động |
|---|---|
| Cài mới (dev/production) | Chạy đủ seeder như trên |
| Deploy thường ngày | **Không** cần `db:seed` |
| Thêm permission/role mới | Chạy seeder cụ thể tương ứng |
| Sau `shield:generate` | Cập nhật seeder tương ứng, chạy lại seeder đó |

---

## Tạo Super Admin

### Cách 1 — Tạo user mới (khuyến nghị production)

```bash
php artisan make:super-admin
```

Hoặc truyền đủ tham số (không hỏi interactive):

```bash
php artisan make:super-admin \
  --username=superadmin \
  --email=super@example.com \
  --password='SecurePass123'
```

Yêu cầu:
- Chạy `PermissionSeeder` trước (qua `db:seed`) để role `super_admin` tồn tại
- Username & email chưa tồn tại
- Mật khẩu tối thiểu 8 ký tự

### Cách 2 — Nâng user hiện có lên super admin

```bash
php artisan user:assign-super-admin admin@example.com
```

Lệnh này gỡ toàn bộ role cũ và gán `super_admin`.

### Đăng nhập

- URL: `{APP_URL}/admin`
- Dùng **username** (không phải email)
- Role `super_admin` có toàn quyền, bao gồm cấu hình hệ thống và bypass chặn đăng nhập

---

## Artisan commands hữu ích

```bash
# Phân quyền
php artisan user:assign-super-admin {email}
php artisan user:assign-role {email} {role}
php artisan user:show-roles {email}

# Telegram test
php artisan telegram:test pending
php artisan telegram:test rejected
php artisan telegram:test cancelled

# Cron / schedule
php artisan profiles:check-pending
php artisan profile:remind-process
php artisan schedule:list

# Queue
php artisan queue:work
php artisan queue:failed
php artisan queue:retry all

# Filament Shield (khi thêm resource mới)
php artisan shield:generate --all
```

---

## Tài liệu bổ sung

| File | Nội dung |
|---|---|
| [PERMISSIONS_GUIDE.md](PERMISSIONS_GUIDE.md) | Hệ thống phân quyền & roles |
| [README_TELEGRAM_SYSTEM.md](README_TELEGRAM_SYSTEM.md) | Hệ thống thông báo Telegram |
| [TELEGRAM_NOTIFICATION_SETUP.md](TELEGRAM_NOTIFICATION_SETUP.md) | Cài đặt Telegram bot |
| [CRON_SETUP_GUIDE.md](CRON_SETUP_GUIDE.md) | Hướng dẫn cron chi tiết |
| [REMIND_PROCESS_CRON_JOB.md](REMIND_PROCESS_CRON_JOB.md) | Cron nhắc nhở xử lý hồ sơ |
| [DASHBOARD_CHARTS_PERMISSION.md](DASHBOARD_CHARTS_PERMISSION.md) | Quyền biểu đồ dashboard |

---

## License

MIT
