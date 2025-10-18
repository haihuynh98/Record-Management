<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

class SystemConfiguration extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'Cấu hình hệ thống';
    
    protected static ?string $title = 'Cấu hình hệ thống';
    
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.system-configuration';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        
        // Super admin luôn có quyền
        if ($user && $user->roles->contains('name', 'super_admin')) {
            return true;
        }
        
        // Kiểm tra permission page_SystemConfiguration hoặc manage_system_configuration
        return Gate::allows('page_SystemConfiguration') || Gate::allows('manage_system_configuration');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle_login_block')
                ->label(fn() => SystemSetting::isLoginBlocked() ? 'Mở khóa đăng nhập' : 'Chặn đăng nhập')
                ->icon(fn() => SystemSetting::isLoginBlocked() ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                ->color(fn() => SystemSetting::isLoginBlocked() ? 'success' : 'danger')
                ->requiresConfirmation()
                ->modalHeading(fn() => SystemSetting::isLoginBlocked() ? 'Mở khóa đăng nhập' : 'Chặn đăng nhập')
                ->modalDescription(fn() => SystemSetting::isLoginBlocked() 
                    ? 'Bạn có chắc chắn muốn mở khóa đăng nhập cho tất cả người dùng?'
                    : 'Bạn có chắc chắn muốn chặn đăng nhập cho tất cả người dùng? Điều này sẽ đăng xuất tất cả user hiện tại.')
                ->modalSubmitActionLabel(fn() => SystemSetting::isLoginBlocked() ? 'Mở khóa' : 'Chặn đăng nhập')
                ->modalCancelActionLabel('Hủy bỏ')
                ->action('toggleLoginBlock')
        ];
    }

    public function toggleLoginBlock()
    {
        try {
            DB::beginTransaction();
            
            $isCurrentlyBlocked = SystemSetting::isLoginBlocked();
            $newStatus = !$isCurrentlyBlocked;
            
            SystemSetting::setLoginBlocked($newStatus);
            
            // Xóa tất cả session hiện tại (trừ session của user hiện tại nếu họ có quyền)
            if ($newStatus) {
                $this->clearAllUserSessions();
            }
            
            DB::commit();
            
            Notification::make()
                ->title($newStatus ? '🔒 Đã chặn đăng nhập' : '🔓 Đã mở khóa đăng nhập')
                ->body($newStatus 
                    ? 'Hệ thống đã được chặn đăng nhập. Tất cả người dùng hiện tại đã bị đăng xuất và không thể đăng nhập mới (trừ admin có quyền).'
                    : 'Hệ thống đã được mở khóa đăng nhập. Tất cả người dùng có thể đăng nhập bình thường.')
                ->success()
                ->duration(5000)
                ->send();
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->title('❌ Lỗi thay đổi trạng thái')
                ->body('Có lỗi xảy ra khi thay đổi trạng thái chặn đăng nhập: ' . $e->getMessage())
                ->danger()
                ->duration(8000)
                ->send();
        }
    }

    private function clearAllUserSessions()
    {
        // Xóa tất cả session trong database
        DB::table('sessions')->delete();
        
        // Xóa tất cả cache session
        if (config('session.driver') === 'cache') {
            cache()->flush();
        }
        
        // Xóa tất cả file session
        if (config('session.driver') === 'file') {
            $sessionPath = storage_path('framework/sessions');
            if (is_dir($sessionPath)) {
                $files = glob($sessionPath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }

    public function getLoginBlockStatus(): bool
    {
        return SystemSetting::isLoginBlocked();
    }

    public function getLoginBlockStatusText(): string
    {
        return SystemSetting::isLoginBlocked() ? 'Đang chặn' : 'Đang mở';
    }

    public function getLoginBlockStatusColor(): string
    {
        return SystemSetting::isLoginBlocked() ? 'danger' : 'success';
    }
}