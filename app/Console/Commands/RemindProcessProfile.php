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
        
        // Optional: Enable debug mode
        // $this->info('Debug: Default timezone = ' . date_default_timezone_get());
        // $this->info('Debug: App timezone = ' . config('app.timezone'));
        // $this->info('Debug: Current time = ' . Carbon::now()->toDateTimeString());

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
        
        $this->info("Debug thời gian: Now = {$now->toDateTimeString()}");
        
        // Case 1: Chưa từng gửi notification (last_notification_sent_at = null)
        if (is_null($profile->last_notification_sent_at)) {
            $this->info("Hồ sơ #{$profile->code} có last_notification_sent_at = null");
            $createdAt = Carbon::parse($profile->created_at);
            
            $this->info("Debug: created_at = {$createdAt->toDateTimeString()}");

            // Kiểm tra người tạo có phải là priority user không
            $isPriorityUser = $profile->createdBy && $profile->createdBy->is_priority;
            $this->info("Debug: isPriorityUser = " . ($isPriorityUser ? 'true' : 'false'));

            // Sử dụng diffInRealMinutes để tránh vấn đề timezone
            $minutesSinceCreated = $createdAt->diffInRealMinutes($now);
            $this->info("Hồ sơ #{$profile->code} đã được tạo cách đây {$minutesSinceCreated} phút.");
            
            if ($isPriorityUser) {
                // Nếu là priority user, kiểm tra đã qua 15 phút chưa
                $shouldSend = $minutesSinceCreated >= 15;
                $this->info("Priority user: {$minutesSinceCreated} >= 15? " . ($shouldSend ? 'YES' : 'NO'));
                return $shouldSend;
            } else {
                // Nếu không phải priority user, kiểm tra đã qua 2 giờ chưa
                $hoursSinceCreated = $createdAt->diffInRealHours($now);
                $shouldSend = $hoursSinceCreated >= 2;
                $this->info("Normal user: {$hoursSinceCreated} hours >= 2? " . ($shouldSend ? 'YES' : 'NO'));
                return $shouldSend;
            }
        }

        // Case 2: Đã từng gửi notification (last_notification_sent_at có giá trị)
        $lastNotificationAt = Carbon::parse($profile->last_notification_sent_at);
        $this->info("Debug: last_notification_sent_at = {$lastNotificationAt->toDateTimeString()}");
        
        // Sử dụng diffInRealMinutes để tránh vấn đề timezone
        $minutesSinceLastNotification = $lastNotificationAt->diffInRealMinutes($now);
        $this->info("Hồ sơ #{$profile->code} đã gửi nhắc nhở lần cuối cách đây {$minutesSinceLastNotification} phút.");

        // Kiểm tra đã qua 15 phút kể từ lần gửi cuối cùng
        $shouldSend = $minutesSinceLastNotification >= 15;
        $this->info("Last notification: {$minutesSinceLastNotification} >= 15? " . ($shouldSend ? 'YES' : 'NO'));
        return $shouldSend;
    }
}
