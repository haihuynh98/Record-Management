<x-filament-panels::page>
    <div class="space-y-6">

        <!-- System Status Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Login Status Card -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Trạng thái Đăng nhập
                        </p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ $this->getLoginBlockStatusText() }}
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center
                            {{ $this->getLoginBlockStatusColor() === 'danger' ? 'bg-red-100 dark:bg-red-900' : 'bg-green-100 dark:bg-green-900' }}">
                            <svg class="w-6 h-6 {{ $this->getLoginBlockStatusColor() === 'danger' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($this->getLoginBlockStatusColor() === 'danger')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                @endif
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Health Card -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Tình trạng Hệ thống
                        </p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-2">
                            Hoạt động
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Users Card -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Người dùng Online
                        </p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-2">
                            {{ \App\Models\User::count() }}
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Authentication Settings -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Cài đặt Xác thực
                        </h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    Chặn đăng nhập hệ thống
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Kiểm soát quyền truy cập vào hệ thống
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $this->getLoginBlockStatusColor() === 'danger' ? 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' : 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' }}">
                                {{ $this->getLoginBlockStatusText() }}
                            </span>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                Thông tin chi tiết:
                            </h4>
                            <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                <li>• Khi chặn: Tất cả user bị đăng xuất, không thể login mới</li>
                                <li>• Khi mở: User có thể đăng nhập bình thường</li>
                                <li>• Super admin luôn có quyền truy cập</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Thông tin Hệ thống
                        </h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Phiên bản Laravel:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ app()->version() }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Môi trường:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ app()->environment() }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Database:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ config('database.default') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Cache Driver:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ config('cache.default') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Off-hours Configuration -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Cấu hình ngoài giờ làm việc
                    </h3>
                </div>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Chọn khoảng thời gian ngoài giờ làm việc. Ví dụ chọn 22:00 đến 02:00 có nghĩa là từ 22h hôm nay đến 02h ngày hôm sau.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bắt đầu</label>
                        <input type="time" wire:model.defer="offHoursStart" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500" />
                        @error('offHoursStart')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kết thúc</label>
                        <input type="time" wire:model.defer="offHoursEnd" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500" />
                        @error('offHoursEnd')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex md:justify-end">
                        <x-filament::button wire:click="saveOffHours" color="primary" icon="heroicon-o-check-circle">
                            Lưu cấu hình
                        </x-filament::button>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    Khoảng thời gian hiện tại: <span class="font-medium text-gray-900 dark:text-white">{{ $this->offHoursStart ?? '22:00' }} → {{ $this->offHoursEnd ?? '02:00' }}</span>
                    @if($this->offHoursStart && $this->offHoursEnd && $this->offHoursStart > $this->offHoursEnd)
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                            (Qua đêm)
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- New Profile Reminder Configuration -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Cấu hình Nhắc nhở Hồ sơ Mới
                    </h3>
                </div>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Cấu hình thời gian gửi thông báo nhắc nhở cho hồ sơ mới <strong>sau khi có hồ sơ mới chờ duyệt</strong>. 
                    Nhập <strong>0</strong> để tắt tính năng này.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            ⏰ Thời gian nhắc (phút)
                        </label>
                        <input 
                            type="number" 
                            wire:model.defer="newProfileReminderMinutes" 
                            min="0" 
                            max="1440" 
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500" 
                            placeholder="60"
                        />
                        @error('newProfileReminderMinutes')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Mặc định: 0 phút (tắt). Ví dụ: 60 = gửi nhắc nhở sau 60 phút kể từ khi hồ sơ visible.
                        </p>
                    </div>
                    <div class="flex md:justify-end">
                        <x-filament::button wire:click="saveNewProfileReminderSettings" color="primary" icon="heroicon-o-check-circle">
                            Lưu cấu hình
                        </x-filament::button>
                    </div>
                </div>

                <!-- Status Display -->
                @if($this->newProfileReminderMinutes !== null)
                    <div class="mt-3 flex items-center space-x-2">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Trạng thái hiện tại:</span>
                        @if($this->newProfileReminderMinutes > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                ✅ Đang bật - {{ $this->newProfileReminderMinutes }} phút
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                                ⭕ Đã tắt
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Warning Section -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Lưu ý quan trọng
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>
                            Các thay đổi cấu hình hệ thống có thể ảnh hưởng đến toàn bộ người dùng. 
                            Vui lòng thận trọng khi thực hiện các thay đổi.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>