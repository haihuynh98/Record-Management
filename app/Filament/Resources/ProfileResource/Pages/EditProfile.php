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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['code'])) {
            $data['code'] = preg_replace('/[^A-Za-z0-9]/', '', preg_replace('/\s+/', '', (string) $data['code']));
        }
        return $data;
    }
    protected function getHeaderActions(): array
    {
        return [
//            Actions\DeleteAction::make(),
        ];
    }
}
