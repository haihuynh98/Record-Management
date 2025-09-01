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

class SendSupportRequestNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $profile;
    protected $supportMessage;
    protected $requestedBy;

    /**
     * Create a new job instance.
     */
    public function __construct(Profile $profile, string $supportMessage, $requestedBy)
    {
        $this->profile = $profile;
        $this->supportMessage = $supportMessage;
        $this->requestedBy = $requestedBy;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        try {
            $telegramService->sendSupportRequestNotification(
                $this->profile, 
                $this->supportMessage, 
                $this->requestedBy
            );
            
            Log::info('Support request notification sent successfully', [
                'profile_id' => $this->profile->id,
                'profile_code' => $this->profile->code,
                'requested_by' => $this->requestedBy->username ?? 'Unknown',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send support request notification', [
                'profile_id' => $this->profile->id,
                'profile_code' => $this->profile->code,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}
