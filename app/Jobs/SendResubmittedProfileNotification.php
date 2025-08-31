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

class SendResubmittedProfileNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle(): void
    {
        try {
            $telegramService = new TelegramService();
            $telegramService->sendResubmittedProfileNotification($this->profile);
        } catch (\Exception $e) {
            Log::error('Failed to send resubmitted profile notification via queue', [
                'profile_id' => $this->profile->id,
                'profile_code' => $this->profile->code,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendResubmittedProfileNotification job failed completely', [
            'profile_id' => $this->profile->id,
            'profile_code' => $this->profile->code,
            'error' => $exception->getMessage()
        ]);
    }
}
