<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'name',
    ];

    public function vipPlayers(): HasMany
    {
        return $this->hasMany(VipPlayer::class);
    }
}
