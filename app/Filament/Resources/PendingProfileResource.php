<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendingProfileResource\Pages;
use App\Filament\Resources\PendingProfileResource\RelationManagers;
use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class PendingProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Hồ sơ chờ';

    protected static ?string $modelLabel = 'Hồ sơ chờ';

    protected static ?string $pluralModelLabel = 'Hồ sơ chờ';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Hồ sơ';

    protected static bool $shouldRegisterNavigation = false;

    public static function canCreate(): bool
    {
        return false; // Không cho phép tạo mới hồ sơ chờ duyệt
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('view_any_pending::profile') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 5) // Chỉ hiển thị hồ sơ có status "Chờ" (trạng thái mới)
            ->where('hidden', false) // Lọc bỏ các hồ sơ đã bị ẩn
            ->where('visible_at', '<=', now()); // Chỉ hiển thị các hồ sơ đã đến thời gian visible
    }

    public static function getNavigationBadge(): ?string
    {
        if (!static::canViewAny()) {
            return null;
        }
        return static::getModel()::where('status', 5)->where('hidden', false)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Mã hồ sơ')
                    ->formatStateUsing(function (string $state, $record) {
                        $code = "#{$state}";
                        
                        // Hiển thị icon ổ khóa nếu hồ sơ đang được xem
                        if ($record->isBeingViewed()) {
                            $viewingUser = $record->viewingUser;
                            $tooltip = $viewingUser ? "Đang được xử lý bởi: {$viewingUser->username}" : "Đang được xử lý";
                            $code .= ' <span class="inline-flex items-center justify-center w-8 h-8 text-lg font-medium text-yellow-600 bg-yellow-100 rounded-full" title="' . $tooltip . '">🔒</span>';
                        }
                        
                        return $code;
                    })
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('character_id')
                    ->label('ID nhân vật')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('createdBy.username')
                    ->label('Người tạo')
                    ->formatStateUsing(function (string $state, $record) {
                        return $state;
                    })
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->formatStateUsing(function ($state, $record) {
                        $statuses = [
                            0 => 'Chờ duyệt',
                            1 => 'Đã duyệt',
                            2 => 'Từ chối',
                            3 => 'Hủy',
                            4 => 'Hỗ trợ',
                            5 => 'Chờ',
                        ];
                        
                        // Kiểm tra trạng thái "Đủ điều kiện" cho status = 5 (Chờ)
                        if ($state == 5 && $record->approved_at && $record->approved_at instanceof \Carbon\Carbon) {
                            $hoursSinceUpdate = $record->approved_at->diffInHours(now());
                            if ($hoursSinceUpdate >= 6) {
                                return 'Đủ điều kiện';
                            }
                        }
                        
                        return $statuses[$state] ?? 'Chờ duyệt';
                    })
                    ->badge()
                    ->color(function ($state, $record) {
                        // Kiểm tra trạng thái "Đủ điều kiện" cho status = 5 (Chờ)
                        if ($state == 5 && $record->approved_at && $record->approved_at instanceof \Carbon\Carbon) {
                            $hoursSinceUpdate = $record->approved_at->diffInHours(now());
                            if ($hoursSinceUpdate >= 6) {
                                return 'success'; // Màu xanh lá cho "Đủ điều kiện"
                            }
                        }
                        
                        return match ($state) {
                            '0' => 'warning',
                            '1' => 'success',
                            '2' => 'danger',
                            '3' => 'gray',
                            '4' => 'info',
                            '5' => 'secondary',
                            default => 'gray',
                        };
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Xem chi tiết')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(function (?Profile $record) {
                        return $record ? 'Chi tiết hồ sơ #' . $record->code : 'Chi tiết hồ sơ';
                    })
                    ->modalContent(function (?Profile $record) {
                        if (!$record) {
                            return new \Illuminate\Support\HtmlString('<div class="p-4 text-center text-gray-500">Không thể tải thông tin hồ sơ</div>');
                        }
                        
                        // Refresh record để có dữ liệu mới nhất
                        $record->refresh();
                        
                        // Clear cache để tránh stale data trong production
                        if (app()->environment('production')) {
                            \Cache::forget("profile_{$record->id}");
                        }
                        
                        // Chỉ check session cho hồ sơ chờ duyệt (status = 0)
                        if ($record->status == 0) {
                            $result = $record->handleViewSession();
                            
                            if (!$result['success']) {
                                return view('filament.resources.profile.modal-viewing', [
                                    'record' => $record,
                                    'errorMessage' => $result['message']
                                ]);
                            }
                        }
                        
                        $statuses = [
                            0 => 'Chờ duyệt',
                            1 => 'Đã duyệt',
                            2 => 'Từ chối',
                            3 => 'Hủy',
                            4 => 'Hỗ trợ',
                            5 => 'Chờ',
                        ];
                        
                        // Kiểm tra trạng thái "Đủ điều kiện" cho status = 5 (Chờ)
                        if ($record->status == 5 && $record->approved_at && $record->approved_at instanceof \Carbon\Carbon) {
                            $hoursSinceUpdate = $record->approved_at->diffInHours(now());
                            if ($hoursSinceUpdate >= 6) {
                                $statuses[5] = 'Đủ điều kiện';
                            }
                        }
                        
                        $statusColors = [
                            0 => 'warning',
                            1 => 'success',
                            2 => 'danger',
                            3 => 'gray',
                            4 => 'info',
                            5 => 'secondary',
                        ];
                        
                        // Cập nhật màu cho trạng thái "Đủ điều kiện"
                        if ($record->status == 5 && $record->approved_at && $record->approved_at instanceof \Carbon\Carbon) {
                            $hoursSinceUpdate = $record->approved_at->diffInHours(now());
                            if ($hoursSinceUpdate >= 6) {
                                $statusColors[5] = 'success';
                            }
                        }
                        
                        return view('filament.resources.profile.modal-content', [
                            'record' => $record,
                            'statuses' => $statuses,
                            'statusColors' => $statusColors,
                            'supportLogs' => collect(), // Không có support logs cho hồ sơ chờ duyệt
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Đóng')
                    ->modalActions([
                        
                        Tables\Actions\Action::make('complete')
                            ->label('Hoàn thành')
                            ->icon('heroicon-m-check-circle')
                            ->color('success')
                            ->requiresConfirmation()
                            ->modalHeading('Xác nhận hoàn thành hồ sơ')
                            ->modalDescription(function (?Profile $record) {
                                return $record ? 'Bạn có chắc chắn muốn hoàn thành hồ sơ #' . $record->code . '?' : 'Bạn có chắc chắn muốn hoàn thành hồ sơ?';
                            })
                            ->modalSubmitActionLabel('Có, hoàn thành')
                            ->modalCancelActionLabel('Không, hủy bỏ')
                            ->visible(function (?Profile $record) {
                                if (!$record) {
                                    return false;
                                }
                                
                                $user = auth()->user();
                                if (!$user) {
                                    return false;
                                }
                                
                                // Chỉ hiển thị khi status là "Chờ" (5), user có permission approve_profile và đã đủ 6 tiếng
                                if ($record->status == 5 && $user->hasPermissionTo('approve_profile')) {
                                    // Kiểm tra nếu đã đủ 6 tiếng từ khi approved_at
                                    if ($record->approved_at && $record->approved_at instanceof \Carbon\Carbon) {
                                        $hoursSinceUpdate = $record->approved_at->diffInHours(now());
                                        return $hoursSinceUpdate >= 6;
                                    }
                                }
                                
                                return false;
                            })
                            ->action(function (?Profile $record) {
                                if (!$record) {
                                    Notification::make()
                                        ->title('Lỗi')
                                        ->body('Không thể tìm thấy hồ sơ')
                                        ->danger()
                                        ->send();
                                    return;
                                }
                                
                                $user = auth()->user();
                                
                                // Cập nhật status từ "Chờ" (5) sang "Đã duyệt" (1)
                                $record->update([
                                    'status' => 1, // Đã duyệt
                                    'approved_at' => now(),
                                    'approved_by' => $user->id,
                                ]);

                                // Clear viewing session sau khi hoàn thành
                                $record->clearViewingSession();

                                Notification::make()
                                    ->title('Đã hoàn thành hồ sơ')
                                    ->body('Hồ sơ #' . $record->code . ' đã được hoàn thành và chuyển sang trạng thái "Đã duyệt".')
                                    ->success()
                                    ->send();
                                    
                                // Redirect về trang pending profiles sau khi hoàn thành
                                return redirect()->to('/admin/pending-profiles');
                            }),
                    ])
                    ->visible(function (?Profile $record) {
                        if (!$record) {
                            return false;
                        }
                        
                        $user = auth()->user();
                        
                        if (!$user) return false;
                        
                        // Super admin và admin có thể xem tất cả
                        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
                            return true;
                        }
                        
                        // Người duyệt có thể xem hồ sơ chờ
                        if ($user->hasRole('approver')) {
                            return $record->status == 5;
                        }
                        
                        return false;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
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
            'index' => Pages\ListPendingProfiles::route('/'),
        ];
    }
}
