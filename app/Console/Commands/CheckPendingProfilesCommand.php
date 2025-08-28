<?php

namespace App\Console\Commands;

use App\Jobs\CheckPendingProfiles;
use Illuminate\Console\Command;

class CheckPendingProfilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profiles:check-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra và gửi thông báo cho hồ sơ chờ xử lý';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu kiểm tra hồ sơ chờ xử lý...');
        
        try {
            CheckPendingProfiles::dispatch();
            $this->info('Job kiểm tra hồ sơ chờ xử lý đã được dispatch thành công!');
        } catch (\Exception $e) {
            $this->error('Lỗi khi dispatch job: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
