<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemindProcessProfile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profile:remind-process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nhắc nhở xử lý các hồ sơ đang chờ xử lý';

    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu kiểm tra các hồ sơ cần nhắc nhở xử lý...');

        // Tìm các profile có trạng thái đang chờ xử lý (status = 0) và được tạo trong ngày hôm nay
        $pendingProfiles = Profile::with(['createdBy'])
            ->where('status', 0) // Trạng thái đang chờ xử lý
            ->whereDate('created_at', Carbon::today())
            ->get();

        $this->info("Tìm thấy {$pendingProfiles->count()} hồ sơ đang chờ xử lý trong ngày hôm nay.");

        $sentCount = 0;
        $this->info('Danh sách hồ sơ đang chờ xử lý: ' . implode(', ', $pendingProfiles->pluck('code')->toArray()));

        foreach ($pendingProfiles as $profile) {
            $this->info("Đang xử lý hồ sơ #{$profile->code}");
            try {
                if ($this->shouldSendReminder($profile)) {
                    $this->telegramService->sendRemindProcessNotification($profile);
                    $sentCount++;
                    $this->info("Đã gửi nhắc nhở cho hồ sơ #{$profile->code}");
                }
            } catch (\Exception $e) {
                Log::error('Lỗi khi gửi nhắc nhở xử lý hồ sơ', [
                    'profile_id' => $profile->id,
                    'profile_code' => $profile->code,
                    'error' => $e->getMessage()
                ]);
                $this->error("Lỗi khi gửi nhắc nhở cho hồ sơ #{$profile->code}: {$e->getMessage()}");
            }
        }

        $this->info("Hoàn thành! Đã gửi {$sentCount} thông báo nhắc nhở.");
        
        return Command::SUCCESS;
    }

    /**
     * Kiểm tra xem có nên gửi nhắc nhở cho profile này không
     */
    private function shouldSendReminder(Profile $profile): bool
    {
        $now = Carbon::now();
        
        // Case 1: Chưa từng gửi notification (last_notification_sent_at = null)
        if (is_null($profile->last_notification_sent_at)) {
            $this->info("Hồ sơ #{$profile->code} có last_notification_sent_at = null");
            $createdAt = Carbon::parse($profile->created_at);

            // Kiểm tra người tạo có phải là priority user không
            $isPriorityUser = $profile->createdBy && $profile->createdBy->is_priority;

            $minutesSinceCreated = $now->diffInMinutes($createdAt);
            $this->info("Hồ sơ #{$profile->code} đã được tạo cách đây {$minutesSinceCreated} phút.");

            if ($isPriorityUser) {
                // Nếu là priority user, kiểm tra đã qua 15 phút chưa
                return $minutesSinceCreated >= 15;
            } else {
                // Nếu không phải priority user, kiểm tra đã qua 2 giờ chưa
                return $now->diffInHours($createdAt) >= 2;
            }
        }

        // Case 2: Đã từng gửi notification (last_notification_sent_at có giá trị)
        $lastNotificationAt = Carbon::parse($profile->last_notification_sent_at);
        $minutesSinceLastNotification = $now->diffInMinutes($lastNotificationAt);
        $this->info("Hồ sơ #{$profile->code} đã gửi nhắc nhở lần cuối cách đây {$minutesSinceLastNotification} phút.");

        // Kiểm tra đã qua 15 phút kể từ lần gửi cuối cùng
        return $minutesSinceLastNotification >= 15;
    }
}
