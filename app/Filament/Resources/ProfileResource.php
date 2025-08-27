<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages;
use App\Filament\Resources\ProfileResource\RelationManagers;
use App\Models\Profile;
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
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Support\RawJs;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Hồ sơ';

    protected static ?string $navigationLabel = 'Hồ sơ';
    protected static ?string $pluralModelLabel = 'Hồ sơ';
    protected static ?string $modelLabel = 'Hồ sơ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Mã hồ sơ')
                    ->required()
                    ->unique(ignoreRecord: true, table: Profile::class, column: 'code')
                    ->disabled(function (string $context) {
                        // Disable trong màn hình edit
                        if ($context === 'edit') {
                            return true;
                        }
                        
                        $user = auth()->user();
                        if ($user?->hasRole('admin') || $user?->hasRole('super_admin') || $user?->hasRole('creator')) {
                            return false;
                        }
                        return true;
                    })
                    ->rules(['alpha_num'])
                    ->validationMessages([
                        'alpha_num' => 'Chỉ cho phép ký tự 0-9, a-z, A-Z (không khoảng trắng/ký tự đặc biệt).',
                    ])
                    ->maxLength(64),
                Forms\Components\TextInput::make('amount')
                    ->label('Giá trị hồ sơ')
                    ->required()
                    ->numeric()
                    ->minValue(40000)
                    ->suffix('VNĐ')
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
            ->recordUrl(function (Profile $record): ?string {
                $user = auth()->user();

                $canEdit = $user && (
                        $user->hasAnyRole(['admin','super_admin'])
                        || ($user->hasRole('creator') && $record->created_by === $user->id)
                    );

                return ($canEdit)
                    ? static::getUrl('edit', ['record' => $record])
                    : null;
            })
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Mã hồ sơ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Giá trị hồ sơ')
                    ->money(currency: 'VND')
                    ->numeric(thousandsSeparator: ','),
                Tables\Columns\TextColumn::make('approvedBy.username')
                    ->label('Người duyệt')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Action::make('approve')
                    ->label('Duyệt')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function (Profile $record) {
                        $user = auth()->user();
                        if (!$user?->hasRole('approver') && !$user?->hasRole('admin') && !$user?->hasRole('super_admin')) {
                            return false;
                        }

                        return $record->status === 0; // Chỉ hiển thị cho hồ sơ chờ duyệt
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
                Tables\Actions\EditAction::make()
                    ->visible(function (Profile $record) {
                        $user = auth()->user();
                        if ($user?->hasRole('admin') || $user?->hasRole('super_admin')) {
                            return $record->status === 0; // Chỉ cho phép edit hồ sơ chờ duyệt
                        }

                        if ($user?->hasRole('approver')) {
                            return false;
                        }

                        if ($user?->hasRole('creator')) {
                            return $record->status === 0 && $record->created_by === $user->id;
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

        if ($user->hasRole('admin') || $user->hasRole('super_admin')) return $query;

        if ($user->hasRole('creator')) {
            return $query->where('created_by', $user->id);
        }

        if ($user->hasRole('approver')) {
            return $query->where('status', 0); // Chỉ hiển thị hồ sơ chờ duyệt
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
