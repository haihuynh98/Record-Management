<button
    type="button"
    class="relative inline-flex items-center justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0"
    x-data="{
        unreadCount: {{ $unreadNotificationsCount }},
        init() {
            this.$wire.on('notifications-count-updated', (count) => {
                this.unreadCount = count;
            });
        }
    }"
    x-on:click="$dispatch('open-modal', { id: 'database-notifications' })"
>
    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" clip-rule="evenodd" />
    </svg>
    
    <span
        x-show="unreadCount > 0"
        x-text="unreadCount"
        class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
    ></span>
    
    <span class="sr-only">Thông báo</span>
</button>
