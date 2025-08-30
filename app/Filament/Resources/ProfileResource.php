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
            'cancel',
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
                        // Disable trong màn hình edit và view
                        if ($context == 'edit' || $context == 'view') {
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
                Forms\Components\TextInput::make('character_id')
                    ->label('ID nhân vật')
                    ->required()
                    ->maxLength(100)
                    ->helperText('Nhập ID nhân vật (cho phép chữ và số)')
                    ->disabled(fn (string $context) => $context == 'view'),
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Lý do từ chối')
                    ->placeholder('Nhập lý do từ chối hồ sơ...')
                    ->minLength(10)
                    ->maxLength(500)
                    ->visible(function (string $context, $record) {
                        // Hiển thị ở màn hình edit và view khi status là reject (2)
                        return ($context == 'edit' || $context == 'view') && $record && $record->status == 2;
                    })
                    ->disabled(fn (string $context) => $context == 'view')
                    ->helperText('Chỉ hiển thị khi hồ sơ bị từ chối'),
                // Thêm các field thông tin bổ sung cho trang view
                Forms\Components\TextInput::make('status')
                    ->label('Trạng thái')
                    ->disabled()
                    ->visible(fn (string $context) => $context == 'view')
                    ->formatStateUsing(function ($state) {
                        $statuses = [
                            0 => 'Chờ duyệt',
                            1 => 'Đã duyệt',
                            2 => 'Từ chối',
                            3 => 'Hủy',
                        ];
                        return $statuses[$state] ?? 'Chờ duyệt';
                    }),

                Forms\Components\TextInput::make('createdBy.username')
                    ->label('Người tạo')
                    ->disabled()
                    ->visible(fn (string $context) => $context == 'view'),
                Forms\Components\TextInput::make('approvedBy.username')
                    ->label('Người duyệt')
                    ->disabled()
                    ->visible(fn (string $context, $record) => $context == 'view' && $record && in_array($record->status, [1, 2, 3])),
                Forms\Components\TextInput::make('created_at')
                    ->label('Ngày tạo')
                    ->disabled()
                    ->visible(fn (string $context) => $context == 'view')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '';
                        if (is_string($state)) {
                            return \Carbon\Carbon::parse($state)->format('d/m/Y H:i:s');
                        }
                        return $state->format('d/m/Y H:i:s');
                    }),
                Forms\Components\TextInput::make('approved_at')
                    ->label('Ngày duyệt')
                    ->disabled()
                    ->visible(fn (string $context, $record) => $context == 'view' && $record && in_array($record->status, [1, 2, 3]))
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '';
                        if (is_string($state)) {
                            return \Carbon\Carbon::parse($state)->format('d/m/Y H:i:s');
                        }
                        return $state->format('d/m/Y H:i:s');
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->poll('10s')
            ->recordUrl(null)
            ->actions([
                \Filament\Tables\Actions\Action::make('view')
                    ->label('Xem chi tiết')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(function (Profile $record) {
                        return 'Chi tiết hồ sơ #' . $record->code;
                    })
                    ->modalContent(function (Profile $record) {
                        // Chỉ check session cho hồ sơ chưa duyệt
                        if ($record->status !== 1) {
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
                        ];
                        
                        $statusColors = [
                            0 => 'warning',
                            1 => 'success',
                            2 => 'danger',
                            3 => 'gray',
                        ];
                        
                        return view('filament.resources.profile.modal-content', [
                            'record' => $record,
                            'statuses' => $statuses,
                            'statusColors' => $statusColors,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Đóng')
                    ->modalActions([
                        
                        \Filament\Tables\Actions\Action::make('approve')
                            ->label('Duyệt')
                            ->icon('heroicon-m-check')
                            ->color('success')
                            ->requiresConfirmation()
                            ->modalHeading('Xác nhận duyệt hồ sơ')
                            ->modalDescription(function (Profile $record) {
                                return 'Bạn có chắc chắn muốn duyệt hồ sơ #' . $record->code . '?';
                            })
                            ->modalSubmitActionLabel('Có, duyệt hồ sơ')
                            ->modalCancelActionLabel('Không, hủy bỏ')
                            ->visible(function (?Profile $record) {
                                if (!$record) {
                                    return false;
                                }
                                
                                $user = auth()->user();
                                if (!$user) {
                                    return false;
                                }
                                
                                // Chỉ check permissions và status, không gọi handleViewSession ở đây
                                return $user->hasPermissionTo('approve_profile') && $record->status === 0;
                            })
                            ->action(function (Profile $record) {
                                $user = auth()->user();
                                
                                // Kiểm tra lại session trước khi thực hiện action
                                if (!$record->canBeInteracted()) {
                                    Notification::make()
                                        ->title('Không thể thực hiện')
                                        ->body('Hồ sơ đang được xử lý bởi người khác')
                                        ->warning()
                                        ->send();
                                    return;
                                }
                                
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
                            ->modalHeading('Xác nhận từ chối hồ sơ')
                            ->modalDescription(function (Profile $record) {
                                return 'Bạn có chắc chắn muốn từ chối hồ sơ #' . $record->code . '?';
                            })
                            ->modalSubmitActionLabel('Có, từ chối hồ sơ')
                            ->modalCancelActionLabel('Không, hủy bỏ')
                            ->form([
                                Forms\Components\Textarea::make('rejection_reason')
                                    ->label('Lý do từ chối')
                                    ->required()
                                    ->placeholder('Nhập lý do từ chối hồ sơ...')
                                    ->minLength(10)
                                    ->maxLength(500),
                            ])
                                                        ->visible(function (?Profile $record) {
                                if (!$record) {
                                    return false;
                                }
                                
                                $user = auth()->user();
                                if (!$user) {
                                    return false;
                                }
                                
                                // Chỉ check permissions và status, không gọi handleViewSession ở đây
                                return $user->hasPermissionTo('reject_profile') && $record->status === 0;
                            })
                            ->action(function (Profile $record, array $data) {
                                $user = auth()->user();
                                
                                // Kiểm tra lại session trước khi thực hiện action
                                if (!$record->canBeInteracted()) {
                                    Notification::make()
                                        ->title('Không thể thực hiện')
                                        ->body('Hồ sơ đang được xử lý bởi người khác')
                                        ->warning()
                                        ->send();
                                    return;
                                }
                                
                                $record->update([
                                    'status' => 2, // Từ chối
                                    'rejection_reason' => $data['rejection_reason'],
                                    'approved_at' => now(),
                                    'approved_by' => $user->id,
                                ]);

                                // Chỉ hiển thị toast notification cho người thực hiện hành động
                                // Database notification sẽ được gửi qua Listener cho người tạo hồ sơ
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
                            ->modalHeading('Xác nhận nộp lại hồ sơ')
                            ->modalDescription(function (Profile $record) {
                                return 'Bạn có chắc chắn muốn nộp lại hồ sơ #' . $record->code . '?';
                            })
                            ->modalSubmitActionLabel('Có, nộp lại')
                            ->modalCancelActionLabel('Không, hủy bỏ')
                            ->visible(function (?Profile $record) {
                                if (!$record) {
                                    return false;
                                }
                                
                                $user = auth()->user();
                                if (!$user) {
                                    return false;
                                }
                                
                                return $user->hasPermissionTo('resubmit_profile') && 
                                       $record->status === 2 && 
                                       ($record->created_by == $user->id || $user->hasRole(['admin', 'super_admin']));
                            })
                            ->action(function (Profile $record) {
                                $record->update([
                                    'status' => 0, // Chờ duyệt
                                    'rejection_reason' => null, // Xóa lý do từ chối
                                ]);

                                Notification::make()
                                    ->title('Đã nộp lại hồ sơ thành công')
                                    ->body('Hồ sơ #' . $record->code . ' đã được nộp lại.')
                                    ->success()
                                    ->send();
                            }),
                        \Filament\Tables\Actions\Action::make('cancel')
                            ->label('Hủy')
                            ->icon('heroicon-m-x-circle')
                            ->color('gray')
                            ->requiresConfirmation()
                            ->modalHeading('Xác nhận hủy hồ sơ')
                            ->modalDescription(function (Profile $record) {
                                return 'Bạn có chắc chắn muốn hủy hồ sơ #' . $record->code . '? Hành động này không thể hoàn tác.';
                            })
                            ->modalSubmitActionLabel('Có, hủy hồ sơ')
                            ->modalCancelActionLabel('Không, giữ lại')
                            ->visible(function (?Profile $record) {
                                if (!$record) {
                                    return false;
                                }
                                
                                $user = auth()->user();
                                if (!$user) {
                                    return false;
                                }
                                
                                return $user->hasPermissionTo('cancel_profile') && $record->status === 2;
                            })
                            ->action(function (Profile $record) {
                                $user = auth()->user();
                                
                                $record->update([
                                    'status' => 3, // Hủy
                                    'approved_at' => now(),
                                    'approved_by' => $user->id,
                                ]);

                                // Chỉ hiển thị toast notification cho người thực hiện hành động
                                // Database notification sẽ được gửi qua Listener cho người tạo hồ sơ
                                Notification::make()
                                    ->title('Đã hủy hồ sơ')
                                    ->body('Hồ sơ #' . $record->code . ' đã được hủy thành công.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->visible(function (Profile $record) {
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
                            return $record->status == 0;
                        }
                        
                        return false;
                    }),
            ])
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
                    ->html()
                    ->searchable()
                    ->copyable()
                    ->copyableState(fn (string $state): string => "#{$state}")
                    ->copyMessage('Đã sao chép mã hồ sơ vào clipboard')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('character_id')
                    ->label('ID nhân vật')
                    ->searchable()
                    ->copyable()
                    ->copyableState(fn (string $state): string => $state)
                    ->copyMessage('Đã sao chép ID nhân vật vào clipboard')
                    ->copyMessageDuration(1500),
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
                            3 => 'Hủy',
                        ];
                        return $statuses[$state] ?? 'Chờ duyệt';
                    })
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            0 => 'warning',
                            1 => 'success',
                            2 => 'danger',
                            3 => 'gray',
                            default => 'warning',
                        };
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tạo lúc'),
            ])
            ->filters([
                //
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

        // Người tạo chỉ xem hồ sơ của mình và không xem hồ sơ đã hủy
        if ($user->hasRole('creator')) {
            return $query->where('created_by', $user->id)->where('status', '!=', 3);
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
            // 'view' => Pages\ViewProfile::route('/{record}/view'), // Không sử dụng nữa - đã thay thế bằng modal popup
            'edit' => Pages\EditProfile::route('/{record}/edit'),
        ];
    }
}
