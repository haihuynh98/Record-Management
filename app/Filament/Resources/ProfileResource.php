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
                    ->disabled(function () {
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(function (Profile $record): ?string {
                $user = auth()->user();

                $notApproved = !$record->status;
                $canEdit = $user && (
                        $user->hasAnyRole(['admin','super_admin'])
                        || ($user->hasRole('creator') && $record->created_by === $user->id)
                    );

                return ($notApproved && $canEdit)
                    ? static::getUrl('edit', ['record' => $record])
                    : null;
            })
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Mã hồ sơ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('createdBy.username')
                    ->label('Người tạo')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approvedBy.username')
                    ->label('Người duyệt')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('status')
                    ->label('Trạng thái')
                    ->boolean()
                    ->true(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tạo lúc'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(function (Profile $record) {
                        $user = auth()->user();
                        if ($user?->hasRole('admin') || $user?->hasRole('super_admin')) {
                            return true;
                        }

                        if ($user?->hasRole('approver')) {
                            return false;
                        }

                        if ($user?->hasRole('creator')) {
                            if (Schema::hasColumn('profiles', 'status')) {
                                return !$record->status && $record->created_by === $user->id;
                            }
                        }

                        return false;
                    }),
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

                        if (Schema::hasColumn('profiles', 'status')) {
                            return !$record->status;
                        }

                        return is_null($record->approved_at);
                    })
                    ->action(function (Profile $record) {
                        $payload = [];
                        $user = auth()->user();
                        if (Schema::hasColumn('profiles', 'status')) {
                            $payload['status'] = true;
                        }

                        if (Schema::hasColumn('profiles', 'approved_at')) {
                            $payload['approved_at'] = now();
                        }

                        if (Schema::hasColumn('profiles', 'approved_by')) {
                            $payload['approved_by'] = $user->id;
                        }

                        $record->update($payload);

                        Notification::make()
                            ->title('Đã duyệt hồ sơ')
                            ->success()
                            ->send();
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
            return $query->whereNull('approved_at');
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
