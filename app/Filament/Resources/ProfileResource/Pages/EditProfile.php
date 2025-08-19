<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfile extends EditRecord
{
    protected static string $resource = ProfileResource::class;
    public function getHeading(): string
    {
        return 'Chỉnh sửa hồ sơ';
    }

    public function getTitle(): string
    {
        return 'Chỉnh sửa hồ sơ';
    }
    protected function getHeaderActions(): array
    {
        return [
//            Actions\DeleteAction::make(),
        ];
    }
}
