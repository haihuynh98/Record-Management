<?php

namespace App\Console\Commands;

use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class HideLastMonthProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profiles:hide-last-month';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ẩn tất cả các hồ sơ được tạo trong tháng trước (từ ngày 1 đến cuối tháng)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu ẩn hồ sơ của tháng trước...');

        // Lấy tháng trước
        $lastMonth = Carbon::now()->subMonth();
        
        // Ngày đầu tháng trước
        $startOfLastMonth = $lastMonth->copy()->startOfMonth();
        
        // Ngày cuối tháng trước
        $endOfLastMonth = $lastMonth->copy()->endOfMonth();

        $this->info("Tìm hồ sơ từ {$startOfLastMonth->format('d/m/Y')} đến {$endOfLastMonth->format('d/m/Y')}");

        // Tìm và update các hồ sơ
        $profiles = Profile::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->where('hidden', false)
            ->get();

        $count = $profiles->count();

        if ($count === 0) {
            $this->info('Không có hồ sơ nào cần ẩn.');
            Log::info('[HideLastMonthProfiles] Không có hồ sơ nào cần ẩn trong tháng trước.');
            return Command::SUCCESS;
        }

        $this->info("Tìm thấy {$count} hồ sơ cần ẩn.");

        // Update hidden = true
        $updated = Profile::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->where('hidden', false)
            ->update(['hidden' => true]);

        $this->info("Đã ẩn thành công {$updated} hồ sơ.");
        
        Log::info("[HideLastMonthProfiles] Đã ẩn {$updated} hồ sơ được tạo từ {$startOfLastMonth->format('d/m/Y')} đến {$endOfLastMonth->format('d/m/Y')}");

        return Command::SUCCESS;
    }
}
