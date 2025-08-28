<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\ProfileViewingSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfileViewingService
{
    /**
     * Kiểm tra xem profile có đang được xem bởi người khác không
     */
    public function isProfileBeingViewed(Profile $profile): bool
    {
        $this->cleanupInactiveSessions();
        
        return ProfileViewingSession::where('profile_id', $profile->id)
            ->where('is_active', true)
            ->where('last_activity', '>', now()->subMinutes(5))
            ->exists();
    }

    /**
     * Lấy thông tin người đang xem profile
     */
    public function getCurrentViewer(Profile $profile): ?User
    {
        $session = ProfileViewingSession::where('profile_id', $profile->id)
            ->where('is_active', true)
            ->where('last_activity', '>', now()->subMinutes(5))
            ->with('user')
            ->first();

        return $session?->user;
    }

    /**
     * Bắt đầu session xem profile
     */
    public function startViewingSession(Profile $profile, User $user): bool
    {
        // Cleanup sessions cũ
        $this->cleanupInactiveSessions();
        
        // Kiểm tra xem có session active nào không
        if ($this->isProfileBeingViewed($profile)) {
            return false;
        }

        try {
            DB::beginTransaction();
            
            // Deactivate tất cả session cũ của profile này
            ProfileViewingSession::where('profile_id', $profile->id)
                ->update(['is_active' => false]);
            
            // Tạo session mới
            ProfileViewingSession::create([
                'profile_id' => $profile->id,
                'user_id' => $user->id,
                'started_at' => now(),
                'last_activity' => now(),
                'is_active' => true,
            ]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Cập nhật last_activity của session
     */
    public function updateActivity(Profile $profile, User $user): bool
    {
        return ProfileViewingSession::where('profile_id', $profile->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['last_activity' => now()]);
    }

    /**
     * Kết thúc session xem profile
     */
    public function endViewingSession(Profile $profile, User $user): bool
    {
        return ProfileViewingSession::where('profile_id', $profile->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Cleanup các session không hoạt động (quá 5 phút)
     */
    public function cleanupInactiveSessions(): void
    {
        ProfileViewingSession::where('last_activity', '<', now()->subMinutes(5))
            ->update(['is_active' => false]);
    }

    /**
     * Kiểm tra xem user có đang xem profile này không
     */
    public function isUserViewing(Profile $profile, User $user): bool
    {
        return ProfileViewingSession::where('profile_id', $profile->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('last_activity', '>', now()->subMinutes(5))
            ->exists();
    }
}
