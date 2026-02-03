<x-filament-panels::page>
    <style>
        .notifications-container { max-width: 100%; }
        
        .notifications-header {
            background: linear-gradient(135deg, #6366f1 0%, #f59e0b 100%);
            border-radius: 16px;
            padding: 20px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 16px;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        .header-stats { display: flex; align-items: center; gap: 20px; }
        .stat-item { display: flex; align-items: center; gap: 12px; }
        .stat-icon {
            width: 48px; height: 48px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .stat-value { font-size: 28px; font-weight: 700; line-height: 1; }
        .stat-label { font-size: 12px; opacity: 0.85; margin-top: 2px; }
        .stat-divider { width: 1px; height: 40px; background: rgba(255,255,255,0.2); }
        
        .header-actions { display: flex; gap: 8px; }
        .action-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 16px;
            background: rgba(255,255,255,0.2);
            border: none; border-radius: 10px;
            color: white; font-size: 13px; font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        .action-btn:hover { background: rgba(255,255,255,0.3); }
        .action-btn.danger { background: rgba(239, 68, 68, 0.3); }
        .action-btn.danger:hover { background: rgba(239, 68, 68, 0.5); }
        
        .filters-bar {
            background: white;
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        .filter-tabs {
            display: flex;
            background: #f3f4f6;
            border-radius: 8px;
            padding: 4px;
        }
        .filter-tab {
            padding: 8px 16px;
            border: none;
            background: transparent;
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .filter-tab.active {
            background: white;
            color: #6366f1;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            background: white;
            color: #374151;
            cursor: pointer;
            outline: none;
        }
        .filter-select:focus { border-color: #6366f1; }
        
        .notifications-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        .notification-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }
        .notification-item:last-child { border-bottom: none; }
        .notification-item:hover { background: #f9fafb; }
        .notification-item.unread { background: #f0f9ff; }
        
        .notification-indicator {
            width: 4px; height: 40px;
            border-radius: 2px;
            flex-shrink: 0;
        }
        .notification-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .notification-content { flex: 1; min-width: 0; }
        .notification-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .notification-message {
            font-size: 12px;
            color: #6b7280;
            margin: 4px 0 0 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .notification-badge {
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 6px;
            white-space: nowrap;
        }
        .notification-time {
            font-size: 12px;
            color: #9ca3af;
            white-space: nowrap;
        }
        .notification-actions { display: flex; gap: 4px; }
        .notification-action {
            padding: 8px;
            background: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .notification-action.primary { color: #6366f1; }
        .notification-action.primary:hover { background: #e0e7ff; }
        .notification-action.danger { color: #ef4444; }
        .notification-action.danger:hover { background: #fee2e2; }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-icon {
            width: 80px; height: 80px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }
        .empty-title { font-size: 16px; font-weight: 600; color: #1f2937; margin: 0 0 8px 0; }
        .empty-text { font-size: 14px; color: #6b7280; margin: 0; }
        
        .pagination-wrapper {
            background: white;
            border-radius: 12px;
            padding: 12px;
            margin-top: 16px;
            border: 1px solid #e5e7eb;
        }
        
        /* Colors */
        .bg-blue { background: #3b82f6; }
        .bg-green { background: #22c55e; }
        .bg-amber { background: #f59e0b; }
        .bg-purple { background: #a855f7; }
        .bg-orange { background: #f97316; }
        .bg-teal { background: #14b8a6; }
        .bg-indigo { background: #6366f1; }
        .bg-emerald { background: #10b981; }
        .bg-gray { background: #6b7280; }
        
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .badge-green { background: #dcfce7; color: #16a34a; }
        .badge-amber { background: #fef3c7; color: #d97706; }
        .badge-purple { background: #f3e8ff; color: #9333ea; }
        .badge-orange { background: #ffedd5; color: #ea580c; }
        .badge-teal { background: #ccfbf1; color: #0d9488; }
        .badge-indigo { background: #e0e7ff; color: #4f46e5; }
        .badge-emerald { background: #d1fae5; color: #059669; }
        .badge-gray { background: #f3f4f6; color: #4b5563; }
        
        @media (prefers-color-scheme: dark) {
            .filters-bar, .notifications-list, .pagination-wrapper { background: #1f2937; border-color: #374151; }
            .filter-tabs { background: #374151; }
            .filter-tab { color: #9ca3af; }
            .filter-tab.active { background: #4b5563; color: #a5b4fc; }
            .filter-select { background: #374151; border-color: #4b5563; color: white; }
            .notification-item { border-color: #374151; }
            .notification-item:hover { background: #374151; }
            .notification-item.unread { background: rgba(99, 102, 241, 0.1); }
            .notification-title { color: white; }
            .empty-icon { background: #374151; }
            .empty-title { color: white; }
            .notification-action.primary:hover { background: rgba(99, 102, 241, 0.2); }
            .notification-action.danger:hover { background: rgba(239, 68, 68, 0.2); }
        }
    </style>

    <div class="notifications-container">
        {{-- Header --}}
        <div class="notifications-header">
            <div class="header-stats">
                <div class="stat-item">
                    <div class="stat-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <p class="stat-value">{{ $this->unreadCount }}</p>
                        <p class="stat-label">غير مقروء</p>
                    </div>
                </div>
                <div class="stat-divider"></div>
                <div>
                    <p class="stat-value">{{ $this->notifications->total() }}</p>
                    <p class="stat-label">إجمالي</p>
                </div>
            </div>
            
            <div class="header-actions">
                @if($this->unreadCount > 0)
                    <button class="action-btn" wire:click="markAllAsRead">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        قراءة الكل
                    </button>
                @endif
                <button class="action-btn danger" wire:click="deleteAllRead" wire:confirm="هل أنت متأكد؟">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    حذف المقروء
                </button>
            </div>
        </div>
        
        {{-- Filters --}}
        <div class="filters-bar">
            <div class="filter-tabs">
                <button wire:click="setFilter('all')" class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">الكل</button>
                <button wire:click="setFilter('unread')" class="filter-tab {{ $filter === 'unread' ? 'active' : '' }}">غير مقروء</button>
                <button wire:click="setFilter('read')" class="filter-tab {{ $filter === 'read' ? 'active' : '' }}">مقروء</button>
            </div>
            
            <select wire:model.live="typeFilter" class="filter-select">
                <option value="">جميع الأنواع</option>
                @foreach($this->notificationTypes as $type => $label)
                    <option value="{{ $type }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        
        {{-- Notifications List --}}
        <div class="notifications-list">
            @forelse($this->notifications as $notification)
                @php
                    $data = $notification->data;
                    $type = $data['type'] ?? 'general';
                    $isUnread = is_null($notification->read_at);
                    
                    $colorMap = [
                        'new_enrollment' => 'blue',
                        'enrollment_confirmed' => 'green',
                        'course_completed' => 'amber',
                        'new_lesson' => 'purple',
                        'new_question' => 'orange',
                        'question_answered' => 'teal',
                        'lesson_comment' => 'indigo',
                        'order_confirmed' => 'emerald',
                        'new_sale' => 'green',
                        'new_message' => 'blue',
                    ];
                    $color = $colorMap[$type] ?? 'gray';
                @endphp
                
                <div class="notification-item {{ $isUnread ? 'unread' : '' }}">
                    <div class="notification-indicator bg-{{ $color }}"></div>
                    
                    <div class="notification-icon bg-{{ $color }}">
                        <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24">
                            @switch($type)
                                @case('new_enrollment')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    @break
                                @case('enrollment_confirmed')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    @break
                                @case('course_completed')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    @break
                                @case('new_lesson')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    @break
                                @case('new_message')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    @break
                                @case('order_confirmed')
                                @case('new_sale')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    @break
                                @default
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            @endswitch
                        </svg>
                    </div>
                    
                    <div class="notification-content">
                        <p class="notification-title">{{ $data['title'] ?? 'إشعار جديد' }}</p>
                        <p class="notification-message">{{ $data['message'] ?? '' }}</p>
                    </div>
                    
                    <span class="notification-badge badge-{{ $color }}">
                        {{ $this->notificationTypes[$type] ?? 'عام' }}
                    </span>
                    
                    <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                    
                    <div class="notification-actions">
                        @if($isUnread)
                            <button class="notification-action primary" wire:click="markAsRead('{{ $notification->id }}')" title="تحديد كمقروء">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        @endif
                        <button class="notification-action danger" wire:click="deleteNotification('{{ $notification->id }}')" title="حذف">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="32" height="32" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <p class="empty-title">لا توجد إشعارات</p>
                    <p class="empty-text">ستظهر الإشعارات الجديدة هنا</p>
                </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if($this->notifications->hasPages())
            <div class="pagination-wrapper">
                {{ $this->notifications->links() }}
            </div>
        @endif
    </div>
</x-filament-panels::page>
