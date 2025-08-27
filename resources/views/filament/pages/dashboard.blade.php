<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Welcome Header -->
        <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Chào mừng đến với Hệ thống Quản lý Hồ sơ</h1>
                    <p class="text-blue-100 mt-2">Quản lý và theo dõi hồ sơ một cách hiệu quả</p>
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
                <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 group-hover:border-blue-300">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors duration-200">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200">Tạo hồ sơ mới</h3>
                            <p class="text-sm text-gray-600">Thêm hồ sơ mới vào hệ thống</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('filament.admin.pages.profile-statistics') }}" class="group">
                <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 group-hover:border-green-300">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors duration-200">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 group-hover:text-green-600 transition-colors duration-200">Xem thống kê</h3>
                            <p class="text-sm text-gray-600">Theo dõi hiệu suất xử lý hồ sơ</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('filament.admin.resources.profiles.index') }}" class="group">
                <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 group-hover:border-purple-300">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors duration-200">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 group-hover:text-purple-600 transition-colors duration-200">Quản lý hồ sơ</h3>
                            <p class="text-sm text-gray-600">Xem và chỉnh sửa tất cả hồ sơ</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Widgets - Only show if user has permission -->
        @if(auth()->user()->can('view_dashboard_charts'))
            <div class="space-y-6">
                {{ $this->widgets['stats-overview'] }}
                {{ $this->widgets['profile-chart'] }}
            </div>
        @endif
    </div>
</x-filament-panels::page>
