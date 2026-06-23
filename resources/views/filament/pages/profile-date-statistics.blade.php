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
                    <button 
                        type="button"
                        wire:click="resetDates"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Đặt lại
                    </button>
                    
                    <button 
                        type="button"
                        wire:click="filter"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Tìm kiếm
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Thống kê hồ sơ theo ngày
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Hiển thị số lượng hồ sơ được tạo trong khoảng thời gian: 
                    <span class="font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</span> 
                    đến 
                    <span class="font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
                </p>
            </div>
            
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
