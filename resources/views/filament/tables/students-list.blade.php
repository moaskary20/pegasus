<style>
    .subscribers-container { max-width: 100%; }
    
    .subscribers-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        border-radius: 20px;
        padding: 24px 28px;
        color: white;
        margin-bottom: 24px;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        position: relative;
        overflow: hidden;
    }
    .subscribers-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 80%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
    }
    .header-content { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; }
    .header-info { display: flex; align-items: center; gap: 16px; }
    .header-icon {
        width: 56px; height: 56px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
    }
    .header-text h2 { font-size: 24px; font-weight: 800; margin: 0; }
    .header-text p { font-size: 14px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .stats-row { display: flex; gap: 14px; flex-wrap: wrap; }
    .stat-badge {
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 14px 20px;
        text-align: center;
        min-width: 80px;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .stat-badge-value { font-size: 28px; font-weight: 800; margin: 0; line-height: 1; }
    .stat-badge-label { font-size: 11px; opacity: 0.9; margin-top: 4px; }
    
    .subscribers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 20px;
    }
    @media (max-width: 768px) {
        .subscribers-grid { grid-template-columns: 1fr; }
    }
    
    .subscriber-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .subscriber-card:hover {
        box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        transform: translateY(-4px);
    }
    
    .subscriber-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .subscriber-user { display: flex; align-items: center; gap: 14px; flex: 1; }
    .subscriber-avatar {
        width: 56px; height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex; align-items: center; justify-content: center;
        color: white; font-weight: 800; font-size: 22px;
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
        position: relative;
        flex-shrink: 0;
    }
    .avatar-status {
        position: absolute;
        bottom: -3px; right: -3px;
        width: 16px; height: 16px;
        border-radius: 50%;
        border: 3px solid white;
    }
    .avatar-status.active { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .avatar-status.inactive { background: #9ca3af; }
    
    .subscriber-info { flex: 1; min-width: 0; }
    .subscriber-name {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
        text-decoration: none;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .subscriber-name:hover { color: #667eea; }
    .subscriber-email {
        font-size: 13px;
        color: #6b7280;
        margin: 4px 0 0 0;
        display: flex;
        align-items: center;
        gap: 6px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .enroll-badge {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 12px;
        padding: 10px 14px;
        text-align: center;
        color: white;
        flex-shrink: 0;
    }
    .enroll-badge-label { font-size: 9px; text-transform: uppercase; opacity: 0.9; margin: 0; letter-spacing: 0.5px; }
    .enroll-badge-value { font-size: 13px; font-weight: 700; margin: 4px 0 0 0; }
    
    .subscriber-body { padding: 20px; }
    
    .progress-section {
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        border: 1px solid #bae6fd;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 18px;
    }
    .progress-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .progress-label { font-size: 14px; font-weight: 600; color: #0369a1; margin: 0; display: flex; align-items: center; gap: 8px; }
    .progress-value { font-size: 28px; font-weight: 800; color: #0284c7; margin: 0; }
    .progress-bar-bg {
        height: 12px;
        background: #e0f2fe;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 6px;
        transition: width 0.6s ease;
        position: relative;
    }
    .progress-bar-fill::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }
    @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .progress-bar-fill.low { background: linear-gradient(90deg, #ef4444, #f87171); }
    .progress-bar-fill.medium { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .progress-bar-fill.high { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .progress-bar-fill.complete { background: linear-gradient(90deg, #10b981, #34d399); }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }
    .stat-box {
        border-radius: 14px;
        padding: 16px;
        text-align: center;
        transition: all 0.2s;
    }
    .stat-box:hover { transform: scale(1.02); }
    .stat-box.green { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 1px solid #a7f3d0; }
    .stat-box.orange { background: linear-gradient(135deg, #fff7ed, #ffedd5); border: 1px solid #fed7aa; }
    .stat-box.blue { background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #bfdbfe; }
    
    .stat-box-icon { margin-bottom: 8px; display: flex; justify-content: center; }
    .stat-box-label { font-size: 11px; margin: 0 0 6px 0; font-weight: 500; }
    .stat-box.green .stat-box-label { color: #16a34a; }
    .stat-box.orange .stat-box-label { color: #ea580c; }
    .stat-box.blue .stat-box-label { color: #2563eb; }
    
    .stat-box-value { font-size: 22px; font-weight: 800; margin: 0; }
    .stat-box.green .stat-box-value { color: #15803d; }
    .stat-box.orange .stat-box-value { color: #c2410c; }
    .stat-box.blue .stat-box-value { color: #1d4ed8; }
    
    .info-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }
    .info-item {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }
    .info-item-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .info-item-icon.purple { background: linear-gradient(135deg, #a855f7, #9333ea); }
    .info-item-icon.emerald { background: linear-gradient(135deg, #10b981, #059669); }
    .info-item-text { flex: 1; min-width: 0; }
    .info-item-label { font-size: 10px; color: #6b7280; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-item-value { font-size: 14px; font-weight: 700; color: #1f2937; margin: 2px 0 0 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    
    .status-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 18px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .status-badge.completed { background: linear-gradient(135deg, #10b981, #059669); color: white; }
    .status-badge.in-progress { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
    .status-badge.not-started { background: linear-gradient(135deg, #6b7280, #4b5563); color: white; }
    
    .profile-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
    }
    .profile-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.45);
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 40px;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 24px;
        border: 2px dashed #d1d5db;
    }
    .empty-icon {
        width: 100px; height: 100px;
        background: linear-gradient(135deg, #e5e7eb, #d1d5db);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 24px;
    }
    .empty-title { font-size: 22px; font-weight: 700; color: #374151; margin: 0 0 12px 0; }
    .empty-text { font-size: 15px; color: #6b7280; margin: 0; }
    
    @media (prefers-color-scheme: dark) {
        .subscriber-card { background: #1f2937; border-color: #374151; }
        .subscriber-header { background: linear-gradient(135deg, #1f2937, #374151); border-color: #374151; }
        .subscriber-name, .info-item-value { color: #f9fafb; }
        .subscriber-email, .stat-box-label { color: #9ca3af; }
        .info-item { background: #374151; border-color: #4b5563; }
    }
</style>

@php
    $totalCount = $enrollments->count();
    $completedCount = $enrollments->where('completed_at', '!=', null)->count();
    $inProgressCount = $enrollments->filter(fn($e) => $e->completed_at === null && ($e->progress_percentage ?? 0) > 0)->count();
@endphp

<div class="subscribers-container">
    {{-- Header --}}
    <div class="subscribers-header">
        <div class="header-content">
            <div class="header-info">
                <div class="header-icon">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="header-text">
                    <h2>المشتركون في الدورة</h2>
                    <p>قائمة جميع الطلاب المسجلين وتقدمهم</p>
                </div>
            </div>
            
            <div class="stats-row">
                <div class="stat-badge">
                    <p class="stat-badge-value">{{ $totalCount }}</p>
                    <p class="stat-badge-label">إجمالي</p>
                </div>
                <div class="stat-badge">
                    <p class="stat-badge-value">{{ $completedCount }}</p>
                    <p class="stat-badge-label">أكملوا</p>
                </div>
                <div class="stat-badge">
                    <p class="stat-badge-value">{{ $inProgressCount }}</p>
                    <p class="stat-badge-label">قيد التقدم</p>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Students Grid --}}
    <div class="subscribers-grid">
        @forelse($enrollments as $enrollment)
            @php
                $user = $enrollment->user;
                $completedLessons = \App\Models\VideoProgress::where('user_id', $user->id)
                    ->whereHas('lesson.section', function($q) use ($enrollment) {
                        $q->where('course_id', $enrollment->course_id);
                    })
                    ->where('completed', true)
                    ->count();
                $lastActivity = \App\Models\VideoProgress::where('user_id', $user->id)
                    ->whereHas('lesson.section', function($q) use ($enrollment) {
                        $q->where('course_id', $enrollment->course_id);
                    })
                    ->max('last_watched_at');
                $progress = $enrollment->progress_percentage ?? 0;
                $remainingLessons = max(0, $totalLessons - $completedLessons);
                
                $progressClass = match(true) {
                    $progress >= 100 => 'complete',
                    $progress >= 75 => 'high',
                    $progress >= 25 => 'medium',
                    default => 'low',
                };
                
                $isActive = $lastActivity && \Carbon\Carbon::parse($lastActivity)->isAfter(now()->subDays(7));
            @endphp
            
            <div class="subscriber-card">
                <div class="subscriber-header">
                    <div class="subscriber-user">
                        <div class="subscriber-avatar">
                            {{ mb_substr($user->name, 0, 1) }}
                            <span class="avatar-status {{ $isActive ? 'active' : 'inactive' }}"></span>
                        </div>
                        <div class="subscriber-info">
                            <a href="{{ \App\Filament\Resources\Users\UserResource::getUrl('edit', ['record' => $user->id]) }}" class="subscriber-name">
                                {{ $user->name }}
                            </a>
                            <p class="subscriber-email">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $user->email }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="enroll-badge">
                        <p class="enroll-badge-label">تاريخ الاشتراك</p>
                        <p class="enroll-badge-value">{{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('Y/m/d') : $enrollment->created_at->format('Y/m/d') }}</p>
                    </div>
                </div>
                
                <div class="subscriber-body">
                    {{-- Progress --}}
                    <div class="progress-section">
                        <div class="progress-header">
                            <p class="progress-label">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                نسبة التقدم
                            </p>
                            <p class="progress-value">{{ number_format($progress, 0) }}%</p>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill {{ $progressClass }}" style="width: {{ min(100, $progress) }}%"></div>
                        </div>
                    </div>
                    
                    {{-- Stats Grid --}}
                    <div class="stats-grid">
                        <div class="stat-box green">
                            <div class="stat-box-icon">
                                <svg width="22" height="22" fill="none" stroke="#16a34a" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="stat-box-label">دروس مكتملة</p>
                            <p class="stat-box-value">{{ $completedLessons }}</p>
                        </div>
                        
                        <div class="stat-box orange">
                            <div class="stat-box-icon">
                                <svg width="22" height="22" fill="none" stroke="#ea580c" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="stat-box-label">المتبقية</p>
                            <p class="stat-box-value">{{ $remainingLessons }}</p>
                        </div>
                        
                        <div class="stat-box blue">
                            <div class="stat-box-icon">
                                <svg width="22" height="22" fill="none" stroke="#2563eb" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <p class="stat-box-label">آخر نشاط</p>
                            @if($lastActivity)
                                <p class="stat-box-value" style="font-size: 14px;">{{ \Carbon\Carbon::parse($lastActivity)->diffForHumans() }}</p>
                            @else
                                <p class="stat-box-value" style="font-size: 14px; opacity: 0.5;">—</p>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Info Row --}}
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-item-icon emerald">
                                <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="info-item-text">
                                <p class="info-item-label">السعر المدفوع</p>
                                <p class="info-item-value">{{ number_format($enrollment->price_paid ?? 0, 2) }} ج.م</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-icon purple">
                                <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                            </div>
                            <div class="info-item-text">
                                <p class="info-item-label">تاريخ الإكمال</p>
                                <p class="info-item-value">{{ $enrollment->completed_at ? $enrollment->completed_at->format('Y/m/d') : 'جاري العمل...' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Footer --}}
                    <div class="status-footer">
                        @if($enrollment->completed_at)
                            <span class="status-badge completed">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                أكمل الدورة
                            </span>
                        @elseif($progress > 0)
                            <span class="status-badge in-progress">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                قيد التقدم
                            </span>
                        @else
                            <span class="status-badge not-started">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                لم يبدأ بعد
                            </span>
                        @endif
                        
                        <a href="{{ \App\Filament\Resources\Users\UserResource::getUrl('edit', ['record' => $user->id]) }}" class="profile-btn">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            عرض الملف
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">
                    <svg width="40" height="40" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="empty-title">لا يوجد مشتركون</p>
                <p class="empty-text">لم يتم تسجيل أي طلاب في هذه الدورة بعد</p>
            </div>
        @endforelse
    </div>
</div>
