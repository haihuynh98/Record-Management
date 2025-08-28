<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileViewingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'user_id',
        'started_at',
        'last_activity',
        'is_active',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_activity' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope để lấy session active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope để lấy session cũ (quá 5 phút không hoạt động)
     */
    public function scopeInactive($query)
    {
        return $query->where('last_activity', '<', now()->subMinutes(5));
    }
}
