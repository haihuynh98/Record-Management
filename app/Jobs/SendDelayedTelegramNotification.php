<?php

namespace App\Jobs;

use App\Models\Profile;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDelayedTelegramNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected $profile;
    protected $type;
    protected $isPriority;

    /**
     * Create a new job instance.
     */
    public function __construct(Profile $profile, string $type, bool $isPriority = false)
    {
        $this->profile = $profile;
        $this->type = $type;
        $this->isPriority = $isPriority;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        try {
            // Kiểm tra xem profile vẫn còn tồn tại và có trạng thái phù hợp không
            $profile = Profile::find($this->profile->id);
            
            if (!$profile) {
                Log::info('Profile not found, skipping delayed notification', [
                    'profile_id' => $this->profile->id,
                    'type' => $this->type
                ]);
                return;
            }

            // Kiểm tra trạng thái profile trước khi gửi thông báo
            // Chỉ gửi thông báo nếu profile vẫn đang ở trạng thái "Chờ" (5)
            if ($profile->status !== 5) {
                Log::info('Profile status changed, skipping delayed notification', [
                    'profile_id' => $profile->id,
                    'profile_code' => $profile->code,
                    'current_status' => $profile->status,
                    'expected_status' => 5,
                    'type' => $this->type
                ]);
                return;
            }

            switch ($this->type) {
                case 'pending_reminder':
                    $telegramService->sendRemindProcessNotification($profile);
                    break;
                case 'pending':
                    $telegramService->sendPendingProfileNotification($profile, $this->isPriority);
                    break;
                case 'rejected':
                    $telegramService->sendRejectedProfileNotification($profile);
                    break;
                case 'cancelled':
                    $telegramService->sendCancelledProfileNotification($profile);
                    break;
                default:
                    Log::warning('Unknown notification type', ['type' => $this->type]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send delayed Telegram notification', [
                'profile_id' => $this->profile->id,
                'type' => $this->type,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
