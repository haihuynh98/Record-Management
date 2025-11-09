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

class SendNewProfileReminderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected $profile;

    /**
     * Create a new job instance.
     */
    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        try {
            // Kiểm tra xem profile vẫn còn tồn tại không
            $profile = Profile::find($this->profile->id);
            
            if (!$profile) {
                return;
            }

            // Chỉ gửi thông báo nếu profile vẫn ở trạng thái "Chờ xử lý" (0) hoặc "Nộp lại" (6)
            if (!in_array($profile->status, [0, 6])) {
                return;
            }

            // Gửi thông báo nhắc nhở
            $telegramService->sendNewProfileReminderNotification($profile);
            
            
        } catch (\Exception $e) {
            Log::error('Failed to send new profile reminder notification', [
                'profile_id' => $this->profile->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendNewProfileReminderNotification job failed completely', [
            'profile_id' => $this->profile->id,
            'error' => $exception->getMessage()
        ]);
    }
}

