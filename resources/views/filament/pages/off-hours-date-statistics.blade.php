<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Bộ lọc thống kê</h3>
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Từ ngày
                        </label>
                        <input 
                            type="date" 
                            id="start_date"
                            wire:model.live="startDate"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Đến ngày
                        </label>
                        <input 
                            type="date" 
                            id="end_date"
                            wire:model.live="endDate"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <x-filament::button 
                        wire:click="resetDates"
                        color="gray"
                        icon="heroicon-o-arrow-path"
                    >
                        Đặt lại
                    </x-filament::button>
                    
                    <x-filament::button 
                        wire:click="filter"
                        color="primary"
                        icon="heroicon-o-magnifying-glass"
                    >
                        Tìm kiếm
                    </x-filament::button>
                </div>
            </div>
        </div>

        <!-- Statistics Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Thống kê hồ sơ ngoài giờ theo ngày
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Hiển thị số lượng hồ sơ được tạo ngoài giờ làm việc trong khoảng thời gian: 
                    <span class="font-medium">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</span> 
                    đến 
                    <span class="font-medium">{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
                </p>
                @php
                    $startTime = \App\Models\SystemSetting::getOffHoursStart() ?? '22:00';
                    $endTime = \App\Models\SystemSetting::getOffHoursEnd() ?? '02:00';
                @endphp
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                    Khung giờ ngoài giờ làm việc: <span class="font-medium">{{ $startTime }} → {{ $endTime }}</span>
                </p>
            </div>
            
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>

