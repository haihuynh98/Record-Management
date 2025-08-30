<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Chạy kiểm tra hồ sơ chờ xử lý mỗi 5 phút
        $schedule->command('profiles:check-pending')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Chạy nhắc nhở xử lý hồ sơ mỗi 5 phút
        $schedule->command('profile:remind-process')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
