<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class ViewProfile extends ViewRecord
{
    protected static string $resource = ProfileResource::class;

    protected function authorizeAccess(): void
    {
        $record = $this->getRecord();
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        // Kiểm tra quyền xem hồ sơ
        if (!$user->can('view', $record)) {
            abort(403);
        }

        // Kiểm tra quyền theo role
        if ($user->hasRole('creator')) {
            if ($record->created_by !== $user->id || $record->status === 3) {
                abort(403);
            }
        } elseif ($user->hasRole('approver')) {
            if ($record->status !== 0) {
                abort(403);
            }
        }
        // Admin và super_admin có thể xem tất cả
    }

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $record = $this->getRecord();

        $actions = [];

        // Nút Duyệt - chỉ hiển thị cho hồ sơ chờ duyệt
        if ($user?->hasPermissionTo('approve_profile') && $record->status === 0) {
            $actions[] = Actions\Action::make('approve')
                ->label('Duyệt')
                ->icon('heroicon-m-check')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Xác nhận duyệt hồ sơ')
                ->modalDescription('Bạn có chắc chắn muốn duyệt hồ sơ #' . $record->code . '?')
                ->modalSubmitActionLabel('Có, duyệt hồ sơ')
                ->modalCancelActionLabel('Không, hủy bỏ')
                ->action(function () {
                    $user = Auth::user();
                    $record = $this->getRecord();
                    
                    $record->update([
                        'status' => 1, // Đã duyệt
                        'approved_at' => now(),
                        'approved_by' => $user->id,
                    ]);

                    Notification::make()
                        ->title('Đã duyệt hồ sơ')
                        ->body('Hồ sơ #' . $record->code . ' đã được duyệt thành công.')
                        ->success()
                        ->send();

                    $this->redirect(ProfileResource::getUrl('index'));
                });
        }

        // Nút Từ chối - chỉ hiển thị cho hồ sơ chờ duyệt
        if ($user?->hasPermissionTo('reject_profile') && $record->status === 0) {
            $actions[] = Actions\Action::make('reject')
                ->label('Từ chối')
                ->icon('heroicon-m-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Xác nhận từ chối hồ sơ')
                ->modalDescription('Bạn có chắc chắn muốn từ chối hồ sơ #' . $record->code . '?')
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
                ->action(function (array $data) {
                    $user = Auth::user();
                    $record = $this->getRecord();
                    
                    $record->update([
                        'status' => 2, // Từ chối
                        'rejection_reason' => $data['rejection_reason'],
                        'approved_at' => now(),
                        'approved_by' => $user->id,
                    ]);

                    Notification::make()
                        ->title('Đã từ chối hồ sơ')
                        ->body('Hồ sơ #' . $record->code . ' đã bị từ chối.')
                        ->success()
                        ->send();

                    $this->redirect(ProfileResource::getUrl('index'));
                });
        }

        // Nút Hủy - chỉ hiển thị cho hồ sơ chờ duyệt
        if ($user?->hasPermissionTo('cancel_profile') && $record->status === 0) {
            $actions[] = Actions\Action::make('cancel')
                ->label('Hủy')
                ->icon('heroicon-m-x-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Xác nhận hủy hồ sơ')
                ->modalDescription(function () {
                    $record = $this->getRecord();
                    return 'Bạn có chắc chắn muốn hủy hồ sơ #' . $record->code . '? Hành động này không thể hoàn tác.';
                })
                ->modalSubmitActionLabel('Có, hủy hồ sơ')
                ->modalCancelActionLabel('Không, giữ lại')
                ->action(function () {
                    $user = Auth::user();
                    $record = $this->getRecord();
                    
                    $record->update([
                        'status' => 3, // Hủy
                        'approved_at' => now(),
                        'approved_by' => $user->id,
                    ]);

                    Notification::make()
                        ->title('Đã hủy hồ sơ')
                        ->body('Hồ sơ #' . $record->code . ' đã được hủy thành công.')
                        ->success()
                        ->send();

                    $this->redirect(ProfileResource::getUrl('index'));
                });
        }

        // Nút Nộp lại - chỉ hiển thị cho hồ sơ bị từ chối
        if ($user?->hasPermissionTo('resubmit_profile') && $record->status === 2) {
            // Kiểm tra xem có phải người tạo hoặc admin/super_admin không
            if ($record->created_by == $user->id || $user->hasRole(['admin', 'super_admin'])) {
                $actions[] = Actions\Action::make('resubmit')
                    ->label('Nộp lại')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Xác nhận nộp lại hồ sơ')
                    ->modalDescription('Bạn có chắc chắn muốn nộp lại hồ sơ #' . $record->code . '?')
                    ->modalSubmitActionLabel('Có, nộp lại')
                    ->modalCancelActionLabel('Không, hủy bỏ')
                    ->action(function () {
                        $record = $this->getRecord();
                        
                        $record->update([
                            'status' => 0, // Chờ duyệt
                            'rejection_reason' => null, // Xóa lý do từ chối
                        ]);

                        Notification::make()
                            ->title('Đã nộp lại hồ sơ thành công')
                            ->body('Hồ sơ #' . $record->code . ' đã được nộp lại.')
                            ->success()
                            ->send();

                        $this->redirect(ProfileResource::getUrl('index'));
                    });
            }
        }

        return $actions;
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        $user = auth()->user();
        $record = $this->getRecord();
        
        $schema = [
            \Filament\Forms\Components\TextInput::make('code')
                ->label('Mã hồ sơ')
                ->disabled()
                ->prefix('#'),
            \Filament\Forms\Components\TextInput::make('amount')
                ->label('Giá trị hồ sơ')
                ->disabled()
                ->numeric()
                ->formatStateUsing(function ($state) {
                    if (!$state) return '';
                    return number_format($state, 0, ',', ',') . ' VNĐ';
                }),
        ];

        // Chỉ hiển thị lý do từ chối nếu hồ sơ bị từ chối
        if ($record && $record->status === 2) {
            $schema[] = \Filament\Forms\Components\Textarea::make('rejection_reason')
                ->label('Lý do từ chối')
                ->disabled();
        }

        // Chỉ hiển thị thông tin bổ sung khi có quyền duyệt
        if ($user?->hasPermissionTo('approve_profile')) {
            $schema[] = \Filament\Forms\Components\TextInput::make('status')
                ->label('Trạng thái')
                ->disabled()
                ->formatStateUsing(function ($state) {
                    $statuses = [
                        0 => 'Chờ duyệt',
                        1 => 'Đã duyệt',
                        2 => 'Từ chối',
                        3 => 'Hủy',
                    ];
                    return $statuses[$state] ?? 'Chờ duyệt';
                });

            $schema[] = \Filament\Forms\Components\TextInput::make('createdBy.username')
                ->label('Người tạo')
                ->disabled();

            // Chỉ hiển thị người duyệt và ngày duyệt khi hồ sơ đã được xử lý
            if ($record && in_array($record->status, [1, 2, 3])) {
                $schema[] = \Filament\Forms\Components\TextInput::make('approvedBy.username')
                    ->label('Người duyệt')
                    ->disabled();

                $schema[] = \Filament\Forms\Components\TextInput::make('approved_at')
                    ->label('Ngày duyệt')
                    ->disabled()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '';
                        if (is_string($state)) {
                            return \Carbon\Carbon::parse($state)->format('d/m/Y H:i:s');
                        }
                        return $state->format('d/m/Y H:i:s');
                    });
            }

            $schema[] = \Filament\Forms\Components\TextInput::make('created_at')
                ->label('Ngày tạo')
                ->disabled()
                ->formatStateUsing(function ($state) {
                    if (!$state) return '';
                    if (is_string($state)) {
                        return \Carbon\Carbon::parse($state)->format('d/m/Y H:i:s');
                    }
                    return $state->format('d/m/Y H:i:s');
                });
        }

        return $form->schema($schema);
    }
}
