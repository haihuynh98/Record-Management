<?php

namespace App\Models;

use App\Events\ProfileStatusChanged;
use App\Jobs\SendNewProfileNotification;
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
        'is_exchange',
        'exchange_character_id',
        'exchange_profile_code',
        'exchange_password',
        'notes',
        'password',
        'password_lock',
        'rejection_reason',
        'created_by',
        'approved_by',
        'approved_at',
        'status',
        'status_old',
        'hidden',
        'viewing_user_id',
        'viewing_started_at',
        'viewing_session_id',
        'last_notification_sent_at',
        'visible_at',
    ];

    protected $casts = [
        'status' => 'integer',
        'status_old' => 'integer',
        'password_lock' => 'boolean',
        'hidden' => 'boolean',
        'is_exchange' => 'boolean',
        'approved_at' => 'datetime',
        'viewing_started_at' => 'datetime',
        'last_notification_sent_at' => 'datetime',
        'visible_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($profile) {
            // Generate password khi tạo hồ sơ mới nếu chưa có
            if (empty($profile->password)) {
                $profile->password = $profile->generatePassword();
            }
            // Mặc định password_lock = false khi tạo mới
            if (is_null($profile->password_lock)) {
                $profile->password_lock = false;
            }
            
            // Set visible_at dựa trên delay_minutes của creator
            if (!isset($profile->visible_at) && isset($profile->created_by)) {
                $creator = User::find($profile->created_by);
                $delayMinutes = $creator?->delay_minutes ?? 0;
                $profile->visible_at = now()->addMinutes($delayMinutes);
            }
        });

        static::created(function ($profile) {
            // 1. Dispatch job gửi thông báo "Hồ sơ mới" khi profile visible
            if ($profile->visible_at && $profile->visible_at > now()) {
                SendNewProfileNotification::dispatch($profile)->delay($profile->visible_at);
            } else {
                SendNewProfileNotification::dispatch($profile);
            }
            
            // 2. Dispatch job gửi thông báo "Nhắc nhở" sau khi visible + reminder_minutes
            $reminderMinutes = \App\Models\SystemSetting::getNewProfileReminderMinutes();
            
            if ($reminderMinutes > 0) {
                // Tính thời gian gửi nhắc nhở = visible_at + reminder_minutes
                $visibleAt = $profile->visible_at && $profile->visible_at > now() 
                    ? $profile->visible_at 
                    : now();
                
                $reminderAt = \Carbon\Carbon::parse($visibleAt)->addMinutes($reminderMinutes);
                
                \App\Jobs\SendNewProfileReminderNotification::dispatch($profile)->delay($reminderAt);
                
            }
        });

        static::updated(function ($profile) {
            // Kiểm tra nếu trạng thái đã thay đổi
            if ($profile->wasChanged('status')) {
                $oldStatus = $profile->getOriginal('status');
                $newStatus = $profile->status;
                
                // Cập nhật status_old mà không trigger thêm event
                $profile->updateQuietly(['status_old' => $oldStatus]);
                
                // Dispatch event để Listener xử lý thông báo
                event(new ProfileStatusChanged($profile, $oldStatus, $newStatus));
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

    public function supportLogs(){
        return $this->hasMany(SupportLog::class);
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
     * Clear tất cả session viewing của một user
     */
    public static function clearAllViewingSessionsByUser(int $userId): int
    {
        return self::where('viewing_user_id', $userId)
            ->update([
                'viewing_user_id' => null,
                'viewing_started_at' => null,
                'viewing_session_id' => null,
            ]);
    }

    /**
     * Kiểm tra có thể xem hồ sơ không (không set session)
     */
    public function canBeViewed(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Nếu hồ sơ đang được xem bởi người khác (không phải user hiện tại)
        if ($this->isBeingViewed() && !$this->isBeingViewedBy($user->id)) {
            return false;
        }
        
        // Nếu user đang xem chính hồ sơ này hoặc không ai xem thì OK
        return true;
    }

    /**
     * Kiểm tra có thể thao tác với hồ sơ không (cho action buttons)
     */
    public function canBeInteracted(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Nếu không ai đang xem HOẶC chính user này đang xem → Có thể thao tác
        if (!$this->isBeingViewed() || $this->isBeingViewedBy($user->id)) {
            return true;
        }
        
        // Nếu có người khác đang xem → Không thể thao tác
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

        // Nếu user không có quyền duyệt và không có quyền từ chối thì bỏ qua session, cho phép xem luôn
        if (
            !$user->hasPermissionTo('approve_profile') &&
            !$user->hasPermissionTo('reject_profile')
        ) {
            return ['success' => true, 'message' => 'Bạn không cần session để xem hồ sơ.'];
        }

        // Clear tất cả session cũ của user này trước
        $clearedCount = self::clearAllViewingSessionsByUser($user->id);
        // Refresh để có dữ liệu mới nhất
        $this->refresh();
        
        // Nếu hồ sơ đang được xem bởi người khác (sau khi đã clear session của user hiện tại)
        if ($this->isBeingViewed() && !$this->isBeingViewedBy($user->id)) {
            $viewingUser = $this->viewingUser;
            $message = $viewingUser ? "Hồ sơ này đang được xử lý bởi {$viewingUser->username}" : "Hồ sơ này đang được xử lý bởi người khác";
           
            return ['success' => false, 'message' => $message];
        }
        
        // Thiết lập session xem hồ sơ mới
        $sessionId = \Illuminate\Support\Str::uuid()->toString();
        $result = $this->setViewingSession($user->id, $sessionId);
        
        if ($result) {
            return ['success' => true, 'message' => 'Session đã được thiết lập thành công.'];
        } else {
            return ['success' => false, 'message' => 'Không thể thiết lập session.'];
        }
    }

    /**
     * Generate password tự động
     */
    public function generatePassword(): string
    {
        // Tạo password 8 ký tự bao gồm chữ hoa, chữ thường và số
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $password = '';
        
        for ($i = 0; $i < 8; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $password;
    }

    /**
     * Kiểm tra và tạo password nếu chưa có
     */
    public function ensurePasswordExists(): bool
    {
        if (empty($this->password)) {
            $this->password = $this->generatePassword();
            $this->save();
            return true; // Password đã được tạo mới
        }
        return false; // Password đã tồn tại
    }


}
