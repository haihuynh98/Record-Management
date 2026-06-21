<?php

namespace App\Filament\Resources;

use App\Exports\VipPlayersExport;
use App\Filament\Resources\VipPlayerResource\Pages;
use App\Models\Game;
use App\Models\VipPlayer;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VipPlayerResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = VipPlayer::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Người chơi VIP';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Người chơi VIP';

    protected static ?string $pluralModelLabel = 'Người chơi VIP';

    protected static ?string $modelLabel = 'Người chơi VIP';

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
                Forms\Components\Select::make('game_id')
                    ->label('Chọn game')
                    ->options(fn () => Game::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên game')
                            ->required()
                            ->maxLength(255)
                            ->unique('games', 'name'),
                    ])
                    ->createOptionUsing(fn (array $data) => Game::create($data)->getKey()),
                Forms\Components\TextInput::make('customer_name')
                    ->label('Họ tên khách hàng')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Số điện thoại')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('facebook_link')
                    ->label('Link Facebook')
                    ->url()
                    ->maxLength(500),
                Forms\Components\TextInput::make('total_deposit')
                    ->label('Tổng số tiền đã nạp')
                    ->required()
                    ->live(onBlur: false)
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                        $formatted = self::formatDepositInput($state);

                        if ($formatted !== $state) {
                            $set('total_deposit', $formatted);
                        }
                    })
                    ->formatStateUsing(fn ($state) => self::formatDepositInput($state))
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) str_replace(',', '', (string) $state) : 0)
                    ->rules(['required', 'regex:/^[\d,]+$/'])
                    ->helperText('Nhập số tiền, hệ thống tự phân ngàn bằng dấu "," khi nhập quá 3 chữ số'),
                Forms\Components\Textarea::make('notes')
                    ->label('Chú thích')
                    ->rows(3)
                    ->maxLength(1000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('game.name')
                    ->label('Game')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Họ tên khách hàng')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Số điện thoại')
                    ->searchable(),
                Tables\Columns\TextColumn::make('facebook_link')
                    ->label('Link Facebook')
                    ->url(fn ($state) => $state)
                    ->openUrlInNewTab()
                    ->limit(30)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('total_deposit')
                    ->label('Tổng số tiền đã nạp')
                    ->formatStateUsing(fn ($state) => number_format((int) $state, 0, '.', ','))
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Chú thích')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('game_id')
                    ->label('Game')
                    ->options(fn () => Game::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasPermissionTo('delete_any_vip::player')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVipPlayers::route('/'),
            'create' => Pages\CreateVipPlayer::route('/create'),
            'edit' => Pages\EditVipPlayer::route('/{record}/edit'),
        ];
    }

    public static function downloadExport(): BinaryFileResponse
    {
        $filename = 'danh-sach-nguoi-choi-vip-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(new VipPlayersExport, $filename);
    }

    protected static function formatDepositInput(mixed $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $state);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) <= 3) {
            return $digits;
        }

        return number_format((int) $digits, 0, '.', ',');
    }
}
