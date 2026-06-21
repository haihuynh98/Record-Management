<?php

namespace App\Filament\Resources\VipPlayerResource\Pages;

use App\Filament\Resources\VipPlayerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVipPlayer extends CreateRecord
{
    protected static string $resource = VipPlayerResource::class;

    public function getHeading(): string
    {
        return 'Thêm người chơi VIP';
    }

    public function getTitle(): string
    {
        return 'Thêm người chơi VIP';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
