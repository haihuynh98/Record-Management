<?php

namespace App\Filament\Pages;

use App\Models\Profile;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ManageViewingSessions extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationLabel = 'Hồ sơ đang xem';
    protected static ?string $title = 'Quản lý phiên xem';
    protected static ?string $slug = 'manage-viewing-sessions';
    protected static ?string $navigationGroup = 'Hồ sơ';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.manage-viewing-sessions';

    public static function getNavigationBadge(): ?string
    {
        $count = Profile::whereNotNull('viewing_user_id')
            ->whereNotNull('viewing_started_at')
            ->count();
        
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Làm mới')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->dispatch('$refresh')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Profile::query()
                    ->whereNotNull('viewing_user_id')
                    ->whereNotNull('viewing_started_at')
                    ->orderBy('viewing_started_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Mã hồ sơ')
                    ->formatStateUsing(fn (string $state) => "#{$state}")
                    ->searchable()
                    ->copyable()
                    ->copyableState(fn (string $state): string => "#{$state}")
                    ->copyMessage('Đã sao chép mã hồ sơ')
                    ->copyMessageDuration(1500),
                
                Tables\Columns\TextColumn::make('character_id')
                    ->label('ID nhân vật')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Đã sao chép ID nhân vật')
                    ->copyMessageDuration(1500),
                
                Tables\Columns\TextColumn::make('viewingUser.username')
                    ->label('Người đang xem')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) return '-';
                        return $state;
                    })
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('viewing_started_at')
                    ->label('Bắt đầu xem')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('viewing_duration')
                    ->label('Thời gian xem')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record->viewing_started_at) {
                            return '-';
                        }
                        
                        $startTime = $record->viewing_started_at;
                        $now = now();
                        $diffInMinutes = $startTime->diffInMinutes($now);
                        
                        if ($diffInMinutes < 60) {
                            return $diffInMinutes . ' phút';
                        }
                        
                        $hours = floor($diffInMinutes / 60);
                        $minutes = $diffInMinutes % 60;
                        return $hours . ' giờ ' . $minutes . ' phút';
                    })
                    ->badge()
                    ->color(function ($state, $record) {
                        if (!$record->viewing_started_at) {
                            return 'gray';
                        }
                        
                        $diffInMinutes = $record->viewing_started_at->diffInMinutes(now());
                        
                        if ($diffInMinutes < 30) {
                            return 'success';
                        } elseif ($diffInMinutes < 60) {
                            return 'warning';
                        } else {
                            return 'danger';
                        }
                    }),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái hồ sơ')
                    ->formatStateUsing(function ($state, $record) {
                        $statuses = [
                            0 => 'Chờ duyệt',
                            1 => 'Đã duyệt',
                            2 => 'Từ chối',
                            3 => 'Hủy',
                            4 => 'Hỗ trợ',
                            5 => 'Chờ',
                            6 => 'Nộp lại',
                        ];
                        
                        if (in_array($state, [0, 6]) && $record->is_exchange) {
                            return 'Giao Lưu';
                        }
                        
                        return $statuses[$state] ?? 'Chờ duyệt';
                    })
                    ->badge()
                    ->color(function ($state, $record) {
                        if (in_array($state, [0]) && $record->is_exchange) {
                            return 'info';
                        }
                        
                        return match ($state) {
                            0 => 'warning',
                            1 => 'success',
                            2 => 'danger',
                            3 => 'gray',
                            4 => 'info',
                            5 => 'warning',
                            6 => 'info',
                            default => 'warning',
                        };
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('viewing_user_id')
                    ->label('Người đang xem')
                    ->relationship('viewingUser', 'username')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        0 => 'Chờ duyệt',
                        1 => 'Đã duyệt',
                        2 => 'Từ chối',
                        3 => 'Hủy',
                        4 => 'Hỗ trợ',
                        5 => 'Chờ',
                        6 => 'Nộp lại',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('clear_session')
                    ->label('Xóa phiên xem')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Xác nhận xóa phiên xem')
                    ->modalDescription(function (?Profile $record) {
                        if (!$record) return 'Bạn có chắc chắn muốn xóa phiên xem này?';
                        
                        $username = $record->viewingUser?->username ?? 'người dùng';
                        return "Bạn có chắc chắn muốn xóa phiên xem của {$username} cho hồ sơ #{$record->code}?";
                    })
                    ->modalSubmitActionLabel('Có, xóa phiên xem')
                    ->modalCancelActionLabel('Không, hủy bỏ')
                    ->action(function (?Profile $record) {
                        if (!$record) {
                            Notification::make()
                                ->title('Lỗi')
                                ->body('Không thể tìm thấy hồ sơ')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        $username = $record->viewingUser?->username ?? 'người dùng';
                        
                        if ($record->clearViewingSession()) {
                            Notification::make()
                                ->title('Đã xóa phiên xem')
                                ->body("Đã xóa phiên xem của {$username} cho hồ sơ #{$record->code}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Lỗi')
                                ->body('Không thể xóa phiên xem')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('clear_all_sessions')
                        ->label('Xóa tất cả phiên xem')
                        ->icon('heroicon-m-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Xác nhận xóa tất cả phiên xem')
                        ->modalDescription('Bạn có chắc chắn muốn xóa tất cả phiên xem đã chọn?')
                        ->modalSubmitActionLabel('Có, xóa tất cả')
                        ->modalCancelActionLabel('Không, hủy bỏ')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->clearViewingSession()) {
                                    $count++;
                                }
                            }
                            
                            Notification::make()
                                ->title('Đã xóa phiên xem')
                                ->body("Đã xóa {$count} phiên xem thành công")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('viewing_started_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->poll('30s')
            ->emptyStateHeading('Không có hồ sơ nào đang được xem')
            ->emptyStateDescription('Hiện tại không có hồ sơ nào đang được xem bởi người dùng khác.')
            ->emptyStateIcon('heroicon-o-eye-slash');
    }
}

