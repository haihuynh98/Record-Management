<?php

namespace App\Filament\Auth;

use App\Models\SystemSetting;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Illuminate\Validation\ValidationException;
use Filament\Pages\Auth\Login as BaseAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;

class Login extends BaseAuth
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getLoginFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('Tên đăng nhập')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }


    protected function throwFailureValidationException(): never
    {
        // Kiểm tra nếu hệ thống đang chặn login
        if (SystemSetting::isLoginBlocked()) {
            throw ValidationException::withMessages([
                'data.login' => 'Đang hạn chế đăng nhập, xin hãy liên hệ admin!',
            ]);
        }

        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    public function mount(): void
    {
        parent::mount();

        // Kiểm tra nếu hệ thống đang chặn login và hiển thị thông báo
        if (SystemSetting::isLoginBlocked()) {
            $this->addError('data.login', 'Đang hạn chế đăng nhập, xin hãy liên hệ admin!');
        }
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        // Kiểm tra nếu hệ thống đang chặn login
        if (SystemSetting::isLoginBlocked()) {
            // Tìm user bằng email
            $user = Auth::getProvider()->retrieveByCredentials([
                'email' => $data['login'],
            ]);
            
            // Nếu user tồn tại và có quyền thì cho phép đăng nhập
            if ($user) {
                // Super admin luôn có quyền
                if ($user->roles->contains('name', 'super_admin')) {
                    return [
                        'email' => $data['login'],
                        'password' => $data['password'],
                    ];
                }
                
                // Kiểm tra quyền page_SystemConfiguration hoặc manage_system_configuration
                if (Gate::forUser($user)->allows('page_SystemConfiguration') || Gate::forUser($user)->allows('manage_system_configuration')) {
                    return [
                        'email' => $data['login'],
                        'password' => $data['password'],
                    ];
                }
            }
            
            // Nếu không có quyền thì từ chối
            throw ValidationException::withMessages([
                'data.login' => 'Đang hạn chế đăng nhập, xin hãy liên hệ admin!',
            ]);
        }
        
        // Nếu hệ thống không bị chặn thì xử lý bình thường
        return [
            'email' => $data['login'],
            'password' => $data['password'],
        ];
    }
}
