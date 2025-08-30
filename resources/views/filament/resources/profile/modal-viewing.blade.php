<div class="space-y-6">
    <!-- Thông báo có người đang xử lý -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0 mr-2">
                <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <div class="flex-1 pl-2">
                <h3 class="text-lg font-medium text-yellow-800">
                    Hồ sơ đang được xử lý
                </h3>
                <p class="mt-1 text-sm text-yellow-700">
                    @if($record->viewingUser)
                        <strong>{{ $record->viewingUser->username }}</strong> đang xem và xử lý hồ sơ này.
                    @else
                        Có người đang xem và xử lý hồ sơ này.
                    @endif
                </p>
                <p class="mt-1 text-xs text-yellow-600">
                    @if($record->viewing_started_at)
                        Bắt đầu lúc: {{ $record->viewing_started_at->format('H:i:s d/m/Y') }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Thông tin cơ bản của hồ sơ (chỉ hiển thị những thông tin không nhạy cảm) -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Thông tin cơ bản</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-500">Mã hồ sơ</label>
                <div class="flex items-center space-x-2">
                    <p class="text-lg font-semibold text-gray-900">#{{ $record->code }}</p>
                    <button 
                        type="button"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                        onclick="copyToClipboard('#{{ $record->code }}', 'Mã hồ sơ')"
                        title="Sao chép mã hồ sơ"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div>
                <label class="text-sm font-medium text-gray-500">ID nhân vật</label>
                <div class="flex items-center space-x-2">
                    <p class="text-lg font-semibold text-blue-600">{{ $record->character_id ?? 'N/A' }}</p>
                    @if($record->character_id)
                    <button 
                        type="button"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                        onclick="copyToClipboard('{{ $record->character_id }}', 'ID nhân vật')"
                        title="Sao chép ID nhân vật"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                    @endif
                </div>
            </div>
            
            <div>
                <label class="text-sm font-medium text-gray-500">Trạng thái</label>
                <div class="mt-1">
                    @php
                        $statuses = [
                            0 => 'Chờ duyệt',
                            1 => 'Đã duyệt', 
                            2 => 'Từ chối',
                            3 => 'Hủy',
                        ];
                        $statusColors = [
                            0 => 'warning',
                            1 => 'success',
                            2 => 'danger',
                            3 => 'gray',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColors[$record->status] }}-100 text-{{ $statusColors[$record->status] }}-800">
                        {{ $statuses[$record->status] }}
                    </span>
                </div>
            </div>
            
            <div>
                <label class="text-sm font-medium text-gray-500">Người tạo</label>
                <p class="text-sm text-gray-900">
                    {{ $record->createdBy->username ?? 'N/A' }}
                    @if($record->createdBy && $record->createdBy->is_priority)
                        <span class="ml-1 text-yellow-500">⭐</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Thông tin thời gian -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Thông tin thời gian</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-500">Ngày tạo</label>
                <p class="text-sm text-gray-900">{{ $record->created_at ? \Carbon\Carbon::parse($record->created_at)->format('d/m/Y H:i:s') : 'N/A' }}</p>
            </div>
            
            @if(auth()->user()->hasRole(['admin', 'super_admin']) && $record->status === 1 && $record->approved_at)
            <div>
                <label class="text-sm font-medium text-gray-500">Ngày duyệt</label>
                <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($record->approved_at)->format('d/m/Y H:i:s') }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Hướng dẫn -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-medium text-blue-800">Hướng dẫn</h4>
                <p class="mt-1 text-sm text-blue-700">
                    Vui lòng thử lại sau hoặc liên hệ với người đang xử lý để được hỗ trợ. 
                    Trang sẽ tự động làm mới sau 10 giây.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Copy to clipboard function
function copyToClipboard(text, fieldName) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification(fieldName, true);
        }).catch(() => {
            showNotification(fieldName, false);
        });
    } else {
        const tempInput = document.createElement('input');
        tempInput.value = text;
        tempInput.style.position = 'absolute';
        tempInput.style.left = '-9999px';
        document.body.appendChild(tempInput);
        tempInput.select();
        tempInput.setSelectionRange(0, 99999);
        
        try {
            document.execCommand('copy');
            showNotification(fieldName, true);
        } catch (err) {
            showNotification(fieldName, false);
        }
        
        document.body.removeChild(tempInput);
    }
}

// Show notification function
function showNotification(fieldName, success = true) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
        success ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    
    notification.innerHTML = `
        <div class='flex items-center space-x-2'>
            <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='${
                    success 
                        ? 'M5 13l4 4L19 7' 
                        : 'M6 18L18 6M6 6l12 12'
                }'></path>
            </svg>
            <span>${success ? `Đã sao chép ${fieldName} vào clipboard` : `Không thể sao chép ${fieldName}`}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Auto refresh sau 10 giây
setTimeout(function() {
    window.location.reload();
}, 10000);
</script>
