<?php

namespace App\Filament\Resources\VipPlayerResource\Pages;

use App\Filament\Resources\VipPlayerResource;
use Filament\Resources\Pages\EditRecord;

class EditVipPlayer extends EditRecord
{
    protected static string $resource = VipPlayerResource::class;

    public function getHeading(): string
    {
        return 'Sửa thông tin người chơi VIP';
    }

    public function getTitle(): string
    {
        return 'Sửa thông tin người chơi VIP';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
