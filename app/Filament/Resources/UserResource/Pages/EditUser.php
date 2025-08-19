<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    
    public function getHeading(): string
    {
        return 'Chỉnh sửa người dùng';
    }

    public function getTitle(): string
    {
        return 'Chỉnh sửa người dùng';
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load roles cho user hiện tại
        $data['roles'] = $this->record->roles->pluck('id')->toArray();
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Cập nhật roles cho user
        $selectedRoles = $this->form->getState()['roles'] ?? [];
        $roles = Role::whereIn('id', $selectedRoles)->pluck('name');
        $this->record->syncRoles($roles);
    }
}
