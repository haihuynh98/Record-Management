<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class TestDatabaseNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:test-database {type=rejected}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test database notifications for Filament';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        
        $this->info("Testing database notification for type: {$type}");
        
        // Lấy user đầu tiên để test
        $user = User::first();
        if (!$user) {
            $this->error('No user found. Please create a user first.');
            return 1;
        }
        
        // Lấy profile đầu tiên để test
        $profile = Profile::first();
        if (!$profile) {
            $this->error('No profile found. Please create a profile first.');
            return 1;
        }
        
        try {
            $title = '';
            $body = '';
            
            switch ($type) {
                case 'rejected':
                    $title = 'Hồ sơ #' . $profile->code . ' đã bị từ chối';
                    $body = 'Lý do từ chối: Hồ sơ không đạt yêu cầu';
                    break;
                    
                case 'cancelled':
                    $title = 'Hồ sơ #' . $profile->code . ' đã bị hủy';
                    $body = 'Hồ sơ của bạn đã bị hủy bởi admin';
                    break;
                    
                case 'approved':
                    $title = 'Hồ sơ #' . $profile->code . ' đã được duyệt';
                    $body = 'Chúc mừng! Hồ sơ của bạn đã được phê duyệt';
                    break;
                    
                default:
                    $this->error('Invalid type. Use: rejected, cancelled, or approved');
                    return 1;
            }
            
            // Gửi notification trực tiếp
            $user->notify(new \App\Notifications\ProfileStatusNotification(
                $profile,
                $type,
                $title,
                $body
            ));
            
            $this->info("✅ Database notification sent successfully!");
            $this->info("User: {$user->username}");
            $this->info("Profile: #{$profile->code}");
            $this->info("Type: {$type}");
            $this->info("");
            $this->info("Check the notification in Filament admin panel!");
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
