<div class="space-y-6" x-data="{ loading: false }" x-init="loading = false">
    <!-- Loading indicator -->
    <div x-show="loading" class="flex items-center justify-center py-8">
        <div class="flex items-center space-x-2">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
            <span class="text-sm text-gray-600">Đang tải thông tin...</span>
        </div>
    </div>

    <!-- Content -->
    <div x-show="!loading" class="space-y-6">
        <!-- Thông tin cơ bản -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <label class="text-sm font-medium text-gray-500">Mã hồ sơ</label>
                <p class="text-lg font-semibold text-gray-900">#{{ $record->code }}</p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <label class="text-sm font-medium text-gray-500">Giá trị hồ sơ</label>
                <p class="text-lg font-semibold text-green-600">{{ number_format($record->amount, 0, ',', ',') }} VNĐ</p>
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
</div>
