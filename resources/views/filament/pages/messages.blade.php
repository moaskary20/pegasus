<x-filament-panels::page>
    <style>
        .messages-container { max-width: 100%; }
        .messages-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
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
        .messages-header-left { display: flex; align-items: center; gap: 12px; }
        .messages-header-icon {
            width: 48px; height: 48px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .messages-header h2 { font-size: 20px; font-weight: 700; margin: 0; }
        .messages-header p { font-size: 14px; opacity: 0.85; margin: 4px 0 0 0; }
        .new-chat-btn {
            background: rgba(255,255,255,0.2);
            border: none; border-radius: 10px;
            padding: 10px 18px;
            color: white; font-size: 14px; font-weight: 500;
            cursor: pointer;
            display: flex; align-items: center; gap: 8px;
            transition: background 0.2s;
        }
        .new-chat-btn:hover { background: rgba(255,255,255,0.3); }
        
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
        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 10px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border 0.2s;
        }
        .search-input:focus { border-color: #6366f1; }
        
        .conversations-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        .conversation-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background 0.2s;
        }
        .conversation-item:last-child { border-bottom: none; }
        .conversation-item:hover { background: #f9fafb; }
        .conversation-item.unread { background: #f0f9ff; }
        
        .avatar {
            width: 48px; height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 18px;
            flex-shrink: 0;
            position: relative;
        }
        .avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .unread-badge {
            position: absolute;
            top: -4px; right: -4px;
            min-width: 20px; height: 20px;
            background: #ef4444;
            border-radius: 10px;
            color: white;
            font-size: 11px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            padding: 0 6px;
        }
        
        .conversation-content { flex: 1; min-width: 0; }
        .conversation-header { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .conversation-name { font-size: 15px; font-weight: 600; color: #1f2937; margin: 0; }
        .conversation-time { font-size: 12px; color: #9ca3af; white-space: nowrap; }
        .conversation-preview { font-size: 13px; color: #6b7280; margin: 4px 0 0 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        
        .type-badge {
            background: #e0e7ff;
            color: #4f46e5;
            font-size: 11px; font-weight: 600;
            padding: 4px 10px;
            border-radius: 12px;
        }
        
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
        
        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 20px;
        }
        .modal-content {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 420px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .modal-title { font-size: 16px; font-weight: 600; color: #1f2937; margin: 0; }
        .modal-close {
            background: none; border: none;
            color: #9ca3af; cursor: pointer;
            padding: 4px;
        }
        .modal-close:hover { color: #6b7280; }
        .modal-body { padding: 20px; }
        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        .btn-cancel {
            background: none; border: none;
            color: #6b7280; font-size: 14px;
            cursor: pointer; padding: 10px 16px;
        }
        .btn-cancel:hover { color: #1f2937; }
        .btn-primary {
            background: #6366f1;
            border: none; border-radius: 8px;
            color: white; font-size: 14px; font-weight: 500;
            padding: 10px 20px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-primary:hover { background: #4f46e5; }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
        
        .user-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .user-item:hover { background: #f3f4f6; }
        .user-item.selected { background: #e0e7ff; }
        .user-avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex; align-items: center; justify-content: center;
            color: #6b7280; font-weight: 600;
        }
        .user-info { flex: 1; }
        .user-name { font-size: 14px; font-weight: 500; color: #1f2937; margin: 0; }
        .user-email { font-size: 12px; color: #6b7280; margin: 2px 0 0 0; }
        
        .users-list { max-height: 280px; overflow-y: auto; }
        .users-empty { text-align: center; padding: 30px; color: #6b7280; font-size: 14px; }
        
        @media (prefers-color-scheme: dark) {
            .filters-bar, .conversations-list, .modal-content { background: #1f2937; border-color: #374151; }
            .filter-tabs { background: #374151; }
            .filter-tab { color: #9ca3af; }
            .filter-tab.active { background: #4b5563; color: #a5b4fc; }
            .search-input { background: #374151; border-color: #4b5563; color: white; }
            .conversation-item { border-color: #374151; }
            .conversation-item:hover { background: #374151; }
            .conversation-item.unread { background: rgba(99, 102, 241, 0.1); }
            .conversation-name { color: white; }
            .empty-icon { background: #374151; }
            .empty-title { color: white; }
            .modal-header, .modal-footer { background: #111827; border-color: #374151; }
            .modal-title { color: white; }
            .user-item:hover { background: #374151; }
            .user-item.selected { background: rgba(99, 102, 241, 0.2); }
            .user-avatar { background: #374151; color: #9ca3af; }
            .user-name { color: white; }
        }
    </style>

    <div class="messages-container">
        {{-- Header --}}
        <div class="messages-header">
            <div class="messages-header-left">
                <div class="messages-header-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div>
                    <h2>الرسائل</h2>
                    <p>{{ $this->conversations->total() }} محادثة</p>
                </div>
            </div>
            
            <button class="new-chat-btn" wire:click="openNewConversation">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                محادثة جديدة
            </button>
        </div>
        
        {{-- Filters --}}
        <div class="filters-bar">
            <div class="filter-tabs">
                <button wire:click="setFilter('all')" class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">الكل</button>
                <button wire:click="setFilter('unread')" class="filter-tab {{ $filter === 'unread' ? 'active' : '' }}">غير مقروء</button>
                <button wire:click="setFilter('groups')" class="filter-tab {{ $filter === 'groups' ? 'active' : '' }}">المجموعات</button>
            </div>
            
            <input 
                type="text" 
                class="search-input"
                wire:model.live.debounce.300ms="search" 
                placeholder="بحث في المحادثات..."
            >
        </div>
        
        {{-- Conversations List --}}
        <div class="conversations-list">
            @forelse($this->conversations as $conversation)
                @php
                    $unreadCount = $this->getUnreadCount($conversation);
                    $name = $this->getConversationName($conversation);
                    $avatar = $this->getConversationAvatar($conversation);
                    $lastMessage = $conversation->latestMessage;
                @endphp
                
                <div 
                    class="conversation-item {{ $unreadCount > 0 ? 'unread' : '' }}"
                    wire:click="openConversation({{ $conversation->id }})"
                >
                    <div class="avatar">
                        @if($avatar)
                            <img src="{{ Storage::url($avatar) }}" alt="{{ $name }}">
                        @else
                            @if($conversation->type === 'private')
                                {{ mb_substr($name, 0, 1) }}
                            @else
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            @endif
                        @endif
                        
                        @if($unreadCount > 0)
                            <span class="unread-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                        @endif
                    </div>
                    
                    <div class="conversation-content">
                        <div class="conversation-header">
                            <p class="conversation-name">{{ $name }}</p>
                            @if($lastMessage)
                                <span class="conversation-time">{{ $lastMessage->created_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        
                        <p class="conversation-preview">
                            @if($lastMessage)
                                @if($lastMessage->user_id === auth()->id())
                                    <span style="color: #6366f1;">أنت:</span>
                                @else
                                    <span>{{ $lastMessage->user?->name }}:</span>
                                @endif
                                {{ $lastMessage->body ?? 'مرفق' }}
                            @else
                                <span style="font-style: italic;">لا توجد رسائل</span>
                            @endif
                        </p>
                    </div>
                    
                    @if($conversation->type !== 'private')
                        <span class="type-badge">{{ $conversation->type === 'course' ? 'دورة' : 'مجموعة' }}</span>
                    @endif
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="32" height="32" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <p class="empty-title">لا توجد محادثات</p>
                    <p class="empty-text">ابدأ محادثة جديدة للتواصل</p>
                </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if($this->conversations->hasPages())
            <div style="background: white; border-radius: 12px; padding: 12px; margin-top: 16px; border: 1px solid #e5e7eb;">
                {{ $this->conversations->links() }}
            </div>
        @endif
    </div>
    
    {{-- New Conversation Modal --}}
    @if($showNewConversationModal)
        <div class="modal-overlay" wire:click.self="closeNewConversation">
            <div class="modal-content">
                <div class="modal-header">
                    <p class="modal-title">محادثة جديدة</p>
                    <button class="modal-close" wire:click="closeNewConversation">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="modal-body">
                    <input 
                        type="text" 
                        class="search-input" 
                        style="width: 100%; margin-bottom: 16px;"
                        wire:model.live.debounce.300ms="searchUsers" 
                        placeholder="ابحث عن مستخدم..."
                    >
                    
                    <div class="users-list">
                        @forelse($this->searchUsersResults as $user)
                            <div 
                                class="user-item {{ $selectedUserId === $user->id ? 'selected' : '' }}"
                                wire:click="selectUser({{ $user->id }})"
                            >
                                <div class="user-avatar">
                                    @if($user->avatar)
                                        <img src="{{ Storage::url($user->avatar) }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                    @else
                                        {{ mb_substr($user->name, 0, 1) }}
                                    @endif
                                </div>
                                <div class="user-info">
                                    <p class="user-name">{{ $user->name }}</p>
                                    <p class="user-email">{{ $user->email }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="users-empty">
                                @if(strlen($searchUsers) >= 2)
                                    لا توجد نتائج
                                @else
                                    اكتب للبحث عن مستخدم
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button class="btn-cancel" wire:click="closeNewConversation">إلغاء</button>
                    <button 
                        class="btn-primary"
                        wire:click="startConversation"
                        @if(!$selectedUserId) disabled @endif
                    >
                        بدء المحادثة
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
