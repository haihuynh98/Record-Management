<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CheckLoginBlock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra nếu hệ thống đang chặn login
        if (SystemSetting::isLoginBlocked()) {
            // Nếu đang cố gắng đăng nhập (POST request)
            if ($request->isMethod('post') && 
                ($request->routeIs('filament.admin.auth.login') || 
                 $request->is('admin/login') || 
                 $request->is('admin/auth/login'))) {
                
                // Kiểm tra xem có phải super admin hoặc user có quyền cấu hình hệ thống không
                $email = $request->input('data.login');
                if ($email) {
                    $user = Auth::getProvider()->retrieveByCredentials(['email' => $email]);
                    if ($user) {
                        // Super admin luôn có quyền
                        if ($user->roles->contains('name', 'super_admin')) {
                            return $next($request);
                        }
                        
                        // Kiểm tra quyền page_SystemConfiguration hoặc manage_system_configuration
                        if (Gate::forUser($user)->allows('page_SystemConfiguration') || Gate::forUser($user)->allows('manage_system_configuration')) {
                            return $next($request);
                        }
                    }
                }
                
                // Từ chối đăng nhập cho tất cả user khác
                return back()->withErrors([
                    'data.login' => 'Đang hạn chế đăng nhập, xin hãy liên hệ admin!'
                ])->withInput();
            }
            
            // Nếu đang ở trang login (GET request) thì cho phép truy cập
            if ($request->routeIs('filament.admin.auth.login') || 
                $request->is('admin/login') || 
                $request->is('admin/auth/login')) {
                return $next($request);
            }
            
            // Nếu user đã đăng nhập và có quyền quản lý cấu hình hệ thống thì cho phép
            if (Auth::check()) {
                $user = Auth::user();
                
                // Super admin luôn có quyền
                if ($user->roles->contains('name', 'super_admin')) {
                    return $next($request);
                }
                
                // Kiểm tra quyền page_SystemConfiguration hoặc manage_system_configuration
                if (Gate::forUser($user)->allows('page_SystemConfiguration') || Gate::forUser($user)->allows('manage_system_configuration')) {
                    return $next($request);
                }
                
                // Nếu đã đăng nhập nhưng không có quyền thì đăng xuất và redirect về login
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('filament.admin.auth.login')
                    ->withErrors([
                        'email' => 'Đang hạn chế đăng nhập, xin hãy liên hệ admin!'
                    ]);
            }
            
            // Nếu chưa đăng nhập thì redirect về login
            return redirect()->route('filament.admin.auth.login')
                ->withErrors([
                    'email' => 'Đang hạn chế đăng nhập, xin hãy liên hệ admin!'
                ]);
        }
        
        return $next($request);
    }
}
