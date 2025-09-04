<?php

namespace App\Filament\Resources\SupportProfileResource\Pages;

use App\Filament\Resources\SupportProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupportProfiles extends ListRecords
{
    protected static string $resource = SupportProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
