<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VipPlayer extends Model
{
    protected $fillable = [
        'game_id',
        'customer_name',
        'phone',
        'facebook_link',
        'total_deposit',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_deposit' => 'integer',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
