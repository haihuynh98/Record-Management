<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    public function getHeading(): string { return 'Tạo mới người dùng'; }
    public function getTitle(): string { return 'Tạo mới người dùng'; }
}
