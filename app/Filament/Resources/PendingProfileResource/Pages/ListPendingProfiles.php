<?php

namespace App\Filament\Resources\PendingProfileResource\Pages;

use App\Filament\Resources\PendingProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendingProfiles extends ListRecords
{
    protected static string $resource = PendingProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
