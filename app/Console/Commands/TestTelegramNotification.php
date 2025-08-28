<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramNotification;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Console\Command;

class TestTelegramNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test {type=pending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram notification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        
        $this->info("Testing Telegram notification for type: {$type}");
        
        // Tạo test data
        $user = User::first();
        if (!$user) {
            $this->error('No user found. Please create a user first.');
            return 1;
        }
        
        $profile = Profile::first();
        if (!$profile) {
            $this->error('No profile found. Please create a profile first.');
            return 1;
        }
        
        try {
            switch ($type) {
                case 'pending':
                    SendTelegramNotification::dispatch($profile, 'pending', true);
                    $this->info('Priority pending notification dispatched');
                    break;
                case 'rejected':
                    SendTelegramNotification::dispatch($profile, 'rejected');
                    $this->info('Rejected notification dispatched');
                    break;
                case 'cancelled':
                    SendTelegramNotification::dispatch($profile, 'cancelled');
                    $this->info('Cancelled notification dispatched');
                    break;
                default:
                    $this->error('Invalid type. Use: pending, rejected, or cancelled');
                    return 1;
            }
            
            $this->info('Notification job dispatched successfully!');
            $this->info('Run "php artisan queue:work" to process the job.');
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
