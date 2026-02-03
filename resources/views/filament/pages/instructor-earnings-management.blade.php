<x-filament-panels::page>
    <style>
        .earnings-management-container {
            display: flex;
            gap: 24px;
            min-height: calc(100vh - 200px);
        }
        
        /* Header */
        .earnings-header {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 50%, #15803d 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 10px 40px rgba(34, 197, 94, 0.3);
            position: relative;
            overflow: hidden;
        }
        .earnings-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        }
        .earnings-header-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .earnings-header-info { display: flex; align-items: center; gap: 16px; }
        .earnings-header-icon {
            width: 60px; height: 60px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
        }
        .earnings-header-text h1 { font-size: 26px; font-weight: 800; margin: 0; }
        .earnings-header-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        
        /* Tabs */
        .earnings-tabs { display: flex; gap: 8px; }
        .earnings-tab {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .earnings-tab:hover { background: rgba(255,255,255,0.3); }
        .earnings-tab.active { background: white; color: #16a34a; }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.3s;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .stat-icon.green { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .stat-icon.blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .stat-icon.purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .stat-icon.amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-value { font-size: 24px; font-weight: 800; color: #1f2937; margin: 0; }
        .stat-label { font-size: 12px; color: #6b7280; margin: 4px 0 0 0; }
        
        /* Sidebar */
        .earnings-sidebar {
            width: 320px;
            flex-shrink: 0;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 18px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .sidebar-title { font-size: 15px; font-weight: 700; color: #1f2937; margin: 0 0 12px 0; }
        .sidebar-search {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 13px;
            transition: border-color 0.2s;
        }
        .sidebar-search:focus { outline: none; border-color: #22c55e; }
        .sidebar-list { flex: 1; overflow-y: auto; max-height: 500px; }
        .instructor-item {
            padding: 14px 20px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .instructor-item:hover { background: #f9fafb; }
        .instructor-item.active { background: linear-gradient(135deg, #dcfce7, #bbf7d0); border-right: 3px solid #22c55e; }
        .instructor-avatar {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 14px;
            flex-shrink: 0;
        }
        .instructor-info { flex: 1; min-width: 0; }
        .instructor-name { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .instructor-courses { font-size: 11px; color: #6b7280; margin: 4px 0 0 0; }
        
        /* Main Content */
        .earnings-main { flex: 1; min-width: 0; }
        .main-panel {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .panel-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 18px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .panel-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 10px; }
        .panel-body { padding: 24px; }
        
        /* Earnings Table */
        .earnings-table { width: 100%; border-collapse: collapse; }
        .earnings-table th {
            text-align: right;
            padding: 14px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .earnings-table td {
            padding: 14px 16px;
            font-size: 13px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .earnings-table tr:hover { background: #fafafa; }
        .course-title { font-weight: 600; color: #1f2937; }
        .earning-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }
        .earning-badge.percentage { background: #dbeafe; color: #2563eb; }
        .earning-badge.fixed { background: #dcfce7; color: #16a34a; }
        .earning-badge.inactive { background: #f3f4f6; color: #6b7280; }
        .total-amount { font-weight: 700; color: #22c55e; font-size: 14px; }
        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .action-btn.edit { background: #f3f4f6; color: #374151; }
        .action-btn.edit:hover { background: #e5e7eb; }
        .action-btn.delete { background: #fef2f2; color: #dc2626; }
        .action-btn.delete:hover { background: #fee2e2; }
        .action-btn.toggle { background: #fef3c7; color: #d97706; }
        .action-btn.toggle:hover { background: #fde68a; }
        
        /* Form */
        .earning-form {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #86efac;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 20px;
        }
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .form-title { font-size: 16px; font-weight: 700; color: #166534; margin: 0; display: flex; align-items: center; gap: 8px; }
        .form-close {
            background: none;
            border: none;
            color: #166534;
            cursor: pointer;
            padding: 4px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: span 2; }
        .form-label { font-size: 13px; font-weight: 600; color: #166534; }
        .form-input, .form-select {
            padding: 12px 14px;
            border: 2px solid #86efac;
            border-radius: 10px;
            font-size: 14px;
            background: white;
            transition: border-color 0.2s;
        }
        .form-input:focus, .form-select:focus { outline: none; border-color: #22c55e; }
        .form-checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-checkbox {
            width: 20px;
            height: 20px;
            accent-color: #22c55e;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-save {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4); }
        .btn-cancel {
            background: white;
            color: #374151;
            padding: 12px 24px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-cancel:hover { background: #f9fafb; }
        
        /* Settings Panel */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .setting-card {
            background: linear-gradient(135deg, #f9fafb, #f3f4f6);
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 24px;
            transition: all 0.3s;
        }
        .setting-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .setting-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
            font-size: 22px;
        }
        .setting-icon.green { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
        .setting-icon.blue { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }
        .setting-icon.purple { background: linear-gradient(135deg, #ede9fe, #ddd6fe); }
        .setting-icon.amber { background: linear-gradient(135deg, #fef3c7, #fde68a); }
        .setting-label { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
        .setting-input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            transition: border-color 0.2s;
            background: white;
        }
        .setting-input:focus { outline: none; border-color: #22c55e; }
        .setting-hint { font-size: 12px; color: #9ca3af; text-align: center; margin-top: 8px; }
        .save-btn {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s;
            width: 100%;
            margin-top: 24px;
        }
        .save-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4); }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-icon {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }
        .empty-title { font-size: 18px; font-weight: 700; color: #374151; margin: 0 0 8px 0; }
        .empty-text { font-size: 14px; color: #6b7280; margin: 0; }
        
        /* Add Button */
        .add-btn {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4); }
        
        /* Messages */
        .success-message {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            border: 1px solid #86efac;
            border-radius: 12px;
            padding: 14px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #166534;
            font-weight: 600;
        }
        .error-message {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: 1px solid #fca5a5;
            border-radius: 12px;
            padding: 14px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #991b1b;
            font-weight: 600;
        }
        
        /* Summary Card */
        .summary-card {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-radius: 14px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
        }
        .summary-title { font-size: 14px; opacity: 0.9; margin: 0 0 8px 0; }
        .summary-value { font-size: 32px; font-weight: 800; margin: 0; }
        
        @media (max-width: 1024px) {
            .earnings-management-container { flex-direction: column; }
            .earnings-sidebar { width: 100%; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .settings-grid, .form-grid { grid-template-columns: 1fr; }
            .form-group.full { grid-column: span 1; }
        }
        
        @media (prefers-color-scheme: dark) {
            .stat-card, .earnings-sidebar, .main-panel { background: #1f2937; border-color: #374151; }
            .stat-value, .panel-title, .sidebar-title, .instructor-name, .setting-label, .course-title { color: #f9fafb; }
            .sidebar-header, .panel-header { background: linear-gradient(135deg, #1f2937, #374151); border-color: #374151; }
            .earnings-table th { background: #374151; color: #d1d5db; }
            .earnings-table td { color: #e5e7eb; border-color: #374151; }
            .instructor-item { border-color: #374151; }
            .instructor-item:hover { background: #374151; }
            .instructor-item.active { background: linear-gradient(135deg, #064e3b, #065f46); }
            .setting-card { background: linear-gradient(135deg, #374151, #4b5563); border-color: #4b5563; }
            .setting-input, .form-input, .form-select { background: #1f2937; border-color: #4b5563; color: #f9fafb; }
            .sidebar-search { background: #374151; border-color: #4b5563; color: #f9fafb; }
            .earning-form { background: linear-gradient(135deg, #064e3b, #065f46); border-color: #059669; }
            .form-title, .form-label { color: #a7f3d0; }
        }
    </style>
    
    @php
        $stats = $this->stats;
        $instructors = $this->instructors;
        $selectedInstructor = $this->selectedInstructor;
        $instructorEarnings = $this->instructorEarnings;
        $availableCourses = $this->availableCourses;
    @endphp
    
    {{-- Header --}}
    <div class="earnings-header">
        <div class="earnings-header-content">
            <div class="earnings-header-info">
                <div class="earnings-header-icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="earnings-header-text">
                    <h1>إدارة أرباح المدرسين</h1>
                    <p>تحديد نسب العمولة للدورات وإدارة الإعدادات المالية</p>
                </div>
            </div>
            
            <div class="earnings-tabs">
                <button class="earnings-tab {{ $activeTab === 'earnings' ? 'active' : '' }}" wire:click="setTab('earnings')">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    الأرباح
                </button>
                <button class="earnings-tab {{ $activeTab === 'settings' ? 'active' : '' }}" wire:click="setTab('settings')">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    الإعدادات
                </button>
            </div>
        </div>
    </div>
    
    {{-- Stats Grid --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon purple">
                <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value">{{ number_format($stats['total_instructors']) }}</p>
                <p class="stat-label">المدرسين</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon blue">
                <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="stat-value">{{ number_format($stats['active_earnings']) }}</p>
                <p class="stat-label">الدورات النشطة</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon amber">
                <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value">{{ number_format($stats['total_payments'], 0) }}</p>
                <p class="stat-label">إجمالي المدفوعات (ج.م)</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon green">
                <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value">{{ number_format($stats['total_earnings'], 0) }}</p>
                <p class="stat-label">إجمالي الأرباح (ج.م)</p>
            </div>
        </div>
    </div>
    
    @if($activeTab === 'earnings')
    {{-- Earnings Tab --}}
    <div class="earnings-management-container">
        {{-- Sidebar --}}
        <div class="earnings-sidebar">
            <div class="sidebar-header">
                <h3 class="sidebar-title">المدرسين</h3>
                <input type="text" class="sidebar-search" placeholder="البحث عن مدرس..." wire:model.live.debounce.300ms="searchQuery">
            </div>
            <div class="sidebar-list">
                @forelse($instructors as $instructor)
                <div class="instructor-item {{ $selectedInstructorId === $instructor->id ? 'active' : '' }}" wire:click="selectInstructor({{ $instructor->id }})">
                    <div class="instructor-avatar">{{ mb_substr($instructor->name, 0, 1) }}</div>
                    <div class="instructor-info">
                        <p class="instructor-name">{{ $instructor->name }}</p>
                        <p class="instructor-courses">{{ $instructor->courses_count ?? 0 }} دورة نشطة</p>
                    </div>
                </div>
                @empty
                <div style="padding: 30px; text-align: center; color: #9ca3af;">لا يوجد مدرسين</div>
                @endforelse
            </div>
        </div>
        
        {{-- Main Content --}}
        <div class="earnings-main">
            @if($selectedInstructor)
            
            @if(session('earning_success'))
            <div class="success-message">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('earning_success') }}
            </div>
            @endif
            
            @if(session('earning_error'))
            <div class="error-message">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('earning_error') }}
            </div>
            @endif
            
            {{-- Add/Edit Earning Form --}}
            @if($showEarningForm)
            <div class="earning-form">
                <div class="form-header">
                    <h3 class="form-title">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        {{ $editingEarningId ? 'تعديل نسبة العمولة' : 'إضافة نسبة عمولة جديدة' }}
                    </h3>
                    <button class="form-close" wire:click="resetEarningForm">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">الدورة التدريبية</label>
                        <select class="form-select" wire:model="formCourseId" {{ $editingEarningId ? 'disabled' : '' }}>
                            <option value="">اختر الدورة...</option>
                            @if($editingEarningId)
                                @php $currentEarning = $instructorEarnings->firstWhere('id', $editingEarningId); @endphp
                                @if($currentEarning)
                                <option value="{{ $currentEarning->course_id }}" selected>{{ $currentEarning->course->title ?? '' }}</option>
                                @endif
                            @else
                                @foreach($availableCourses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('formCourseId') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">نوع العمولة</label>
                        <select class="form-select" wire:model.live="formEarningsType">
                            <option value="percentage">نسبة مئوية (%)</option>
                            <option value="fixed">مبلغ ثابت (ج.م)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">{{ $formEarningsType === 'percentage' ? 'النسبة المئوية' : 'المبلغ الثابت' }}</label>
                        <input type="number" class="form-input" wire:model="formEarningsValue" min="0" step="0.1" placeholder="{{ $formEarningsType === 'percentage' ? 'مثال: 70' : 'مثال: 100' }}">
                        @error('formEarningsValue') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">الحالة</label>
                        <div class="form-checkbox-group" style="height: 46px; align-items: center;">
                            <input type="checkbox" class="form-checkbox" wire:model="formIsActive" id="formIsActive">
                            <label for="formIsActive" style="font-size: 14px; color: #166534;">نشط (يتم حساب الأرباح)</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button class="btn-save" wire:click="saveEarning">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $editingEarningId ? 'تحديث' : 'حفظ' }}
                    </button>
                    <button class="btn-cancel" wire:click="resetEarningForm">إلغاء</button>
                </div>
            </div>
            @endif
            
            <div class="summary-card">
                <p class="summary-title">إجمالي أرباح {{ $selectedInstructor->name }}</p>
                <p class="summary-value">{{ number_format($instructorEarnings->where('is_active', true)->sum(fn($e) => $e->calculateTotalEarnings()), 2) }} ج.م</p>
            </div>
            
            <div class="main-panel">
                <div class="panel-header">
                    <h3 class="panel-title">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        نسب العمولة للدورات
                    </h3>
                    @if(!$showEarningForm && $availableCourses->count() > 0)
                    <button class="add-btn" wire:click="openAddEarningForm">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        إضافة عمولة
                    </button>
                    @endif
                </div>
                <div class="panel-body" style="padding: 0;">
                    @if($instructorEarnings->count() > 0)
                    <table class="earnings-table">
                        <thead>
                            <tr>
                                <th>الدورة</th>
                                <th>نوع العمولة</th>
                                <th>القيمة</th>
                                <th>الطلاب</th>
                                <th>المدفوعات</th>
                                <th>الأرباح</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($instructorEarnings as $earning)
                            <tr style="{{ !$earning->is_active ? 'opacity: 0.6;' : '' }}">
                                <td><span class="course-title">{{ Str::limit($earning->course->title ?? 'غير محدد', 30) }}</span></td>
                                <td>
                                    <span class="earning-badge {{ $earning->earnings_type }}">
                                        {{ $earning->earnings_type === 'percentage' ? 'نسبة مئوية' : 'مبلغ ثابت' }}
                                    </span>
                                </td>
                                <td style="font-weight: 700;">
                                    {{ $earning->earnings_type === 'percentage' ? number_format($earning->earnings_value, 1) . '%' : number_format($earning->earnings_value, 2) . ' ج.م' }}
                                </td>
                                <td>{{ $earning->getStudentsCount() }}</td>
                                <td>{{ number_format($earning->getTotalPayments(), 2) }}</td>
                                <td class="total-amount">{{ number_format($earning->calculateTotalEarnings(), 2) }}</td>
                                <td>
                                    <span class="earning-badge {{ $earning->is_active ? 'percentage' : 'inactive' }}">
                                        {{ $earning->is_active ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <button class="action-btn edit" wire:click="editEarning({{ $earning->id }})" title="تعديل">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button class="action-btn toggle" wire:click="toggleEarningStatus({{ $earning->id }})" title="{{ $earning->is_active ? 'إيقاف' : 'تفعيل' }}">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($earning->is_active)
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                @endif
                                            </svg>
                                        </button>
                                        <button class="action-btn delete" wire:click="deleteEarning({{ $earning->id }})" wire:confirm="هل أنت متأكد من حذف هذه العمولة؟" title="حذف">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg width="32" height="32" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="empty-title">لا توجد نسب عمولة</p>
                        <p class="empty-text">لم يتم تحديد نسب عمولة لدورات هذا المدرس بعد</p>
                        @if($availableCourses->count() > 0)
                        <button class="add-btn" style="margin: 20px auto 0;" wire:click="openAddEarningForm">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            إضافة عمولة
                        </button>
                        @else
                        <p style="font-size: 13px; color: #9ca3af; margin-top: 10px;">لا توجد دورات متاحة لهذا المدرس</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="main-panel">
                <div class="panel-body">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg width="32" height="32" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                            </svg>
                        </div>
                        <p class="empty-title">اختر مدرس</p>
                        <p class="empty-text">اختر مدرس من القائمة لإدارة نسب العمولة لدوراته</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @else
    {{-- Settings Tab --}}
    <div class="main-panel">
        <div class="panel-header">
            <h3 class="panel-title">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                إعدادات العمولات والسحب
            </h3>
        </div>
        <div class="panel-body">
            @if(session('success'))
            <div class="success-message">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif
            
            <div class="settings-grid">
                <div class="setting-card">
                    <div class="setting-icon green">💰</div>
                    <label class="setting-label">نسبة عمولة المدرس الافتراضية</label>
                    <input type="number" class="setting-input" wire:model="defaultCommissionRate" min="0" max="100" step="0.1">
                    <p class="setting-hint">النسبة التي يحصل عليها المدرس من كل عملية بيع (%)</p>
                </div>
                
                <div class="setting-card">
                    <div class="setting-icon blue">🏦</div>
                    <label class="setting-label">رسوم المعالجة الإدارية</label>
                    <input type="number" class="setting-input" wire:model="adminFeeRate" min="0" max="50" step="0.1">
                    <p class="setting-hint">نسبة الرسوم المخصومة عند السحب (%)</p>
                </div>
                
                <div class="setting-card">
                    <div class="setting-icon purple">📊</div>
                    <label class="setting-label">الحد الأدنى للسحب</label>
                    <input type="number" class="setting-input" wire:model="minimumPayout" min="0" step="10">
                    <p class="setting-hint">أقل مبلغ يمكن للمدرس سحبه (ج.م)</p>
                </div>
                
                <div class="setting-card">
                    <div class="setting-icon amber">📅</div>
                    <label class="setting-label">أيام معالجة السحب</label>
                    <input type="number" class="setting-input" wire:model="processingDays" min="1" max="30">
                    <p class="setting-hint">عدد الأيام المتوقعة لمعالجة طلبات السحب</p>
                </div>
            </div>
            
            <button class="save-btn" wire:click="saveSettings">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                حفظ الإعدادات
            </button>
        </div>
    </div>
    @endif
</x-filament-panels::page>
