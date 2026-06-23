<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Welcome Header -->
        <div class="bg-gradient-to-r from-primary-800 via-primary-700 to-primary-900 rounded-xl p-6 text-white shadow-lg ring-1 ring-warning-500/30">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Chào mừng đến với Fastcall CRM</h1>
                    <p class="text-primary-100 mt-2">Quản lý và theo dõi hồ sơ một cách hiệu quả</p>
                </div>
                <div class="hidden md:block">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('filament.admin.resources.profiles.create') }}" class="group">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow duration-200 group-hover:border-primary-300 dark:group-hover:border-primary-600">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center group-hover:bg-primary-200 dark:group-hover:bg-primary-800 transition-colors duration-200">
                            <svg class="w-6 h-6 text-primary-700 dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors duration-200">Tạo hồ sơ mới</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Thêm hồ sơ mới vào hệ thống</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('filament.admin.pages.profile-statistics') }}" class="group">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow duration-200 group-hover:border-warning-300 dark:group-hover:border-warning-600">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900 rounded-lg flex items-center justify-center group-hover:bg-warning-200 dark:group-hover:bg-warning-800 transition-colors duration-200">
                            <svg class="w-6 h-6 text-warning-700 dark:text-warning-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-warning-700 dark:group-hover:text-warning-300 transition-colors duration-200">Xem thống kê</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Theo dõi hiệu suất xử lý hồ sơ</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('filament.admin.resources.profiles.index') }}" class="group">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow duration-200 group-hover:border-primary-300 dark:group-hover:border-primary-600">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-primary-50 dark:bg-primary-950 rounded-lg flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900 transition-colors duration-200">
                            <svg class="w-6 h-6 text-primary-600 dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-300 transition-colors duration-200">Quản lý hồ sơ</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Xem và chỉnh sửa tất cả hồ sơ</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Widgets - Only show if user has permission -->
        @if(auth()->user()->can('widget_StatsOverview'))
            <div class="space-y-6">
                {{ $this->widgets['stats-overview'] }}
                {{ $this->widgets['profile-chart'] }}
            </div>
        @endif
    </div>
</x-filament-panels::page>
