# Dashboard Charts Permission

## Tổng quan
Permission `view_dashboard_charts` được tạo để kiểm soát quyền truy cập vào các biểu đồ và thống kê trên dashboard.

## Permission Details
- **Tên permission**: `view_dashboard_charts`
- **Mô tả**: Cho phép người dùng xem các widget thống kê và biểu đồ trên dashboard
- **Widgets được bảo vệ**:
  - `StatsOverview`: Hiển thị 4 số liệu thống kê chính
  - `ProfileChart`: Biểu đồ xu hướng hồ sơ theo tuần

## Roles có quyền truy cập
- `super_admin`: Có tất cả quyền
- `admin`: Có quyền xem dashboard charts

## Cách sử dụng

### 1. Kiểm tra permission trong code
```php
if (auth()->user()->can('view_dashboard_charts')) {
    // Hiển thị widgets
}
```

### 2. Trong Blade templates
```blade
@if(auth()->user()->can('view_dashboard_charts'))
    {{ $this->widgets['stats-overview'] }}
    {{ $this->widgets['profile-chart'] }}
@endif
```

### 3. Trong Widget classes
```php
public static function canView(): bool
{
    return auth()->user()->can('view_dashboard_charts');
}
```

## Thêm permission cho role mới
```php
$role = Role::where('name', 'your_role_name')->first();
$permission = Permission::where('name', 'view_dashboard_charts')->first();
$role->givePermissionTo($permission);
```

## Gỡ permission
```php
$role = Role::where('name', 'your_role_name')->first();
$role->revokePermissionTo('view_dashboard_charts');
```
