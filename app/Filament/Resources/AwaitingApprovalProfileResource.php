<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages;
use App\Models\Profile;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class AwaitingApprovalProfileResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Profile::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Hồ sơ';

    protected static ?string $navigationLabel = 'Hồ sơ chờ duyệt';
    protected static ?string $pluralModelLabel = 'Hồ sơ chờ duyệt';
    protected static ?string $modelLabel = 'Hồ sơ chờ duyệt';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (!$user) return null;

        // Chỉ hiển thị badge cho người có quyền
        if (!$user->hasPermissionTo('view_awaiting_approval_profile')) {
            return null;
        }

        $count = static::getEloquentQuery()
            ->where('status', 0)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getEloquentQuery()
            ->where('status', 0)
            ->count();

        return $count > 0 ? 'warning' : null;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user?->hasPermissionTo('view_awaiting_approval_profile') ?? false;
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'approve',
            'reject',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user) return $query->whereRaw('1=0');

        // Chỉ hiển thị hồ sơ chờ duyệt (status = 0)
        $query = $query->where('status', 0);

        // Super admin và admin có thể xem tất cả hồ sơ chờ duyệt
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return $query->orderBy('created_at', 'asc');
        }

        // Người duyệt chỉ xem hồ sơ chờ duyệt
        if ($user->hasRole('approver')) {
            return $query->orderBy('created_at', 'asc');
        }

        return $query->whereRaw('1=0');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\AwaitingApprovalProfiles::route('/'),
        ];
    }

    // Sử dụng lại table và form từ ProfileResource
    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return ProfileResource::table($table);
    }

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return ProfileResource::form($form);
    }
}
