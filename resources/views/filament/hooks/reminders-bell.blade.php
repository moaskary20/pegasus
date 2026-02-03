@auth
@php
    $service = app(\App\Services\ReminderService::class);
    $counts = $service->getReminderCounts(auth()->user());
    $reminders = $service->generateReminders(auth()->user())->take(5);
@endphp

<div 
    x-data="{
        open: false,
        count: {{ $counts['total'] }},
        reminders: @js($reminders->values()->all()),
        loading: false,
        
        init() {
            this.startPolling();
        },
        
        startPolling() {
            setInterval(() => {
                this.fetchCounts();
            }, 60000); // Refresh every minute
        },
        
        async fetchCounts() {
            try {
                const response = await fetch('/api/reminders/counts');
                if (!response.ok) return;
                const data = await response.json();
                this.count = data.total || 0;
            } catch (e) {
                console.error('Error fetching reminder counts:', e);
            }
        },
        
        async fetchReminders() {
            this.loading = true;
            try {
                const response = await fetch('/api/reminders');
                if (!response.ok) return;
                const data = await response.json();
                this.reminders = (data.reminders || []).slice(0, 5);
                this.count = data.meta?.total || 0;
            } catch (e) {
                console.error('Error fetching reminders:', e);
            }
            this.loading = false;
        }
    }"
    x-init="init()"
    @click.away="open = false"
    class="relative flex items-center"
>
    {{-- Clock Button --}}
    <button 
        @click="open = !open; if(open) fetchReminders()"
        class="relative p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 transition-colors rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20"
    >
        <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        
        {{-- Badge --}}
        <span 
            x-show="count > 0"
            x-text="count > 99 ? '99+' : count"
            x-cloak
            class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 flex items-center justify-center text-[10px] font-bold text-white bg-amber-500 rounded-full"
        ></span>
    </button>
    
    {{-- Dropdown --}}
    <div 
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute left-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50"
        style="top: 100%;"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-amber-500 to-orange-500">
            <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                <span>⏰</span>
                التذكيرات
            </h3>
            <span class="text-xs text-white/80" x-text="count + ' تذكير'"></span>
        </div>
        
        {{-- Reminders List --}}
        <div class="max-h-80 overflow-y-auto">
            <template x-if="loading">
                <div class="px-4 py-8 text-center">
                    <div class="animate-spin inline-block w-6 h-6 border-2 border-amber-500 border-t-transparent rounded-full"></div>
                </div>
            </template>
            
            <template x-if="!loading && reminders.length === 0">
                <div class="px-4 py-8 text-center">
                    <div class="text-4xl mb-2">🎉</div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">لا توجد تذكيرات</p>
                </div>
            </template>
            
            <template x-for="reminder in reminders" :key="reminder.type + '-' + (reminder.remindable_id || 0)">
                <a 
                    :href="reminder.action_url || '#'"
                    class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors"
                >
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-lg"
                         :class="{
                            'bg-purple-100': reminder.color === 'purple',
                            'bg-blue-100': reminder.color === 'blue',
                            'bg-green-100': reminder.color === 'green',
                            'bg-orange-100': reminder.color === 'orange',
                            'bg-teal-100': reminder.color === 'teal',
                            'bg-yellow-100': reminder.color === 'yellow',
                            'bg-red-100': reminder.color === 'red',
                            'bg-indigo-100': reminder.color === 'indigo'
                         }"
                         x-text="reminder.icon">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="reminder.title"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2" x-text="reminder.message"></p>
                    </div>
                </a>
            </template>
        </div>
        
        {{-- Footer --}}
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
            <a 
                href="{{ route('filament.admin.pages.reminders') }}"
                class="block text-center text-sm text-amber-600 hover:text-amber-700 font-medium"
            >
                عرض جميع التذكيرات
            </a>
        </div>
    </div>
</div>
@endauth
