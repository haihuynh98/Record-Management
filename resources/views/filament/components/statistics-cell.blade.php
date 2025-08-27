<div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-100 shadow-sm">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-700">Số lượng: </span>
        </div>
        <span class="text-lg font-bold text-blue-600">{{ $count }}</span>
    </div>
    
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
            <span class="text-xs text-gray-600">Giá trị: </span>
        </div>
        <span class="text-sm font-semibold text-green-600">{{ number_format($amount, 0, ',', ',') }} ₫</span>
    </div>
</div>
