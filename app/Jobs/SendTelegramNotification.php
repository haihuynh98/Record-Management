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

class SendTelegramNotification implements ShouldQueue
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
            switch ($this->type) {
                case 'pending':
                    $telegramService->sendPendingProfileNotification($this->profile, $this->isPriority);
                    break;
                case 'rejected':
                    $telegramService->sendRejectedProfileNotification($this->profile);
                    break;
                case 'cancelled':
                    $telegramService->sendCancelledProfileNotification($this->profile);
                    break;
                default:
                    Log::warning('Unknown notification type', ['type' => $this->type]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram notification', [
                'profile_id' => $this->profile->id,
                'type' => $this->type,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
