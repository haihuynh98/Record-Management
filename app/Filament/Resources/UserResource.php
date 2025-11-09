<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Người dùng';

    protected static ?string $navigationLabel = 'Người dùng';
    protected static ?string $pluralModelLabel = 'Người dùng';
    protected static ?string $modelLabel = 'Người dùng';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->label('Tên người dùng')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')
                    ->label('Tên đăng nhập')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->helperText('Dùng để đăng nhập vào hệ thống'),
                Forms\Components\TextInput::make('password')
                    ->label('Mật khẩu')
                    ->password()
                    ->revealable()
                    ->required(fn (string $context) => $context === 'create')
                    ->hidden(fn (string $context) => $context === 'edit')
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? \Illuminate\Support\Facades\Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state)),
                Forms\Components\Select::make('roles')
                    ->label('Vai trò')
                    ->options(function () {
                        return Role::where('name', '!=', 'super_admin')
                            ->pluck('name', 'id')
                            ->map(function ($roleName) {
                                // Map role names to Vietnamese labels
                                $labels = [
                                    'admin' => 'Quản trị viên',
                                    'approver' => 'Người phê duyệt',
                                    'creator' => 'Người tạo',
                                ];
                                return $labels[$roleName] ?? $roleName;
                            });
                    })
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->visible(fn () => auth()->user()?->hasPermissionTo('update_user'))
                    ->required(fn () => auth()->user()?->hasPermissionTo('update_user'))
                    ->live(),
                Forms\Components\Toggle::make('is_priority')
                    ->label('Người dùng ưu tiên')
                    ->helperText('Đánh dấu người dùng này là ưu tiên')
                    ->default(false)
                    ->visible(false),
                Forms\Components\TextInput::make('delay_minutes')
                    ->label('Thời gian (phút)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1440) // Max 24 hours (1440 minutes)
                    ->default(0)
                    ->suffix('phút')
                    ->visible(function (Forms\Get $get) {
                        $selectedRoles = $get('roles');
                        
                        if (!$selectedRoles) return false;
                        
                        // Check if any selected role is 'creator'
                        $creatorRole = \Spatie\Permission\Models\Role::where('name', 'creator')->first();
                        
                        if (is_array($selectedRoles)) {
                            return $creatorRole && in_array($creatorRole->id, $selectedRoles);
                        }
                        
                        return $creatorRole && $creatorRole->id == $selectedRoles;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->label('Tên người dùng')
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('email')
                    ->label('Tên đăng nhập')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_priority')
                    ->label('Ưu tiên')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->visible(false),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Vai trò')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        $labels = [
                            'admin' => 'Quản trị viên',
                            'approver' => 'Người phê duyệt',
                            'creator' => 'Người tạo',
                            'super_admin' => 'Super Admin',
                        ];
                        return $labels[$state] ?? $state;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ngày cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasPermissionTo('delete_any_user')),
                ]),
            ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
