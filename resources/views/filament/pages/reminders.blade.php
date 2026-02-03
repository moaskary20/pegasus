<x-filament-panels::page>
    <style>
        .reminders-container { max-width: 100%; }
        
        .reminders-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 10px 40px rgba(245, 158, 11, 0.3);
            position: relative;
            overflow: hidden;
        }
        .reminders-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        }
        .header-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .header-info { display: flex; align-items: center; gap: 16px; }
        .header-icon {
            width: 60px; height: 60px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
        }
        .header-text h1 { font-size: 28px; font-weight: 800; margin: 0; }
        .header-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        
        .header-stats { display: flex; gap: 16px; flex-wrap: wrap; }
        .header-stat {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            padding: 14px 20px;
            text-align: center;
            min-width: 90px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .header-stat-value { font-size: 28px; font-weight: 800; margin: 0; line-height: 1; }
        .header-stat-label { font-size: 11px; opacity: 0.9; margin-top: 4px; }
        
        .content-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 24px;
        }
        @media (max-width: 1024px) {
            .content-grid { grid-template-columns: 1fr; }
        }
        
        .sidebar-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        .sidebar-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .sidebar-title { font-size: 15px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 8px; }
        .sidebar-body { padding: 12px; }
        
        .filter-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 4px;
        }
        .filter-item:hover { background: #f3f4f6; }
        .filter-item.active { background: linear-gradient(135deg, #fef3c7, #fde68a); }
        .filter-item-left { display: flex; align-items: center; gap: 12px; }
        .filter-item-icon { font-size: 18px; }
        .filter-item-label { font-size: 14px; font-weight: 500; color: #374151; }
        .filter-item-count {
            background: #e5e7eb;
            color: #4b5563;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .filter-item.active .filter-item-count { background: #f59e0b; color: white; }
        
        .settings-section { margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
        .settings-title { font-size: 12px; font-weight: 600; color: #6b7280; margin: 0 0 12px 16px; text-transform: uppercase; }
        .setting-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            border-radius: 10px;
        }
        .setting-item:hover { background: #f9fafb; }
        .setting-label { font-size: 13px; color: #374151; display: flex; align-items: center; gap: 8px; }
        .setting-toggle {
            position: relative;
            width: 44px;
            height: 24px;
            background: #d1d5db;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .setting-toggle.active { background: #f59e0b; }
        .setting-toggle::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .setting-toggle.active::after { transform: translateX(20px); }
        
        .reminders-list { display: flex; flex-direction: column; gap: 16px; }
        
        .reminder-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            transition: all 0.3s;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .reminder-card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .reminder-content {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 24px;
        }
        .reminder-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            flex-shrink: 0;
        }
        .reminder-icon.purple { background: linear-gradient(135deg, #ede9fe, #ddd6fe); }
        .reminder-icon.blue { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }
        .reminder-icon.green { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
        .reminder-icon.orange { background: linear-gradient(135deg, #ffedd5, #fed7aa); }
        .reminder-icon.teal { background: linear-gradient(135deg, #ccfbf1, #99f6e4); }
        .reminder-icon.yellow { background: linear-gradient(135deg, #fef9c3, #fef08a); }
        .reminder-icon.red { background: linear-gradient(135deg, #fee2e2, #fecaca); }
        .reminder-icon.indigo { background: linear-gradient(135deg, #e0e7ff, #c7d2fe); }
        
        .reminder-info { flex: 1; min-width: 0; }
        .reminder-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; }
        .reminder-message { font-size: 14px; color: #6b7280; margin: 6px 0 0 0; line-height: 1.5; }
        .reminder-type {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: #9ca3af;
            margin-top: 8px;
        }
        
        .reminder-actions { display: flex; gap: 10px; flex-shrink: 0; }
        .reminder-action {
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .reminder-action.primary {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        .reminder-action.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
        }
        .reminder-action.secondary {
            background: #f3f4f6;
            color: #6b7280;
            border: 1px solid #e5e7eb;
        }
        .reminder-action.secondary:hover { background: #e5e7eb; }
        
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 20px;
            border: 2px dashed #d1d5db;
        }
        .empty-icon { font-size: 64px; margin-bottom: 20px; }
        .empty-title { font-size: 20px; font-weight: 700; color: #374151; margin: 0 0 8px 0; }
        .empty-text { font-size: 15px; color: #6b7280; margin: 0; }
        
        @media (prefers-color-scheme: dark) {
            .sidebar-card, .reminder-card { background: #1f2937; border-color: #374151; }
            .sidebar-header { background: linear-gradient(135deg, #1f2937, #374151); border-color: #374151; }
            .sidebar-title, .reminder-title { color: #f9fafb; }
            .filter-item:hover { background: #374151; }
            .filter-item.active { background: linear-gradient(135deg, #78350f, #92400e); }
            .filter-item-label, .setting-label { color: #d1d5db; }
        }
    </style>

    @php
        $reminders = $this->reminders;
        $counts = $this->reminderCounts;
        $settings = $this->settings;
        $types = \App\Models\Reminder::getTypes();
    @endphp

    <div class="reminders-container">
        {{-- Header --}}
        <div class="reminders-header">
            <div class="header-content">
                <div class="header-info">
                    <div class="header-icon">⏰</div>
                    <div class="header-text">
                        <h1>التذكيرات</h1>
                        <p>تتبع مهامك والأشياء التي تحتاج انتباهك</p>
                    </div>
                </div>
                
                <div class="header-stats">
                    <div class="header-stat">
                        <p class="header-stat-value">{{ $counts['total'] }}</p>
                        <p class="header-stat-label">إجمالي التذكيرات</p>
                    </div>
                    <div class="header-stat">
                        <p class="header-stat-value">{{ $counts['quiz'] ?? 0 }}</p>
                        <p class="header-stat-label">اختبارات</p>
                    </div>
                    <div class="header-stat">
                        <p class="header-stat-value">{{ $counts['message'] ?? 0 }}</p>
                        <p class="header-stat-label">رسائل</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-grid">
            {{-- Sidebar --}}
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h3 class="sidebar-title">
                        <span>🔍</span>
                        تصفية التذكيرات
                    </h3>
                </div>
                <div class="sidebar-body">
                    <div class="filter-item {{ $activeFilter === 'all' ? 'active' : '' }}" wire:click="setFilter('all')">
                        <div class="filter-item-left">
                            <span class="filter-item-icon">📋</span>
                            <span class="filter-item-label">الكل</span>
                        </div>
                        <span class="filter-item-count">{{ $counts['total'] }}</span>
                    </div>
                    
                    @foreach($types as $type => $label)
                        @if(($counts[$type] ?? 0) > 0)
                            <div class="filter-item {{ $activeFilter === $type ? 'active' : '' }}" wire:click="setFilter('{{ $type }}')">
                                <div class="filter-item-left">
                                    <span class="filter-item-icon">{{ \App\Models\Reminder::getTypeIcon($type) }}</span>
                                    <span class="filter-item-label">{{ $label }}</span>
                                </div>
                                <span class="filter-item-count">{{ $counts[$type] ?? 0 }}</span>
                            </div>
                        @endif
                    @endforeach
                    
                    <div class="settings-section">
                        <p class="settings-title">إعدادات التذكيرات</p>
                        @foreach($types as $type => $label)
                            <div class="setting-item">
                                <span class="setting-label">
                                    <span>{{ \App\Models\Reminder::getTypeIcon($type) }}</span>
                                    {{ $label }}
                                </span>
                                <div class="setting-toggle {{ ($settings[$type] ?? true) ? 'active' : '' }}" wire:click="toggleSetting('{{ $type }}')"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            {{-- Reminders List --}}
            <div class="reminders-list">
                @forelse($reminders as $reminder)
                    <div class="reminder-card">
                        <div class="reminder-content">
                            <div class="reminder-icon {{ $reminder['color'] }}">
                                {{ $reminder['icon'] }}
                            </div>
                            <div class="reminder-info">
                                <h4 class="reminder-title">{{ $reminder['title'] }}</h4>
                                <p class="reminder-message">{{ $reminder['message'] }}</p>
                                <span class="reminder-type">
                                    {{ $types[$reminder['type']] ?? $reminder['type'] }}
                                </span>
                            </div>
                            <div class="reminder-actions">
                                @if($reminder['action_url'] ?? null)
                                    <a href="{{ $reminder['action_url'] }}" class="reminder-action primary">
                                        {{ $reminder['action_label'] ?? 'عرض' }}
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endif
                                <button class="reminder-action secondary" wire:click="dismissReminder('{{ $reminder['type'] }}', {{ $reminder['remindable_id'] ?? 'null' }})">
                                    تجاهل
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">🎉</div>
                        <p class="empty-title">لا توجد تذكيرات!</p>
                        <p class="empty-text">أنت مواكب لكل شيء - أحسنت!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
