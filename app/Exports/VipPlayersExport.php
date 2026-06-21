<?php

namespace App\Exports;

use App\Models\Game;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class VipPlayersExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return Game::query()
            ->with(['vipPlayers' => fn ($query) => $query->orderBy('customer_name')])
            ->orderBy('name')
            ->get()
            ->map(fn (Game $game) => new VipPlayersPerGameSheet($game))
            ->all();
    }
}
