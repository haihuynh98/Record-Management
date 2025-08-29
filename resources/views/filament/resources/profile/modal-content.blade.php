<div class="space-y-6">
    <!-- Thông tin cơ bản -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-500">Mã hồ sơ</label>
                <button 
                    type="button"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                    onclick="copyToClipboard('#{{ $record->code }}', 'Mã hồ sơ')"
                    title="Sao chép mã hồ sơ"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
            <p class="text-lg font-semibold text-gray-900">#{{ $record->code }}</p>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-500">ID nhân vật</label>
                <button 
                    type="button"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                    onclick="copyToClipboard('{{ $record->character_id ?? 'N/A' }}', 'ID nhân vật')"
                    title="Sao chép ID nhân vật"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
            <p class="text-lg font-semibold text-blue-600">{{ $record->character_id ?? 'N/A' }}</p>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <label class="text-sm font-medium text-gray-500">Trạng thái</label>
            <div class="mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColors[$record->status] }}-100 text-{{ $statusColors[$record->status] }}-800">
                    {{ $statuses[$record->status] }}
                </span>

            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <label class="text-sm font-medium text-gray-500">Người tạo</label>
            <p class="text-sm text-gray-900">
                {{ $record->createdBy->username ?? 'N/A' }}
                @if($record->createdBy && $record->createdBy->is_priority)
                    <span class="ml-1 text-yellow-500">⭐</span>
                @endif
            </p>
        </div>
    </div>

    <!-- Thông tin thời gian -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gray-50 p-4 rounded-lg">
            <label class="text-sm font-medium text-gray-500">Ngày tạo</label>
            <p class="text-sm text-gray-900">{{ $record->created_at ? \Carbon\Carbon::parse($record->created_at)->format('d/m/Y H:i:s') : 'N/A' }}</p>
        </div>
        
        @if(auth()->user()->hasRole(['admin', 'super_admin']) && $record->status === 1 && $record->approved_at)
        <div class="bg-gray-50 p-4 rounded-lg">
            <label class="text-sm font-medium text-gray-500">Ngày duyệt</label>
            <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($record->approved_at)->format('d/m/Y H:i:s') }}</p>
        </div>
        @endif
    </div>

    <!-- Người duyệt - chỉ hiển thị cho admin khi hồ sơ đã duyệt -->
    @if(auth()->user()->hasRole(['admin', 'super_admin']) && $record->status === 1 && $record->approvedBy)
    <div class="bg-gray-50 p-4 rounded-lg">
        <label class="text-sm font-medium text-gray-500">Người duyệt</label>
        <p class="text-sm text-gray-900">{{ $record->approvedBy->username }}</p>
    </div>
    @endif

    <!-- Lý do từ chối (nếu có) -->
    @if($record->status === 2 && $record->rejection_reason)
    <div class="bg-red-50 p-4 rounded-lg border border-red-200">
        <label class="text-sm font-medium text-red-700">Lý do từ chối</label>
        <div class="mt-2">
            <p class="text-sm text-red-800">{{ $record->rejection_reason }}</p>
        </div>
    </div>
    @endif
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
</script>


