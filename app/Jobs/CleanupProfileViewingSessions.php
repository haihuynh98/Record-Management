<?php

namespace App\Jobs;

use App\Models\ProfileViewingSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupProfileViewingSessions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        // Xóa các session không hoạt động (quá 5 phút)
        $deletedCount = ProfileViewingSession::where('last_activity', '<', now()->subMinutes(5))
            ->delete();

        \Log::info("Cleaned up {$deletedCount} inactive profile viewing sessions");
    }
}
