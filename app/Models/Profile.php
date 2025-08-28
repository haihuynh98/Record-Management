<?php

namespace App\Models;

use App\Events\ProfileStatusChanged;
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
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    protected static function booted()
    {
        static::updating(function ($profile) {
            // Lưu trạng thái cũ trước khi cập nhật
            $profile->old_status = $profile->getOriginal('status');
        });

        static::updated(function ($profile) {
            // Kiểm tra nếu trạng thái đã thay đổi
            if (isset($profile->old_status) && $profile->old_status !== $profile->status) {
                Log::info('Profile status changed', [
                    'profile_id' => $profile->id,
                    'profile_code' => $profile->code,
                    'old_status' => $profile->old_status,
                    'new_status' => $profile->status
                ]);

                // Dispatch event
                event(new ProfileStatusChanged($profile, $profile->old_status, $profile->status));
            }
        });
    }

    public function approvedBy(){
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUnApprove($q)
    {
        return $q->whereNull('approved_at');
    }
}
