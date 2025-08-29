<?php

namespace App\Models;

use App\Events\ProfileStatusChanged;
use App\Services\TelegramService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'profiles';
    protected $fillable = [
        'code',
        'character_id',
        'rejection_reason',
        'created_by',
        'approved_by',
        'approved_at',
        'status',
        'status_old',
        'viewing_user_id',
        'viewing_started_at',
        'viewing_session_id',
    ];

    protected $casts = [
        'status' => 'integer',
        'status_old' => 'integer',
        'viewing_started_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::updating(function ($profile) {
            // Kiểm tra nếu trạng thái đang thay đổi
            if ($profile->isDirty('status')) {
                // Lưu trạng thái cũ vào status_old
                $profile->status_old = $profile->getOriginal('status');
            }
        });

        static::updated(function ($profile) {
            // Kiểm tra nếu trạng thái đã thay đổi
            if ($profile->wasChanged('status')) {
                $oldStatus = $profile->getOriginal('status');
                $newStatus = $profile->status;
                
                // Dispatch event
                event(new ProfileStatusChanged($profile, $oldStatus, $newStatus));
                
                // Gửi thông báo Telegram khi hồ sơ bị hủy
                if ($newStatus === 3) {
                    try {
                        $telegramService = app(TelegramService::class);
                        $telegramService->sendCancelledProfileNotification($profile);
                    } catch (\Exception $e) {
                        Log::error('Failed to send Telegram notification for cancelled profile', [
                            'profile_id' => $profile->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        });
    }

    public function approvedBy(){
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function viewingUser(){
        return $this->belongsTo(User::class, 'viewing_user_id');
    }

    public function scopeUnApprove($q)
    {
        return $q->whereNull('approved_at');
    }

    /**
     * Kiểm tra xem hồ sơ có đang được xem bởi ai đó không
     */
    public function isBeingViewed(): bool
    {
        return !is_null($this->viewing_user_id) && !is_null($this->viewing_started_at);
    }

    /**
     * Kiểm tra xem hồ sơ có đang được xem bởi user cụ thể không
     */
    public function isBeingViewedBy(int $userId): bool
    {
        return $this->viewing_user_id === $userId && !is_null($this->viewing_started_at);
    }

    /**
     * Thiết lập session xem hồ sơ
     */
    public function setViewingSession(int $userId, string $sessionId): bool
    {
        $result = $this->update([
            'viewing_user_id' => $userId,
            'viewing_started_at' => now(),
            'viewing_session_id' => $sessionId,
        ]);
        
        // Refresh model để cập nhật dữ liệu
        $this->refresh();
        
        return $result;
    }

    /**
     * Xóa session xem hồ sơ
     */
    public function clearViewingSession(): bool
    {
        return $this->update([
            'viewing_user_id' => null,
            'viewing_started_at' => null,
            'viewing_session_id' => null,
        ]);
    }

    /**
     * Xóa session xem hồ sơ nếu thuộc về user cụ thể
     */
    public function clearViewingSessionIfOwnedBy(int $userId): bool
    {
        if ($this->viewing_user_id === $userId) {
            return $this->clearViewingSession();
        }
        return false;
    }

    /**
     * Xử lý session khi mở modal xem hồ sơ
     */
    public function handleViewSession(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện hành động này.'];
        }
        

        
        // Nếu hồ sơ đang được xem bởi người khác
        if ($this->isBeingViewed() && !$this->isBeingViewedBy($user->id)) {
            $viewingUser = $this->viewingUser;
            $message = $viewingUser ? "Hồ sơ này đang được xử lý bởi {$viewingUser->username}" : "Hồ sơ này đang được xử lý bởi người khác";
            return ['success' => false, 'message' => $message];
        }
        
        // Thiết lập session xem hồ sơ
        $sessionId = \Illuminate\Support\Str::uuid()->toString();
        $result = $this->setViewingSession($user->id, $sessionId);
        

        
        if ($result) {
            return ['success' => true, 'message' => 'Session đã được thiết lập thành công.'];
        } else {
            return ['success' => false, 'message' => 'Không thể thiết lập session.'];
        }
    }


}
