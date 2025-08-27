<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'profiles';
    protected $fillable = [
        'code',
        'amount',
        'rejection_reason',
        'created_by',
        'approved_by',
        'approved_at',
        'status',
    ];

    protected $casts = [
        'amount' => 'integer',
        'status' => 'integer',
    ];

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
