<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages;
use App\Filament\Resources\ProfileResource\RelationManagers;
use App\Models\Profile;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Schema;

use Filament\Tables\Actions\EditAction;
use Filament\Support\RawJs;

class ProfileResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Profile::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Hồ sơ';

    protected static ?string $navigationLabel = 'Hồ sơ';
    protected static ?string $pluralModelLabel = 'Hồ sơ';
    protected static ?string $modelLabel = 'Hồ sơ';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'approve',
            'reject',
            'resubmit',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Mã hồ sơ')
                    ->required()
                    ->unique(ignoreRecord: true, table: Profile::class, column: 'code')
                    ->prefix('#')
                    ->disabled(function (string $context) {
                        // Disable trong màn hình edit
                        if ($context === 'edit') {
                            return true;
                        }
                        
                        $user = auth()->user();
                        if ($user?->hasPermissionTo('create_profile')) {
                            return false;
                        }
                        return true;
                    })
                    ->inputMode('numeric')
                    ->extraInputAttributes([
                        'pattern' => '[0-9]*',
                        'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57',
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")'
                    ])
                    ->rules(['regex:/^[0-9]+$/'])
                    ->validationMessages([
                        'regex' => 'Mã hồ sơ chỉ cho phép nhập số (0-9) và không có dấu cách.',
                    ])
                    ->maxLength(64),
                Forms\Components\TextInput::make('amount')
                    ->label('Giá trị hồ sơ')
                    ->required()
                    ->numeric()
                    ->minValue(40000)
                    ->helperText('Giá trị tối thiểu: 40,000 VNĐ')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Lý do từ chối')
                    ->placeholder('Nhập lý do từ chối hồ sơ...')
                    ->minLength(10)
                    ->maxLength(500)
                    ->visible(function (string $context, $record) {
                        // Chỉ hiển thị ở màn hình edit và status là reject (2)
                        return $context === 'edit' && $record && $record->status === 2;
                    })
                    ->helperText('Chỉ hiển thị khi chỉnh sửa hồ sơ bị từ chối'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->poll('5s')

            ->recordUrl(function (Profile $record): ?string {
                $user = auth()->user();

                $canEdit = $user && (
                        $user->can('update', $record)
                        || ($user->hasPermissionTo('update_profile') && $record->created_by === $user->id)
                    );

                return ($canEdit)
                    ? static::getUrl('edit', ['record' => $record])
                    : null;
            })
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Mã hồ sơ')
                    ->formatStateUsing(fn (string $state): string => "#{$state}")
                    ->searchable()
                    ->copyable()
                    ->copyableState(fn (string $state): string => "#{$state}")
                    ->copyMessage('Đã sao chép mã hồ sơ vào clipboard')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Giá trị hồ sơ')
                    ->money(currency: 'VND')
                    ->numeric(thousandsSeparator: ','),
                Tables\Columns\TextColumn::make('approvedBy.username')
                    ->label('Người duyệt')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(function () {
                        $user = auth()->user();
                        return $user?->hasPermissionTo('approve_profile');
                    }),
                Tables\Columns\TextColumn::make('createdBy.username')
                    ->label('Người tạo')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->createdBy && $record->createdBy->roles->contains('name', 'creator') && $record->createdBy->is_priority) {
                            return $state . ' ⭐';
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->formatStateUsing(function ($state) {
                        $statuses = [
                            0 => 'Chờ duyệt',
                            1 => 'Đã duyệt',
                            2 => 'Từ chối',
                        ];
                        return $statuses[$state] ?? 'Chờ duyệt';
                    })
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            0 => 'warning',
                            1 => 'success',
                            2 => 'danger',
                            default => 'warning',
                        };
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tạo lúc'),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('approve')
                    ->label('Duyệt')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function (Profile $record) {
                        $user = auth()->user();
                        return $user?->hasPermissionTo('approve_profile') && $record->status === 0;
                    })
                    ->action(function (Profile $record) {
                        $user = auth()->user();
                        
                        $record->update([
                            'status' => 1, // Đã duyệt
                            'approved_at' => now(),
                            'approved_by' => $user->id,
                        ]);

                        Notification::make()
                            ->title('Đã duyệt hồ sơ')
                            ->success()
                            ->send();
                    }),
                \Filament\Tables\Actions\Action::make('reject')
                    ->label('Từ chối')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(function (Profile $record) {
                        $user = auth()->user();
                        return $user?->hasPermissionTo('reject_profile') && $record->status === 0;
                    })
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Lý do từ chối')
                            ->required()
                            ->placeholder('Nhập lý do từ chối hồ sơ...')
                            ->minLength(10)
                            ->maxLength(500),
                    ])
                    ->action(function (Profile $record, array $data) {
                        $user = auth()->user();
                        
                        $record->update([
                            'status' => 2, // Từ chối
                            'rejection_reason' => $data['rejection_reason'],
                            'approved_at' => now(),
                            'approved_by' => $user->id,
                        ]);

                        Notification::make()
                            ->title('Đã từ chối hồ sơ')
                            ->success()
                            ->send();
                    }),
                \Filament\Tables\Actions\Action::make('resubmit')
                    ->label('Nộp lại')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(function (Profile $record) {
                        $user = auth()->user();
                        
                        // Cho phép resubmit nếu:
                        // 1. Có quyền resubmit_profile
                        // 2. Hồ sơ bị từ chối (status = 2)
                        // 3. Và (là người tạo HOẶC là admin/super_admin)
                        return $user?->hasPermissionTo('resubmit_profile') && 
                               $record->status === 2 && 
                               ($record->created_by === $user->id || $user->hasRole(['admin', 'super_admin']));
                    })
                    ->action(function (Profile $record) {
                        $record->update([
                            'status' => 0, // Chờ duyệt
                            'rejection_reason' => null, // Xóa lý do từ chối
                        ]);

                        Notification::make()
                            ->title('Đã nộp lại hồ sơ thành công')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make()
                    ->visible(function (Profile $record) {
                        $user = auth()->user();
                        if ($user?->can('update', $record)) {
                            return $record->status === 0; // Chỉ cho phép edit hồ sơ chờ duyệt
                        }

                        if ($user?->hasPermissionTo('update_profile') && $record->created_by === $user->id) {
                            return $record->status === 0;
                        }

                        return false;
                    }),
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user) return $query->whereRaw('1=0');

        // Super admin và admin có thể xem tất cả
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) return $query;

        // Người tạo chỉ xem hồ sơ của mình
        if ($user->hasRole('creator')) {
            return $query->where('created_by', $user->id);
        }

        // Người duyệt chỉ xem hồ sơ chờ duyệt
        if ($user->hasRole('approver')) {
            return $query->where('status', 0);
        }

        return $query->whereRaw('1=0');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfiles::route('/'),
            'create' => Pages\CreateProfile::route('/create'),
            'edit' => Pages\EditProfile::route('/{record}/edit'),
        ];
    }
}
