<?php

namespace App\Services;

use Telegram\Bot\Api;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new Api(config('telegram.bots.mybot.token'));
    }

    /**
     * Gửi tin nhắn đến chat/channel
     */
    public function sendMessage($chatId, $message, $parseMode = 'HTML')
    {
        try {
            $response = $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
            ]);

            Log::info('Telegram message sent successfully', [
                'chat_id' => $chatId,
                'message' => $message,
                'response' => $response
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message', [
                'chat_id' => $chatId,
                'message' => $message,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Gửi thông báo hồ sơ chờ xử lý
     */
    public function sendPendingProfileNotification($profile, $isPriority = false)
    {
        $priorityText = $isPriority ? '⭐ <b>ƯU TIÊN</b> ⭐' : '';
        $timeText = $isPriority ? '15 phút' : '2 giờ';
        $characterId = $profile->character_id ?: 'Chưa có';
        
        $message = "
🚨 <b>THÔNG BÁO HỒ SƠ CHỜ XỬ LÝ</b> 🚨

{$priorityText}

📋 <b>Mã hồ sơ:</b> #{$profile->code}
👤 <b>ID nhân vật:</b> {$characterId}
👨‍💼 <b>Người tạo:</b> {$profile->createdBy->username}
⏰ <b>Thời gian tạo:</b> {$profile->created_at->format('d/m/Y H:i:s')}
⏳ <b>Đã chờ:</b> {$timeText}

⚠️ <b>Hồ sơ này đã chờ xử lý quá {$timeText}!</b>

🔗 <b>Link xử lý:</b> " . config('app.url') . "/admin/profiles
        ";

        return $this->sendMessage(config('services.telegram.chat_id'), $message);
    }

    /**
     * Gửi thông báo hồ sơ bị từ chối
     */
    public function sendRejectedProfileNotification($profile)
    {
        $characterId = $profile->character_id ?: 'Chưa có';
        $rejectionReason = $profile->rejection_reason ?: 'Không có lý do';
        $approvedAt = $profile->approved_at ? \Carbon\Carbon::parse($profile->approved_at)->format('d/m/Y H:i:s') : 'Chưa có';
        
        $message = "
❌ <b>THÔNG BÁO HỒ SƠ BỊ TỪ CHỐI</b> ❌

📋 <b>Mã hồ sơ:</b> #{$profile->code}
👤 <b>ID nhân vật:</b> {$characterId}
👨‍💼 <b>Người tạo:</b> {$profile->createdBy->username}
👨‍⚖️ <b>Người duyệt:</b> {$profile->approvedBy->username}
⏰ <b>Thời gian từ chối:</b> {$approvedAt}

📝 <b>Lý do từ chối:</b>
{$rejectionReason}

🔗 <b>Link xem chi tiết:</b> " . config('app.url') . "/admin/profiles
        ";

        return $this->sendMessage(config('services.telegram.chat_id'), $message);
    }

    /**
     * Gửi thông báo hồ sơ bị hủy
     */
    public function sendCancelledProfileNotification($profile)
    {
        $characterId = $profile->character_id ?: 'Chưa có';
        $approvedAt = $profile->approved_at ? \Carbon\Carbon::parse($profile->approved_at)->format('d/m/Y H:i:s') : 'Chưa có';
        
        $message = "
🚫 <b>THÔNG BÁO HỒ SƠ BỊ HỦY</b> 🚫

📋 <b>Mã hồ sơ:</b> #{$profile->code}
👤 <b>ID nhân vật:</b> {$characterId}
👨‍💼 <b>Người tạo:</b> {$profile->createdBy->username}
👨‍⚖️ <b>Người hủy:</b> {$profile->approvedBy->username}
⏰ <b>Thời gian hủy:</b> {$approvedAt}

🔗 <b>Link xem chi tiết:</b> " . config('app.url') . "/admin/profiles
        ";

        return $this->sendMessage(config('services.telegram.chat_id'), $message);
    }
}
