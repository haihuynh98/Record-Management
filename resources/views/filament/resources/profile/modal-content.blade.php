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
        
        @if($record->is_exchange && $record->exchange_profile_code)
        <div class="bg-gray-50 p-4 rounded-lg" x-data="{ copied: false }">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-purple-700">ID Hồ Sơ Giao Lưu</label>
                <button 
                    type="button"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                    x-on:click="navigator.clipboard.writeText('#{{ $record->exchange_profile_code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    title="Sao chép ID hồ sơ giao lưu"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <p class="text-lg font-semibold text-purple-800">#{{ $record->exchange_profile_code }}</p>
                <span x-show="copied" x-transition class="text-green-500 text-sm">Đã copy!</span>
            </div>
        </div>
        @endif
        
        @if($record->is_exchange && $record->exchange_character_id)
        <div class="bg-gray-50 p-4 rounded-lg" x-data="{ copied: false }">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-purple-700">ID Giao Lưu</label>
                <button 
                    type="button"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                    x-on:click="navigator.clipboard.writeText('{{ $record->exchange_character_id }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    title="Sao chép ID giao lưu"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <p class="text-lg font-semibold text-purple-800">{{ $record->exchange_character_id }}</p>
                <span x-show="copied" x-transition class="text-green-500 text-sm">Đã copy!</span>
            </div>
        </div>
        @endif
    </div>

    <!-- Chú thích (nếu có) -->
    @if($record->notes)
    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
        <label class="text-sm font-medium text-blue-700">Chú thích</label>
        <div class="mt-2">
            <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $record->notes }}</p>
        </div>
    </div>
    @endif

    <!-- Mật khẩu -->
    @if(($record->status == 0 || $record->status == 5 || $record->status == 6) && auth()->user()->hasPermissionTo('approve_profile') && !$record->password_lock)
    <div 
        class="bg-yellow-50 p-4 rounded-lg border border-yellow-200"
        x-data="{
            password: @js($record->password),
            copied: false,
            passwordError: '',
            specialChars: '!@#$%^&*',
            buildRandomPassword() {
                const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const lowercase = 'abcdefghijklmnopqrstuvwxyz';
                const numbers = '0123456789';
                const pool = uppercase + lowercase + numbers + this.specialChars;
                const chars = [
                    uppercase.charAt(Math.floor(Math.random() * uppercase.length)),
                    lowercase.charAt(Math.floor(Math.random() * lowercase.length)),
                    numbers.charAt(Math.floor(Math.random() * numbers.length)),
                    this.specialChars.charAt(Math.floor(Math.random() * this.specialChars.length)),
                ];
                while (chars.length < 8) {
                    chars.push(pool.charAt(Math.floor(Math.random() * pool.length)));
                }
                for (let i = chars.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [chars[i], chars[j]] = [chars[j], chars[i]];
                }
                return chars.join('');
            },
            validatePassword(value) {
                if (!value || value.length < 8) {
                    return 'Mật khẩu phải có ít nhất 8 ký tự';
                }
                if (!/[^a-zA-Z0-9]/.test(value)) {
                    return 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt';
                }
                if (!/\d/.test(value)) {
                    return 'Mật khẩu phải có ít nhất 1 số';
                }
                if (!/[A-Z]/.test(value)) {
                    return 'Mật khẩu phải có ít nhất 1 chữ hoa';
                }
                if (!/[a-z]/.test(value)) {
                    return 'Mật khẩu phải có ít nhất 1 chữ thường';
                }
                return '';
            },
            generateNewPassword() {
                this.password = this.buildRandomPassword();
                this.passwordError = '';
                this.updatePassword();
            },
            updatePassword() {
                const error = this.validatePassword(this.password);
                if (error) {
                    this.passwordError = error;
                    return;
                }
                this.passwordError = '';
                fetch('/admin/profiles/{{ $record->id }}/update-password', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ password: this.password })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success && data.message) {
                        this.passwordError = data.message;
                    }
                })
                .catch(error => console.log('Error updating password:', error));
            }
        }"
        x-init="
            if (!password || password.trim() === '' || validatePassword(password)) {
                generateNewPassword();
            }
        "
    >
        <div class="flex items-center justify-between mb-2">
            <label class="text-sm font-medium text-yellow-700">Mật khẩu</label>
            <button 
                type="button"
                class="text-xs bg-yellow-200 hover:bg-yellow-300 text-yellow-800 px-2 py-1 rounded transition-colors"
                x-on:click="generateNewPassword()"
                title="Tạo mật khẩu mới"
            >
                Tạo mới
            </button>
        </div>
        <div class="flex items-center gap-2">
            <input 
                type="text" 
                x-model="password"
                x-on:input="updatePassword()"
                class="flex-1 px-3 py-2 border border-yellow-300 rounded-md text-sm font-mono bg-white"
                readonly
            />
            <button 
                type="button"
                class="text-yellow-600 hover:text-yellow-800 transition-colors"
                x-on:click="navigator.clipboard.writeText(password); copied = true; setTimeout(() => copied = false, 2000)"
                title="Sao chép mật khẩu"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </button>
            <span x-show="copied" x-transition class="text-green-500 text-sm">Đã copy!</span>
        </div>
        <p class="text-xs text-yellow-600 mt-1">
            Mật khẩu sẽ được lưu khi bạn duyệt hồ sơ
            <span class="block">Yêu cầu: tối thiểu 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt</span>
        </p>
        <p x-show="passwordError" x-text="passwordError" class="text-xs text-red-600 mt-1"></p>
    </div>
    @elseif($record->status == 1 && $record->password)
    <div class="bg-green-50 p-4 rounded-lg border border-green-200" x-data="{ copied: false }">
        <div class="flex items-center justify-between mb-2">
            <label class="text-sm font-medium text-green-700">Mật khẩu</label>
        </div>
        <div class="flex items-center gap-2">
            <span class="flex-1 px-3 py-2 bg-white border border-green-300 rounded-md text-sm font-mono text-green-800">{{ $record->password }}</span>
            <button 
                type="button"
                class="text-green-600 hover:text-green-800 transition-colors"
                x-on:click="navigator.clipboard.writeText('{{ $record->password }}'); copied = true; setTimeout(() => copied = false, 2000)"
                title="Sao chép mật khẩu"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </button>
            <span x-show="copied" x-transition class="text-green-500 text-sm">Đã copy!</span>
        </div>
    </div>
    @endif

    <!-- Mật khẩu giao lưu (chỉ cho hồ sơ giao lưu) -->
    @if($record->is_exchange)
        @if(($record->status == 0 || $record->status == 5 || $record->status == 6) && auth()->user()->hasPermissionTo('approve_profile') && !$record->password_lock)
        <div 
            class="bg-purple-50 p-4 rounded-lg border border-purple-200"
            x-data="{
                exchangePassword: @js($record->exchange_password ?? ''),
                copiedExchange: false,
                exchangePasswordError: '',
                specialChars: '!@#$%^&*',
                init() {
                    if (!this.exchangePassword || this.exchangePassword.trim() === '' || this.validatePassword(this.exchangePassword)) {
                        this.generateNewExchangePassword();
                    }
                },
                buildRandomPassword() {
                    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
                    const numbers = '0123456789';
                    const pool = uppercase + lowercase + numbers + this.specialChars;
                    const chars = [
                        uppercase.charAt(Math.floor(Math.random() * uppercase.length)),
                        lowercase.charAt(Math.floor(Math.random() * lowercase.length)),
                        numbers.charAt(Math.floor(Math.random() * numbers.length)),
                        this.specialChars.charAt(Math.floor(Math.random() * this.specialChars.length)),
                    ];
                    while (chars.length < 8) {
                        chars.push(pool.charAt(Math.floor(Math.random() * pool.length)));
                    }
                    for (let i = chars.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [chars[i], chars[j]] = [chars[j], chars[i]];
                    }
                    return chars.join('');
                },
                validatePassword(value) {
                    if (!value || value.length < 8) {
                        return 'Mật khẩu phải có ít nhất 8 ký tự';
                    }
                    if (!/[^a-zA-Z0-9]/.test(value)) {
                        return 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt';
                    }
                    if (!/\d/.test(value)) {
                        return 'Mật khẩu phải có ít nhất 1 số';
                    }
                    if (!/[A-Z]/.test(value)) {
                        return 'Mật khẩu phải có ít nhất 1 chữ hoa';
                    }
                    if (!/[a-z]/.test(value)) {
                        return 'Mật khẩu phải có ít nhất 1 chữ thường';
                    }
                    return '';
                },
                generateNewExchangePassword() {
                    this.exchangePassword = this.buildRandomPassword();
                    this.exchangePasswordError = '';
                    this.updateExchangePassword();
                },
                updateExchangePassword() {
                    const error = this.validatePassword(this.exchangePassword);
                    if (error) {
                        this.exchangePasswordError = error;
                        return;
                    }
                    this.exchangePasswordError = '';
                    fetch('/admin/profiles/{{ $record->id }}/update-exchange-password', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ exchange_password: this.exchangePassword })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success && data.message) {
                            this.exchangePasswordError = data.message;
                        }
                    })
                    .catch(error => console.log('Error updating exchange password:', error));
                }
            }"
        >
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium text-purple-700">Mật khẩu giao lưu</label>
                <button 
                    type="button"
                    class="text-xs bg-purple-200 hover:bg-purple-300 text-purple-800 px-2 py-1 rounded transition-colors"
                    x-on:click="generateNewExchangePassword()"
                    title="Tạo mật khẩu giao lưu mới"
                >
                    Tạo mới
                </button>
            </div>
            <div class="flex items-center gap-2">
                <input 
                    type="text" 
                    x-model="exchangePassword"
                    x-on:input="updateExchangePassword()"
                    class="flex-1 px-3 py-2 border border-purple-300 rounded-md text-sm font-mono bg-white"
                    readonly
                />
                <button 
                    type="button"
                    class="text-purple-600 hover:text-purple-800 transition-colors"
                    x-on:click="navigator.clipboard.writeText(exchangePassword); copiedExchange = true; setTimeout(() => copiedExchange = false, 2000)"
                    title="Sao chép mật khẩu giao lưu"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
                <span x-show="copiedExchange" x-transition class="text-green-500 text-sm">Đã copy!</span>
            </div>
            <p class="text-xs text-purple-600 mt-1">
                Mật khẩu giao lưu sẽ được lưu khi bạn duyệt hồ sơ
                <span class="block">Yêu cầu: tối thiểu 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt</span>
            </p>
            <p x-show="exchangePasswordError" x-text="exchangePasswordError" class="text-xs text-red-600 mt-1"></p>
        </div>
        @elseif($record->status == 1 && $record->exchange_password)
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200" x-data="{ copiedExchange: false }">
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium text-purple-700">Mật khẩu giao lưu</label>
            </div>
            <div class="flex items-center gap-2">
                <span class="flex-1 px-3 py-2 bg-white border border-purple-300 rounded-md text-sm font-mono text-purple-800">{{ $record->exchange_password }}</span>
                <button 
                    type="button"
                    class="text-purple-600 hover:text-purple-800 transition-colors"
                    x-on:click="navigator.clipboard.writeText('{{ $record->exchange_password }}'); copiedExchange = true; setTimeout(() => copiedExchange = false, 2000)"
                    title="Sao chép mật khẩu giao lưu"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
                <span x-show="copiedExchange" x-transition class="text-green-500 text-sm">Đã copy!</span>
            </div>
        </div>
        @endif
    @endif

    <!-- Thông tin thời gian -->
    <div class="grid grid-cols-1 gap-4">
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

// Clear viewing session khi modal đóng (chỉ cho hồ sơ chờ duyệt)
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


