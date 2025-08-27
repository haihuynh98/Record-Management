<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Forms\Set;

class EditProfile extends EditRecord
{
    protected static string $resource = ProfileResource::class;
    
    public function getHeading(): string
    {
        return 'Chỉnh sửa hồ sơ';
    }

    public function getTitle(): string
    {
        return 'Chỉnh sửa hồ sơ';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['code'])) {
            $data['code'] = preg_replace('/[^A-Za-z0-9]/', '', preg_replace('/\s+/', '', (string) $data['code']));
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $record = $this->getRecord();
        
        // Kiểm tra quyền duyệt
        $canApprove = $user && $user->can('approve', $record);
        $canShowApproveActions = $record && $record->status === 0; // Chỉ hiển thị cho hồ sơ chờ duyệt
        
        // Kiểm tra quyền từ chối
        $canReject = $user && $user->can('reject', $record);
        
        // Kiểm tra quyền nộp lại
        $canResubmit = $user && $user->can('resubmit', $record) && $record && $record->status === 2 && $record->created_by === $user->id;
        
        $actions = [];
        
        // Thêm nút duyệt nếu có quyền
        if ($canApprove && $canShowApproveActions) {
            $actions[] = Actions\Action::make('approve')
                ->label('Duyệt')
                ->color('success')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->modalHeading('Xác nhận duyệt hồ sơ')
                ->modalDescription('Bạn có chắc chắn muốn duyệt hồ sơ này?')
                ->modalSubmitActionLabel('Duyệt')
                ->action(function () {
                    $user = auth()->user();
                    $record = $this->getRecord();
                    
                    $record->update([
                        'status' => 1, // Đã duyệt
                        'approved_at' => now(),
                        'approved_by' => $user->id,
                    ]);

                    Notification::make()
                        ->title('Đã duyệt hồ sơ thành công')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                });
        }
        
        // Thêm nút từ chối nếu có quyền
        if ($canReject && $canShowApproveActions) {
            $actions[] = Actions\Action::make('reject')
                ->label('Từ chối')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->modalHeading('Xác nhận từ chối hồ sơ')
                ->modalDescription('Bạn có chắc chắn muốn từ chối hồ sơ này?')
                ->modalSubmitActionLabel('Từ chối')
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Lý do từ chối')
                        ->required()
                        ->placeholder('Nhập lý do từ chối hồ sơ...')
                        ->minLength(10)
                        ->maxLength(500),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();
                    $record = $this->getRecord();
                    
                    $record->update([
                        'status' => 2, // Từ chối
                        'rejection_reason' => $data['rejection_reason'],
                        'approved_at' => now(),
                        'approved_by' => $user->id,
                    ]);

                    Notification::make()
                        ->title('Đã từ chối hồ sơ')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                });
        }
        
        // Thêm nút nộp lại nếu có quyền
        if ($canResubmit) {
            $actions[] = Actions\Action::make('resubmit')
                ->label('Nộp lại')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Xác nhận nộp lại hồ sơ')
                ->modalDescription('Bạn có chắc chắn muốn nộp lại hồ sơ này?')
                ->modalSubmitActionLabel('Nộp lại')
                ->action(function () {
                    $record = $this->getRecord();
                    
                    $record->update([
                        'status' => 0, // Chờ duyệt
                        'rejection_reason' => null, // Xóa lý do từ chối
                    ]);

                    Notification::make()
                        ->title('Đã nộp lại hồ sơ thành công')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                });
        }
        
        return $actions;
    }
}
