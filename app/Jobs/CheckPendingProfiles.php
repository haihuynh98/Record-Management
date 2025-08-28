<?php

namespace App\Jobs;

use App\Models\Profile;
use App\Jobs\SendTelegramNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckPendingProfiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $now = Carbon::now();
            
            // Kiểm tra hồ sơ ưu tiên chờ xử lý quá 15 phút
            $priorityProfiles = Profile::where('status', 0)
                ->whereHas('createdBy', function ($query) {
                    $query->where('is_priority', true);
                })
                ->where('created_at', '<=', $now->copy()->subMinutes(15))
                ->with(['createdBy'])
                ->get();

            foreach ($priorityProfiles as $profile) {
                SendTelegramNotification::dispatch($profile, 'pending', true);
                Log::info('Priority profile pending notification sent', [
                    'profile_id' => $profile->id,
                    'profile_code' => $profile->code
                ]);
            }

            // Kiểm tra hồ sơ thường chờ xử lý quá 2 giờ
            $normalProfiles = Profile::where('status', 0)
                ->whereHas('createdBy', function ($query) {
                    $query->where('is_priority', false);
                })
                ->where('created_at', '<=', $now->copy()->subHours(2))
                ->with(['createdBy'])
                ->get();

            foreach ($normalProfiles as $profile) {
                SendTelegramNotification::dispatch($profile, 'pending', false);
                Log::info('Normal profile pending notification sent', [
                    'profile_id' => $profile->id,
                    'profile_code' => $profile->code
                ]);
            }

            Log::info('CheckPendingProfiles job completed', [
                'priority_count' => $priorityProfiles->count(),
                'normal_count' => $normalProfiles->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check pending profiles', [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
