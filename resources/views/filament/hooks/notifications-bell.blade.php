@auth
<div 
    x-data="{
        open: false,
        count: 0,
        notifications: [],
        loading: false,
        pollingInterval: null,
        
        init() {
            this.fetchNotifications();
            this.startPolling();
        },
        
        startPolling() {
            this.pollingInterval = setInterval(() => {
                this.fetchCount();
            }, 30000);
        },
        
        async fetchNotifications() {
            try {
                const response = await fetch('/api/notifications?unread=1&per_page=5');
                if (!response.ok) return;
                const data = await response.json();
                this.notifications = (data.notifications || []).map(n => ({
                    id: n.id,
                    type: n.data?.type || 'general',
                    title: n.data?.title || 'إشعار جديد',
                    message: n.data?.message || '',
                    created_at: this.formatDate(n.created_at),
                }));
                this.count = data.meta?.unread_count || 0;
            } catch (e) {
                console.error('Error fetching notifications:', e);
            }
        },
        
        async fetchCount() {
            try {
                const response = await fetch('/api/notifications/unread-count');
                if (!response.ok) return;
                const data = await response.json();
                this.count = data.count || 0;
            } catch (e) {
                console.error('Error fetching count:', e);
            }
        },
        
        async markAsRead(id) {
            try {
                await fetch(`/api/notifications/${id}/read`, { 
                    method: 'POST', 
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                        'Content-Type': 'application/json'
                    } 
                });
                this.notifications = this.notifications.filter(n => n.id !== id);
                this.count = Math.max(0, this.count - 1);
            } catch (e) {
                console.error('Error marking as read:', e);
            }
        },
        
        async markAllAsRead() {
            try {
                await fetch('/api/notifications/read-all', { 
                    method: 'POST', 
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                        'Content-Type': 'application/json'
                    } 
                });
                this.notifications = [];
                this.count = 0;
            } catch (e) {
                console.error('Error marking all as read:', e);
            }
        },
        
        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return 'الآن';
            if (diff < 3600) return Math.floor(diff / 60) + ' دقيقة';
            if (diff < 86400) return Math.floor(diff / 3600) + ' ساعة';
            return Math.floor(diff / 86400) + ' يوم';
        },
        
        getIcon(type) {
            const icons = {
                'new_enrollment': 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
                'enrollment_confirmed': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'course_completed': 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                'new_lesson': 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z',
                'new_question': 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'question_answered': 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
                'lesson_comment': 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z',
                'order_confirmed': 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
                'new_sale': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            };
            return icons[type] || 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
        },
        
        getColor(type) {
            const colors = {
                'new_enrollment': 'text-blue-500',
                'enrollment_confirmed': 'text-green-500',
                'course_completed': 'text-amber-500',
                'new_lesson': 'text-purple-500',
                'new_question': 'text-orange-500',
                'question_answered': 'text-teal-500',
                'lesson_comment': 'text-indigo-500',
                'order_confirmed': 'text-emerald-500',
                'new_sale': 'text-green-600',
            };
            return colors[type] || 'text-gray-500';
        }
    }"
    x-init="init()"
    @click.away="open = false"
    class="relative flex items-center"
>
    {{-- Bell Button --}}
    <button 
        @click="open = !open; if(open) fetchNotifications()"
        class="relative p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
    >
        <svg style="width:10px;height:10px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        
        {{-- Badge --}}
        <span 
            x-show="count > 0"
            x-text="count > 99 ? '99+' : count"
            x-cloak
            class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 flex items-center justify-center text-[10px] font-bold text-white bg-red-500 rounded-full"
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
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">الإشعارات</h3>
            <button 
                x-show="count > 0"
                @click="markAllAsRead()"
                class="text-xs text-primary-600 hover:text-primary-700"
            >
                قراءة الكل
            </button>
        </div>
        
        {{-- Notifications List --}}
        <div class="max-h-80 overflow-y-auto">
            <template x-if="notifications.length === 0">
                <div class="px-4 py-8 text-center">
                    <svg style="width:10px;height:10px" class="mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">لا توجد إشعارات جديدة</p>
                </div>
            </template>
            
            <template x-for="notification in notifications" :key="notification.id">
                <div 
                    class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 last:border-0 cursor-pointer transition-colors"
                    @click="markAsRead(notification.id)"
                >
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <svg :class="getColor(notification.type)" style="width:10px;height:10px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(notification.type)"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="notification.title"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="notification.message"></p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1" x-text="notification.created_at"></p>
                    </div>
                </div>
            </template>
        </div>
        
        {{-- Footer --}}
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
            <a 
                href="{{ url('/admin/notifications') }}"
                class="block text-center text-sm text-primary-600 hover:text-primary-700 font-medium"
            >
                عرض جميع الإشعارات
            </a>
        </div>
    </div>
</div>
@endauth
