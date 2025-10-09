<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportProfileResource\Pages;
use App\Filament\Resources\SupportProfileResource\RelationManagers;
use App\Models\Profile;
use App\Models\SupportLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class SupportProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationLabel = 'Hồ sơ hỗ trợ';

    protected static ?string $modelLabel = 'Hồ sơ hỗ trợ';

    protected static ?string $pluralModelLabel = 'Hồ sơ hỗ trợ';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Hồ sơ';

    public static function canCreate(): bool
    {
        return false; // Không cho phép tạo mới hồ sơ hỗ trợ
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('view_any_support::profile') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 4) // Chỉ hiển thị hồ sơ có status hỗ trợ
            ->where('hidden', false); // Lọc bỏ các hồ sơ đã bị ẩn
    }

    public static function getNavigationBadge(): ?string
    {
        if (!static::canViewAny()) {
            return null;
        }
        return static::getModel()::where('status', 4)->where('hidden', false)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
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
                        if ($record->createdBy && $record->createdBy->roles->contains('name', 'creator') && $record->createdBy->is_priority) {
                            return $state . ' ⭐';
                        }
                        return $state;
                    })
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->formatStateUsing(function ($state) {
                        $statuses = [
                            0 => 'Chờ duyệt',
                            1 => 'Đã duyệt',
                            2 => 'Từ chối',
                            3 => 'Hủy',
                            4 => 'Hỗ trợ',
                        ];
                        return $statuses[$state] ?? 'Chờ duyệt';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'warning',
                        '1' => 'success',
                        '2' => 'danger',
                        '3' => 'gray',
                        '4' => 'info',
                        default => 'gray',
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
                        
                        $statuses = [
                            0 => 'Chờ duyệt',
                            1 => 'Đã duyệt',
                            2 => 'Từ chối',
                            3 => 'Hủy',
                            4 => 'Hỗ trợ',
                        ];
                        
                        $statusColors = [
                            0 => 'warning',
                            1 => 'success',
                            2 => 'danger',
                            3 => 'gray',
                            4 => 'info',
                        ];
                        
                        // Lấy tất cả support logs
                        $supportLogs = $record->supportLogs()->with('user')->latest()->get();
                        
                        return view('filament.resources.profile.modal-content', [
                            'record' => $record,
                            'statuses' => $statuses,
                            'statusColors' => $statusColors,
                            'supportLogs' => $supportLogs,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Đóng')
                    ->modalActions([
                        Tables\Actions\Action::make('resolve_support')
                            ->label('Xong')
                            ->icon('heroicon-m-check-circle')
                            ->color('success')
                            ->requiresConfirmation()
                            ->modalHeading('Xác nhận hoàn thành hỗ trợ')
                            ->modalDescription(function (?Profile $record) {
                                return $record ? 'Bạn có chắc chắn muốn đánh dấu hoàn thành hỗ trợ cho hồ sơ #' . $record->code . '?' : 'Bạn có chắc chắn muốn đánh dấu hoàn thành hỗ trợ?';
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
                                
                                // Chỉ hiển thị khi status là hỗ trợ (4) và user có permission approve_profile
                                return $record->status == 4 && $user->hasPermissionTo('approve_profile');
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
                                
                                // Cập nhật status về "Đã duyệt" (1)
                                $record->update([
                                    'status' => 1, // Đã duyệt
                                    'approved_at' => now(),
                                    'approved_by' => $user->id,
                                ]);

                                Notification::make()
                                    ->title('Đã hoàn thành hỗ trợ')
                                    ->body('Hồ sơ #' . $record->code . ' đã được đánh dấu hoàn thành hỗ trợ và chuyển về trạng thái "Đã duyệt".')
                                    ->success()
                                    ->send();
                                    
                                // Redirect về trang support profiles sau khi hoàn thành
                                return redirect()->to('/admin/support-profiles');
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
                        
                        // Người tạo chỉ xem hồ sơ của mình
                        if ($user->hasRole('creator')) {
                            return $record->created_by == $user->id && $record->status != 3;
                        }
                        
                        // Người duyệt có thể xem hồ sơ chờ duyệt
                        if ($user->hasRole('approver')) {
                            return $record->status == 0 || $record->status == 4;
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
            'index' => Pages\ListSupportProfiles::route('/'),
        ];
    }
}
