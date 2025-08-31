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

📋 <b>Mã hồ sơ:</b> <code>#{$profile->code}</code>
👤 <b>ID nhân vật:</b> {$characterId}
👨‍💼 <b>Người tạo:</b> {$profile->createdBy->username}
⏰ <b>Thời gian tạo:</b> {$profile->created_at->format('d/m/Y H:i:s')}

        ";

        $result = $this->sendMessage(config('services.telegram.chat_id'), $message);
        
        // Cập nhật thời gian gửi notification cuối cùng nếu gửi thành công
        if ($result) {
            $profile->update(['last_notification_sent_at' => now()]);
        }
        
        return $result;
    }

    /**
     * Gửi thông báo hồ sơ bị từ chối
     */
    public function sendRejectedProfileNotification($profile)
    {
        $characterId = $profile->character_id ?: 'Chưa có';
        $rejectionReason = $profile->rejection_reason ?: 'Không có lý do';
        $approvedAt = $profile->approved_at ? \Carbon\Carbon::parse($profile->approved_at)->format('d/m/Y H:i:s') : 'Chưa có';
        $rejectedBy = $profile->approvedBy ? $profile->approvedBy->username : 'Hệ thống';
        $createdBy = $profile->createdBy ? $profile->createdBy->username : 'Không xác định';
        
        $message = "
❌ <b>THÔNG BÁO HỒ SƠ BỊ TỪ CHỐI</b> ❌

📋 <b>Mã hồ sơ:</b> <code>#{$profile->code}</code>
👤 <b>ID nhân vật:</b> {$characterId}
👨‍💼 <b>Người tạo:</b> {$createdBy}
👨‍⚖️ <b>Người duyệt:</b> {$rejectedBy}
⏰ <b>Thời gian từ chối:</b> {$approvedAt}

📝 <b>Lý do từ chối:</b>
{$rejectionReason}


        ";

        $result = $this->sendMessage(config('services.telegram.chat_id'), $message);
        
        // Cập nhật thời gian gửi notification cuối cùng nếu gửi thành công
        if ($result) {
            $profile->update(['last_notification_sent_at' => now()]);
        }
        
        return $result;
    }

    /**
     * Gửi thông báo hồ sơ bị hủy
     */
    public function sendCancelledProfileNotification($profile)
    {
        // Kiểm tra xem đã gửi thông báo hủy trong vòng 5 phút qua chưa để tránh spam
        if ($profile->last_notification_sent_at && 
            $profile->last_notification_sent_at->diffInMinutes(now()) < 5) {
            Log::info('Skipped duplicate cancelled notification', [
                'profile_id' => $profile->id,
                'profile_code' => $profile->code,
                'last_sent' => $profile->last_notification_sent_at
            ]);
            return false;
        }

        $characterId = $profile->character_id ?: 'Chưa có';
        $approvedAt = $profile->approved_at ? \Carbon\Carbon::parse($profile->approved_at)->format('d/m/Y H:i:s') : 'Chưa có';
        $cancelledBy = $profile->approvedBy ? $profile->approvedBy->username : 'Hệ thống';
        $createdBy = $profile->createdBy ? $profile->createdBy->username : 'Không xác định';
        
        $message = "
🚫 <b>THÔNG BÁO HỒ SƠ BỊ HỦY</b> 🚫

📋 <b>Mã hồ sơ:</b> <code>#{$profile->code}</code>
👤 <b>ID nhân vật:</b> {$characterId}
👨‍💼 <b>Người tạo:</b> {$createdBy}
👨‍⚖️ <b>Người hủy:</b> {$cancelledBy}
⏰ <b>Thời gian hủy:</b> {$approvedAt}


        ";

        $result = $this->sendMessage(config('services.telegram.chat_id'), $message);
        
        // Cập nhật thời gian gửi notification cuối cùng nếu gửi thành công
        if ($result) {
            $profile->update(['last_notification_sent_at' => now()]);
        }
        
        return $result;
    }

    /**
     * Gửi thông báo khi có hồ sơ mới được tạo
     */
    public function sendNewProfileCreatedNotification($profile)
    {
        $characterId = $profile->character_id ?: 'Chưa có';
        $createdBy = $profile->createdBy ? $profile->createdBy->username : 'Không xác định';
        
        $message = "
🆕 <b>THÔNG BÁO HỒ SƠ MỚI ĐƯỢC TẠO</b> 🆕

📋 <b>Mã hồ sơ:</b> <code>#{$profile->code}</code>
👤 <b>ID nhân vật:</b> {$characterId}
👨‍💼 <b>Người tạo:</b> {$createdBy}
📊 <b>Trạng thái:</b> Chờ xử lý

🔍 <i>Hồ sơ mới đã được tạo và đang chờ xử lý!</i>

        ";

        $result = $this->sendMessage(config('services.telegram.chat_id'), $message);
        
        return $result;
    }

    /**
     * Gửi thông báo khi hồ sơ được nộp lại (từ chối -> chờ xử lý)
     */
    public function sendResubmittedProfileNotification($profile)
    {
        $characterId = $profile->character_id ?: 'Chưa có';
        $createdBy = $profile->createdBy ? $profile->createdBy->username : 'Không xác định';
        
        $message = "
🔄 <b>THÔNG BÁO HỒ SƠ NỘP LẠI</b> 🔄

📋 <b>Mã hồ sơ:</b> <code>#{$profile->code}</code>
👤 <b>ID nhân vật:</b> {$characterId}
👨‍💼 <b>Người nộp:</b> {$createdBy}
📊 <b>Trạng thái:</b> Chờ xử lý

🔁 <i>Hồ sơ đã được nộp lại sau khi bị từ chối!</i>

        ";

        $result = $this->sendMessage(config('services.telegram.chat_id'), $message);
        
        return $result;
    }

    /**
     * Gửi thông báo nhắc nhở xử lý hồ sơ
     */
    public function sendRemindProcessNotification($profile)
    {
        $characterId = $profile->character_id ?: 'Chưa có';
        $createdBy = $profile->createdBy ? $profile->createdBy->username : 'Không xác định';
        
        // Tính thời gian đã trôi qua
        $createdAt = \Carbon\Carbon::parse($profile->created_at);
        $now = \Carbon\Carbon::now();
        $timeElapsed = $createdAt->diffForHumans($now, true);
        
        // Kiểm tra xem đã gửi notification bao nhiêu lần
        $isFirstReminder = is_null($profile->last_notification_sent_at);
        $reminderText = $isFirstReminder ? 'LẦN ĐẦU' : 'NHẮC LẠI';
        
        $message = "
🔔 <b>NHẮC NHỞ XỬ LÝ HỒ SƠ - {$reminderText}</b> 🔔


📋 <b>Mã hồ sơ:</b> <code>#{$profile->code}</code>
👤 <b>ID nhân vật:</b> {$characterId}
👨‍💼 <b>Người tạo:</b> {$createdBy}
⏰ <b>Thời gian tạo:</b> {$profile->created_at->format('d/m/Y H:i:s')}

💡 <i>Hồ sơ này đang chờ xử lý, vui lòng kiểm tra và xử lý sớm nhất có thể!</i>

        ";

        $result = $this->sendMessage(config('services.telegram.chat_id'), $message);
        
        // Cập nhật thời gian gửi notification cuối cùng nếu gửi thành công
        if ($result) {
            $profile->update(['last_notification_sent_at' => now()]);
        }
        
        return $result;
    }
}
