<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProfile extends CreateRecord
{
    protected static string $resource = ProfileResource::class;
    public function getHeading(): string { return 'Tạo mới hồ sơ'; }
    public function getTitle(): string { return 'Tạo mới hồ sơ'; }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = preg_replace('/[^A-Za-z0-9]/', '', preg_replace('/\s+/', '', (string) $data['code']));
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
