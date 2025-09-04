<div class="space-y-6">
    <!-- Thông tin cơ bản -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gray-50 p-4 rounded-lg" x-data="{ copied: false }">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-500">Mã hồ sơ</label>
                <button 
                    type="button"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                    x-on:click="navigator.clipboard.writeText('#{{ $record->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    title="Sao chép mã hồ sơ"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <p class="text-lg font-semibold text-gray-900">#{{ $record->code }}</p>
                <span x-show="copied" x-transition class="text-green-500 text-sm">Đã copy!</span>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg" x-data="{ copied: false }">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-500">ID nhân vật</label>
                <button 
                    type="button"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                    x-on:click="navigator.clipboard.writeText('{{ $record->character_id ?? 'N/A' }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    title="Sao chép ID nhân vật"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <p class="text-lg font-semibold text-blue-600">{{ $record->character_id ?? 'N/A' }}</p>
                <span x-show="copied" x-transition class="text-green-500 text-sm">Đã copy!</span>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <label class="text-sm font-medium text-gray-500">Trạng thái</label>
            <div class="mt-1">
                <span class="inline-flex items-center rounded-full text-xs font-medium bg-{{ $statusColors[$record->status] }}-100 text-{{ $statusColors[$record->status] }}-800">
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

    <!-- Nội dung hỗ trợ (nếu có) -->
    @if($record->status === 4 && $supportLogs->count() > 0)
    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
        <label class="text-sm font-medium text-blue-700 mb-3 block">Lịch sử yêu cầu hỗ trợ</label>
        
        <div class="space-y-3">
            @foreach($supportLogs as $index => $supportLog)
            <div class="bg-white p-3 rounded border border-blue-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-blue-600">
                        Yêu cầu #{{ $supportLogs->count() - $index }}
                    </span>
                    <span class="text-xs text-gray-500">
                        {{ \Carbon\Carbon::parse($supportLog->created_at)->format('d/m/Y H:i:s') }}
                    </span>
                </div>
                <div class="mb-2">
                    <p class="text-sm text-gray-800">{{ $supportLog->support_message }}</p>
                </div>
                <div class="text-xs text-gray-500">
                    <span>Người yêu cầu: {{ $supportLog->user->username ?? 'N/A' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>

// Clear session khi modal đóng (chỉ cho hồ sơ chờ duyệt)
@if($record->status === 0)
window.addEventListener('beforeunload', function() {
    fetch('/admin/profiles/{{ $record->id }}/clear-session', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    });
});

// Không cần Livewire events, chỉ dùng polling mechanism
@endif

// Force close modal khi status changed (for production)
const originalStatus = {{ $record->status }};
const checkStatusChange = setInterval(() => {
    fetch('/admin/profiles/{{ $record->id }}/status', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status !== originalStatus) {
            clearInterval(checkStatusChange);
            // Status changed, close modal
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
    })
    .catch(error => {
        console.log('Status check error:', error);
    });
}, 2000); // Check every 2 seconds

// Clean up interval when modal closes
window.addEventListener('beforeunload', function() {
    clearInterval(checkStatusChange);
});
</script>


