<?php

namespace App\Console\Commands;

use App\Models\Profile;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupProfileViewingSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profiles:cleanup-sessions {--minutes=30 : Số phút để xem xét session cũ}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup các session xem hồ sơ cũ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = $this->option('minutes');
        $cutoffTime = Carbon::now()->subMinutes($minutes);

        $this->info("Đang cleanup các session xem hồ sơ cũ hơn {$minutes} phút...");

        $profiles = Profile::whereNotNull('viewing_started_at')
            ->where('viewing_started_at', '<', $cutoffTime)
            ->get();

        $count = 0;
        foreach ($profiles as $profile) {
            $profile->clearViewingSession();
            $count++;
            $this->line("Đã xóa session cho hồ sơ #{$profile->code}");
        }

        $this->info("Hoàn thành! Đã xóa {$count} session cũ.");
    }
}
