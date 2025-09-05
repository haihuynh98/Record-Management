<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\AwaitingApprovalProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class AwaitingApprovalProfiles extends ListRecords
{
    protected static string $resource = AwaitingApprovalProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Không có action tạo mới ở trang này vì chỉ hiển thị hồ sơ chờ duyệt
        ];
    }

    protected function getTablePollingInterval(): ?string
    {
        return '5s'; // Polling nhanh hơn cho trang chờ duyệt
    }
}
