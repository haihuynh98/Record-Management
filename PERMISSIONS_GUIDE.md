# Hướng dẫn Hệ thống Phân quyền

## Tổng quan

Hệ thống phân quyền được xây dựng theo chuẩn Filament Shield với các permission cụ thể cho từng chức năng.

## Các Permission được tạo

### Profile Resource
- `view_profile` - Xem hồ sơ
- `view_any_profile` - Xem danh sách hồ sơ
- `create_profile` - Tạo hồ sơ mới
- `update_profile` - Cập nhật hồ sơ
- `delete_profile` - Xóa hồ sơ
- `delete_any_profile` - Xóa nhiều hồ sơ
- `approve_profile` - **Duyệt hồ sơ** (Permission đặc biệt)
- `reject_profile` - **Từ chối hồ sơ** (Permission đặc biệt)
- `resubmit_profile` - **Nộp lại hồ sơ** (Permission đặc biệt)

### User Resource
- `view_user` - Xem người dùng
- `view_any_user` - Xem danh sách người dùng
- `create_user` - Tạo người dùng mới
- `update_user` - Cập nhật người dùng
- `delete_user` - Xóa người dùng
- `delete_any_user` - Xóa nhiều người dùng

### Role Resource
- `view_role` - Xem vai trò
- `view_any_role` - Xem danh sách vai trò
- `create_role` - Tạo vai trò mới
- `update_role` - Cập nhật vai trò
- `delete_role` - Xóa vai trò
- `delete_any_role` - Xóa nhiều vai trò

## Các Role và Permission được gán

### Super Admin
- Có tất cả permissions
- Quyền cao nhất trong hệ thống

### Admin
- Tất cả permissions của Profile, User, Role
- **Không có** permission `resubmit_profile`

### Approver (Người phê duyệt)
- `view_profile`
- `view_any_profile`
- `approve_profile`
- `reject_profile`

### Creator (Người tạo)
- `view_profile`
- `view_any_profile`
- `create_profile`
- `update_profile`
- `resubmit_profile`

## Cách hoạt động của các nút hành động

### Nút "Duyệt"
- **Hiển thị khi**: User có permission `approve_profile` VÀ hồ sơ ở trạng thái "Chờ duyệt"
- **Chức năng**: Chuyển hồ sơ từ trạng thái "Chờ duyệt" (0) thành "Đã duyệt" (1)

### Nút "Từ chối"
- **Hiển thị khi**: User có permission `reject_profile` VÀ hồ sơ ở trạng thái "Chờ duyệt"
- **Chức năng**: Chuyển hồ sơ từ trạng thái "Chờ duyệt" (0) thành "Từ chối" (2) và yêu cầu nhập lý do

### Nút "Nộp lại"
- **Hiển thị khi**: User có permission `resubmit_profile` VÀ hồ sơ ở trạng thái "Từ chối" VÀ user là người tạo hồ sơ
- **Chức năng**: Chuyển hồ sơ từ trạng thái "Từ chối" (2) thành "Chờ duyệt" (0) và xóa lý do từ chối

## Cách thêm permission mới

1. Thêm permission vào method `getPermissionPrefixes()` trong Resource
2. Tạo method tương ứng trong Policy
3. Chạy lệnh `php artisan shield:generate --resource=ResourceName`
4. Cập nhật seeder nếu cần
5. Chạy `php artisan db:seed --class=PermissionSeeder`

## Cách kiểm tra permission trong code

```php
// Kiểm tra permission cơ bản
if ($user->can('create', Profile::class)) {
    // Có quyền tạo hồ sơ
}

// Kiểm tra permission đặc biệt
if ($user->can('approve', $profile)) {
    // Có quyền duyệt hồ sơ cụ thể
}

// Kiểm tra permission trong Filament
->visible(fn () => auth()->user()?->can('create', Profile::class))
```

## Lưu ý quan trọng

1. **Policy được ưu tiên**: Tất cả permission đều được kiểm tra thông qua Policy
2. **Role vẫn được sử dụng**: Để đơn giản hóa việc quản lý, các role vẫn được sử dụng để gán permission
3. **Permission đặc biệt**: 3 permission `approve`, `reject`, `resubmit` có logic phức tạp hơn trong Policy
4. **Bảo mật**: Tất cả action đều được kiểm tra permission trước khi thực hiện
