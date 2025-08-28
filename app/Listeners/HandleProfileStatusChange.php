<?php

namespace App\Listeners;

use App\Events\ProfileStatusChanged;
use App\Jobs\SendTelegramNotification;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleProfileStatusChange implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;
    public $timeout = 30;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProfileStatusChanged $event): void
    {
        try {
            $profile = $event->profile;
            $oldStatus = $event->oldStatus;
            $newStatus = $event->newStatus;

            // Gửi thông báo Telegram khi hồ sơ bị từ chối (từ chờ xử lý -> từ chối)
            if ($oldStatus === 0 && $newStatus === 2) {
                SendTelegramNotification::dispatch($profile, 'rejected');
                
                // Gửi push notification cho người tạo hồ sơ
                $notification = Notification::make()
                    ->title('Hồ sơ #' . $profile->code . ' đã bị từ chối')
                    ->body('Lý do từ chối: ' . ($profile->rejection_reason ?: 'Không có lý do'))
                    ->danger()
                    ->icon('heroicon-o-x-circle')
                    ->actions([
                        Action::make('view_profile')
                            ->label('Xem hồ sơ')
                            ->url(config('app.url') . '/admin/profiles')
                            ->button()
                            ->markAsRead(),
                        Action::make('mark_as_read')
                            ->label('Đánh dấu đã đọc')
                            ->button()
                            ->markAsRead(),
                    ]);

                $profile->createdBy->notify($notification->toDatabase());

                Log::info('Profile rejected notification sent', [
                    'profile_id' => $profile->id,
                    'profile_code' => $profile->code
                ]);
            }

            // Gửi thông báo Telegram khi hồ sơ bị hủy (từ từ chối -> hủy)
            if ($oldStatus === 2 && $newStatus === 3) {
                SendTelegramNotification::dispatch($profile, 'cancelled');
                
                // Gửi push notification cho người tạo hồ sơ
                $notification = Notification::make()
                    ->title('Hồ sơ #' . $profile->code . ' đã bị hủy')
                    ->body('Hồ sơ của bạn đã bị hủy bởi ' . $profile->approvedBy->username)
                    ->warning()
                    ->icon('heroicon-o-exclamation-triangle')
                    ->actions([
                        Action::make('view_profile')
                            ->label('Xem hồ sơ')
                            ->url(config('app.url') . '/admin/profiles')
                            ->button()
                            ->markAsRead(),
                        Action::make('mark_as_read')
                            ->label('Đánh dấu đã đọc')
                            ->button()
                            ->markAsRead(),
                    ]);

                $profile->createdBy->notify($notification->toDatabase());

                Log::info('Profile cancelled notification sent', [
                    'profile_id' => $profile->id,
                    'profile_code' => $profile->code
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to handle profile status change', [
                'profile_id' => $event->profile->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
