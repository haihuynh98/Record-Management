<?php

namespace App\Filament\Resources\VipPlayerResource\Pages;

use App\Filament\Resources\VipPlayerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVipPlayers extends ListRecords
{
    protected static string $resource = VipPlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Tải danh sách Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => VipPlayerResource::downloadExport()),
            Actions\CreateAction::make()
                ->label('Thêm người chơi VIP'),
        ];
    }
}
