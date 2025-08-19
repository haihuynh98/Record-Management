<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    public function getHeading(): string { return 'Tạo mới người dùng'; }
    public function getTitle(): string { return 'Tạo mới người dùng'; }

    protected function afterCreate(): void
    {
        // Gán roles cho user mới tạo
        $selectedRoles = $this->form->getState()['roles'] ?? [];
        if (!empty($selectedRoles)) {
            $roles = Role::whereIn('id', $selectedRoles)->pluck('name');
            $this->record->syncRoles($roles);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
