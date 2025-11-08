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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Schema;
use App\Jobs\SendResubmittedProfileNotification;
use App\Jobs\SendSupportRequestNotification;
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
            'view_awaiting_approval',
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('view_any_profile') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermissionTo('create_profile') ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->hasPermissionTo('view_profile') ?? false;
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
                        'unique' => 'Mã hồ sơ này đã tồn tại. Vui lòng chọn mã khác.',
                    ])
                    ->maxLength(64),
                Forms\Components\TextInput::make('character_id')
                    ->label('ID nhân vật')
                    ->required()
                    ->maxLength(100)
                    ->helperText('Nhập ID nhân vật (cho phép chữ và số)')
                    ->disabled(fn(string $context) => $context == 'view'),
                
                // Section hồ sơ giao lưu
                Forms\Components\Section::make('Thông tin giao lưu')
                    ->schema([
                        Forms\Components\Checkbox::make('is_exchange')
                            ->label('Hồ sơ giao lưu')
                            ->reactive()
                            ->columnSpanFull()
                            ->disabled(fn(string $context) => $context == 'view'),
                        
                        Forms\Components\TextInput::make('exchange_profile_code')
                            ->label('ID Hồ Sơ Giao Lưu')
                            ->prefix('#')
                            ->inputMode('numeric')
                            ->extraInputAttributes([
                                'pattern' => '[0-9]*',
                                'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57',
                                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")'
                            ])
                            ->rules(['regex:/^[0-9]+$/'])
                            ->validationMessages([
                                'regex' => 'ID Hồ Sơ Giao Lưu chỉ cho phép nhập số (0-9) và không có dấu cách.',
                            ])
                            ->maxLength(64)
                            ->visible(fn(callable $get) => $get('is_exchange'))
                            ->disabled(fn(string $context) => $context == 'view'),
                        
                            Forms\Components\TextInput::make('exchange_character_id')
                            ->label('ID Giao Lưu')
                            ->maxLength(100)
                            ->helperText('Nhập ID nhân vật (cho phép chữ và số)')
                            ->visible(fn(callable $get) => $get('is_exchange'))
                            ->disabled(fn(string $context) => $context == 'view'),
                        
                    ])
                    ->columns(2)
                    ->visible(function (string $context) {
                        // Hiển thị ở màn hình create, edit và view
                        return in_array($context, ['create', 'edit', 'view']);
                    }),
                

                Forms\Components\Textarea::make('notes')
                    ->label('Chú thích')
                    ->placeholder('Nhập chú thích cho hồ sơ (không bắt buộc)...')
                    ->maxLength(1000)
                    ->rows(3)
                    ->visible(function (string $context) {
                        // Hiển thị ở màn hình create, edit và view
                        return in_array($context, ['create', 'edit', 'view']);
                    })
                    ->disabled(fn(string $context) => $context == 'view')
                    ->helperText('Thêm chú thích hoặc ghi chú về hồ sơ này'),
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Lý do từ chối')
                    ->placeholder('Nhập lý do từ chối hồ sơ...')
                    ->minLength(10)
                    ->maxLength(500)
                    ->visible(function (string $context, $record) {
                        // Hiển thị ở màn hình edit và view khi status là reject (2)
                        return ($context == 'edit' || $context == 'view') && $record && $record->status == 2;
                    })
                    ->disabled(fn(string $context) => $context == 'view')
                    ->helperText('Chỉ hiển thị khi hồ sơ bị từ chối'),
                // Thêm các field thông tin bổ sung cho trang view
                Forms\Components\TextInput::make('status')
                    ->label('Trạng thái')
                    ->disabled()
                    ->visible(fn(string $context) => $context == 'view')
                    ->formatStateUsing(function ($state) {
                        $statuses = [
                            0 => 'Chờ duyệt',
                            1 => 'Đã duyệt',
                            2 => 'Từ chối',
                            3 => 'Hủy',
                            4 => 'Hỗ trợ',
                            5 => 'Chờ',
                            6 => 'Nộp lại',
                        ];
                        return $statuses[$state] ?? 'Chờ duyệt';
                    }),

                Forms\Components\TextInput::make('createdBy.username')
                    ->label('Người tạo')
                    ->disabled()
                    ->visible(fn(string $context) => $context == 'view'),
                Forms\Components\TextInput::make('approvedBy.username')
                    ->label('Người duyệt')
                    ->disabled()
                    ->visible(fn(string $context, $record) => $context == 'view' && $record && in_array($record->status, [1, 2, 3])),
                Forms\Components\TextInput::make('created_at')
                    ->label('Ngày tạo')
                    ->disabled()
                    ->visible(fn(string $context) => $context == 'view')
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
                    ->visible(fn(string $context, $record) => $context == 'view' && $record && in_array($record->status, [1, 2, 3]))
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '';
                        if (is_string($state)) {
                            return \Carbon\Carbon::parse($state)->format('d/m/Y H:i:s');
                        }
                        return $state->format('d/m/Y H:i:s');
                    }),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('visible_at', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->recordUrl(null)
            ->actions([
                \Filament\Tables\Actions\Action::make('support')
                    ->label('Hỗ trợ')
                    ->icon('heroicon-m-phone')
                    ->color('warning')
                    ->modalHeading('Yêu cầu hỗ trợ')
                    ->modalDescription(function (?Profile $record) {
                        return $record ? 'Vui lòng mô tả chi tiết vấn đề bạn cần hỗ trợ với hồ sơ #' . $record->code . ':' : 'Vui lòng mô tả chi tiết vấn đề bạn cần hỗ trợ:';
                    })
                    ->form([
                        Forms\Components\Textarea::make('support_message')
                            ->label('Nội dung yêu cầu hỗ trợ')
                            ->required()
                            ->placeholder('Nhập chi tiết vấn đề bạn cần hỗ trợ...')
                            ->minLength(10)
                            ->maxLength(1000)
                            ->helperText('Mô tả chi tiết vấn đề để chúng tôi có thể hỗ trợ bạn tốt nhất'),
                    ])
                    ->visible(function (?Profile $record) {
                        if (!$record) {
                            return false;
                        }

                        $user = auth()->user();
                        if (!$user) {
                            return false;
                        }

                        // Chỉ hiển thị button hỗ trợ khi status là "Đã duyệt" (1)
                        return $record->status == 1;
                    })
                    ->action(function (?Profile $record, array $data) {
                        if (!$record) {
                            Notification::make()
                                ->title('Lỗi')
                                ->body('Không thể tìm thấy hồ sơ')
                                ->danger()
                                ->send();
                            return;
                        }

                        $user = auth()->user();

                        // Cập nhật status của profile thành hỗ trợ (4)
                        $record->update([
                            'status' => 4, // Hỗ trợ
                        ]);

                        // Lưu support message vào bảng support_logs
                        \App\Models\SupportLog::create([
                            'profile_id' => $record->id,
                            'user_id' => $user->id,
                            'support_message' => $data['support_message'],
                        ]);

                        // Dispatch job để gửi thông báo hỗ trợ
                        SendSupportRequestNotification::dispatch($record, $data['support_message'], $user);

                        Notification::make()
                            ->title('Đã gửi yêu cầu hỗ trợ')
                            ->body('Yêu cầu hỗ trợ của bạn đã được gửi thành công. Trạng thái hồ sơ đã được cập nhật thành "Hỗ trợ".')
                            ->success()
                            ->send();
                    }),
                \Filament\Tables\Actions\Action::make('view')
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

                        // Đảm bảo password tồn tại
                        $record->ensurePasswordExists();

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
                            6 => 'Nộp lại',
                        ];

                        // Kiểm tra hồ sơ giao lưu cho status = 0 hoặc 6 (Chờ duyệt hoặc Nộp lại)
                        if ($record->status == 0 &&$record->is_exchange) {
                            $statuses[$record->status] = 'Giao Lưu';
                        }

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
                            5 => 'warning', // Màu cam cho trạng thái "Chờ"
                            6 => 'info', // Màu xanh dương cho trạng thái "Nộp lại"
                        ];

                        // Cập nhật màu cho hồ sơ giao lưu
                        if ($record->status == 0 && $record->is_exchange) {
                            $statusColors[0] = 'info';
                        }

                        // Cập nhật màu cho trạng thái "Đủ điều kiện"
                        if ($record->status == 5 && $record->approved_at && $record->approved_at instanceof \Carbon\Carbon) {
                            $hoursSinceUpdate = $record->approved_at->diffInHours(now());
                            if ($hoursSinceUpdate >= 6) {
                                $statusColors[5] = 'success';
                            }
                        }

                        // Lấy tất cả support logs nếu status là hỗ trợ (4)
                        $supportLogs = collect();
                        if ($record->status == 4) {
                            $supportLogs = $record->supportLogs()->with('user')->latest()->get();
                        }

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

                        \Filament\Tables\Actions\Action::make('approve')
                            ->label('Duyệt')
                            ->icon('heroicon-m-check')
                            ->color('success')
                            ->requiresConfirmation()
                            ->modalHeading('Xác nhận duyệt hồ sơ')
                            ->modalDescription(function (?Profile $record) {
                                return $record ? 'Bạn có chắc chắn muốn duyệt hồ sơ #' . $record->code . '?' : 'Bạn có chắc chắn muốn duyệt hồ sơ?';
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
                                return $user->hasPermissionTo('approve_profile') && in_array($record->status, [0, 6]);
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

                                // Lấy password từ database (đã được generate khi tạo hồ sơ)
                                $password = $record->password;

                                $record->update([
                                    'status' => 1, // Đã duyệt
                                    'password_lock' => true, // Lock password sau khi approve
                                    'approved_at' => now(),
                                    'approved_by' => $user->id,
                                ]);

                                // Clear viewing session sau khi approve
                                $record->clearViewingSession();

                                Notification::make()
                                    ->title('Đã duyệt hồ sơ')
                                    ->body('Hồ sơ #' . $record->code . ' đã được duyệt với mật khẩu: ' . $password)
                                    ->success()
                                    ->send();

                                // Redirect dựa trên quyền của user
                                $redirectUrl = $user->hasPermissionTo('view_any_profile')
                                    ? '/admin/profiles'
                                    : '/admin/awaiting-approval-profiles';
                                return redirect()->to($redirectUrl);
                            }),
                        \Filament\Tables\Actions\Action::make('reject')
                            ->label('Từ chối')
                            ->icon('heroicon-m-x-mark')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->modalHeading('Xác nhận từ chối hồ sơ')
                            ->modalDescription(function (?Profile $record) {
                                return $record ? 'Bạn có chắc chắn muốn từ chối hồ sơ #' . $record->code . '?' : 'Bạn có chắc chắn muốn từ chối hồ sơ?';
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
                                return $user->hasPermissionTo('reject_profile') && in_array($record->status, [0, 6]);
                            })
                            ->action(function (?Profile $record, array $data) {
                                if (!$record) {
                                    Notification::make()
                                        ->title('Lỗi')
                                        ->body('Không thể tìm thấy hồ sơ')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                $user = auth()->user();

                                $record->update([
                                    'status' => 2, // Từ chối
                                    'rejection_reason' => $data['rejection_reason'],
                                    'approved_at' => now(),
                                    'approved_by' => $user->id,
                                ]);

                                // Clear viewing session sau khi reject
                                $record->clearViewingSession();

                                // Chỉ hiển thị toast notification cho người thực hiện hành động
                                // Database notification sẽ được gửi qua Listener cho người tạo hồ sơ
                                Notification::make()
                                    ->title('Đã từ chối hồ sơ')
                                    ->success()
                                    ->send();

                                // Kiểm tra nếu user không có quyền resubmit hoặc không phải người tạo thì redirect
                                $user = auth()->user();
                                $canResubmit = $user->hasPermissionTo('resubmit_profile');

                                if (!$canResubmit) {
                                    // Redirect dựa trên quyền của user
                                    $redirectUrl = $user->hasPermissionTo('view_any_profile')
                                        ? '/admin/profiles'
                                        : '/admin/awaiting-approval-profiles';
                                    return redirect()->to($redirectUrl);
                                }
                            }),
                        \Filament\Tables\Actions\Action::make('resubmit')
                            ->label('Nộp lại')
                            ->icon('heroicon-m-arrow-path')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Xác nhận nộp lại hồ sơ')
                            ->modalDescription(function (?Profile $record) {
                                return $record ? 'Bạn có chắc chắn muốn nộp lại hồ sơ #' . $record->code . '?' : 'Bạn có chắc chắn muốn nộp lại hồ sơ?';
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
                                    $record->status == 2 &&
                                    ($record->created_by == $user->id || $user->hasRole(['admin', 'super_admin']));
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

                                // Clear viewing session trước khi resubmit
                                $record->clearViewingSession();

                                $record->update([
                                    'status' => 6, // Nộp lại
                                    'rejection_reason' => null, // Xóa lý do từ chối
                                ]);

                                SendResubmittedProfileNotification::dispatch($record);

                                Notification::make()
                                    ->title('Đã nộp lại hồ sơ thành công')
                                    ->body('Hồ sơ #' . $record->code . ' đã được chuyển sang trạng thái "Nộp lại".')
                                    ->success()
                                    ->send();
                            }),
                        \Filament\Tables\Actions\Action::make('cancel')
                            ->label('Hủy')
                            ->icon('heroicon-m-x-circle')
                            ->color('gray')
                            ->requiresConfirmation()
                            ->modalHeading('Xác nhận hủy hồ sơ')
                            ->modalDescription(function (?Profile $record) {
                                return $record ? 'Bạn có chắc chắn muốn hủy hồ sơ #' . $record->code . '? Hành động này không thể hoàn tác.' : 'Bạn có chắc chắn muốn hủy hồ sơ? Hành động này không thể hoàn tác.';
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

                                return $user->hasPermissionTo('cancel_profile') && $record->status == 2;
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

                                $record->update([
                                    'status' => 3, // Hủy
                                    'approved_at' => now(),
                                    'approved_by' => $user->id,
                                ]);

                                // Clear viewing session sau khi cancel
                                $record->clearViewingSession();

                                // Chỉ hiển thị toast notification cho người thực hiện hành động
                                // Database notification sẽ được gửi qua Listener cho người tạo hồ sơ
                                Notification::make()
                                    ->title('Đã hủy hồ sơ')
                                    ->body('Hồ sơ #' . $record->code . ' đã được hủy thành công.')
                                    ->success()
                                    ->send();

                                // Redirect về trang profile sau khi hủy
                                return redirect()->to('/admin/profiles');
                            }),

                        \Filament\Tables\Actions\Action::make('resolve_support')
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

                                // Redirect về trang profile sau khi hoàn thành
                                return redirect()->to('/admin/profiles');
                            }),

                        \Filament\Tables\Actions\Action::make('set_pending')
                            ->label('Chờ')
                            ->icon('heroicon-m-clock')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Xác nhận chuyển trạng thái')
                            ->modalDescription(function (?Profile $record) {
                                return $record ? 'Bạn có chắc chắn muốn chuyển hồ sơ #' . $record->code . ' sang trạng thái "Chờ"?' : 'Bạn có chắc chắn muốn chuyển hồ sơ sang trạng thái "Chờ"?';
                            })
                            ->modalSubmitActionLabel('Có, chuyển trạng thái')
                            ->modalCancelActionLabel('Không, hủy bỏ')
                            ->visible(function (?Profile $record) {
                                if (!$record) {
                                    return false;
                                }

                                $user = auth()->user();
                                if (!$user) {
                                    return false;
                                }

                                // Chỉ hiển thị khi status là "Chờ duyệt" (0) và user có permission approve_profile
                                return in_array($record->status, [0, 6]) && $user->hasPermissionTo('approve_profile');
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

                                // Cập nhật status từ "Chờ duyệt" (0) sang "Chờ" (5)
                                $record->update([
                                    'status' => 5, // Chờ
                                    'approved_at' => now(),
                                    'approved_by' => $user->id,
                                ]);

                                // Clear viewing session sau khi chuyển trạng thái
                                $record->clearViewingSession();

                                // Dispatch job để gửi thông báo Telegram sau thời gian delay
                                $delayMinutes = (int) config('services.telegram.delayed_notification_minutes', 360);
                                \App\Jobs\SendDelayedTelegramNotification::dispatch($record, 'pending_reminder', $user->is_priority ?? false)
                                    ->delay(now()->addMinutes($delayMinutes));

                                Notification::make()
                                    ->title('Đã chuyển trạng thái')
                                    ->body('Hồ sơ #' . $record->code . ' đã được chuyển sang trạng thái "Chờ". Thông báo nhắc nhở sẽ được gửi sau ' . $delayMinutes . ' phút.')
                                    ->success()
                                    ->send();

                                // Redirect dựa trên quyền của user
                                $redirectUrl = $user->hasPermissionTo('view_any_profile')
                                    ? '/admin/profiles'
                                    : '/admin/awaiting-approval-profiles';
                                return redirect()->to($redirectUrl);
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
                            return in_array($record->status, [0, 6]);
                        }

                        return false;
                    }),
                \Filament\Tables\Actions\Action::make('delete')
                    ->label('Xóa')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn(Profile $record) => $record->delete())
                    ->visible(function () {
                        $user = auth()->user();
                        return $user?->hasRole(['admin', 'super_admin']);
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
                    ->copyableState(fn(string $state): string => "#{$state}")
                    ->copyMessage('Đã sao chép mã hồ sơ vào clipboard')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('character_id')
                    ->label('ID nhân vật')
                    ->searchable()
                    ->copyable()
                    ->copyableState(fn(string $state): string => $state)
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
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->formatStateUsing(function ($state, $record) {
                        // Kiểm tra hồ sơ giao lưu cho status = 0 hoặc 6 (Chờ duyệt hoặc Nộp lại)
                        if (in_array($state, [0, 6]) && $record->is_exchange) {
                            return 'Giao Lưu';
                        }

                        $statuses = [
                            0 => 'Chờ duyệt',
                            1 => 'Đã duyệt',
                            2 => 'Từ chối',
                            3 => 'Hủy',
                            4 => 'Hỗ trợ',
                            5 => 'Chờ',
                            6 => 'Nộp lại',
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
                        // Màu tím cho hồ sơ giao lưu ở trạng thái chờ duyệt hoặc nộp lại
                        if (in_array($state, [0]) && $record->is_exchange) {
                            return 'info';
                        }

                        // Kiểm tra trạng thái "Đủ điều kiện" cho status = 5 (Chờ)
                        if ($state == 5 && $record->approved_at && $record->approved_at instanceof \Carbon\Carbon) {
                            $hoursSinceUpdate = $record->approved_at->diffInHours(now());
                            if ($hoursSinceUpdate >= 6) {
                                return 'success'; // Màu xanh lá cho "Đủ điều kiện"
                            }
                        }

                        return match ($state) {
                            0 => 'warning',
                            1 => 'success',
                            2 => 'danger',
                            3 => 'gray',
                            4 => 'info',
                            5 => 'warning', // Màu cam cho trạng thái "Chờ"
                            6 => 'info', // Màu xanh dương cho trạng thái "Nộp lại"
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

        // Lọc bỏ các hồ sơ đã bị ẩn
        $query->where('hidden', false);

        // Chỉ hiển thị các hồ sơ đã đến thời gian visible
        $query->where('visible_at', '<=', now());

        // Super admin và admin có thể xem tất cả
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) return $query;

        // Người tạo chỉ xem hồ sơ của mình và không xem hồ sơ đã hủy
        if ($user->hasRole('creator')) {
            return $query->where('created_by', $user->id)->whereIn('status', [0, 1, 2, 4, 5, 6]);
        }

        // Người duyệt chỉ xem hồ sơ chờ duyệt VÀ đã đến thời gian hiển thị
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
