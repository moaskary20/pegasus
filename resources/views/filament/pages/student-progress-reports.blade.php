<x-filament-panels::page>
    <style>
        .progress-reports-container { max-width: 100%; }
        
        .progress-header {
            background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 50%, #a78bfa 100%);
            border-radius: 16px;
            padding: 24px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
        }
        .progress-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        .progress-header-info { display: flex; align-items: center; gap: 14px; }
        .export-btn {
            display: flex; align-items: center; gap: 6px;
            padding: 10px 18px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            color: white;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            backdrop-filter: blur(5px);
            text-decoration: none;
        }
        .export-btn:hover { background: rgba(255,255,255,0.25); }
        .progress-header-icon {
            width: 52px; height: 52px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .progress-title { font-size: 24px; font-weight: 700; margin: 0; }
        .progress-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .stat-value { font-size: 28px; font-weight: 700; color: #1f2937; margin: 0; }
        .stat-label { font-size: 12px; color: #6b7280; margin: 6px 0 0 0; }
        .stat-card.purple .stat-value { color: #7c3aed; }
        .stat-card.green .stat-value { color: #16a34a; }
        .stat-card.blue .stat-value { color: #2563eb; }
        .stat-card.amber .stat-value { color: #d97706; }
        .stat-card.gray .stat-value { color: #6b7280; }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 1024px) {
            .content-grid { grid-template-columns: 1fr; }
        }
        
        .card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .card-title { font-size: 16px; font-weight: 600; color: #1f2937; margin: 0; }
        .card-body { padding: 16px 20px; }
        
        .filters-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            background: white;
            min-width: 150px;
        }
        .filter-tabs {
            display: flex;
            background: #f3f4f6;
            border-radius: 8px;
            padding: 3px;
        }
        .filter-tab {
            padding: 6px 14px;
            border: none;
            background: transparent;
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .filter-tab.active {
            background: white;
            color: #7c3aed;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .search-input {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            min-width: 200px;
        }
        
        .students-list { display: flex; flex-direction: column; }
        .student-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .student-item:last-child { border-bottom: none; }
        
        .student-avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600; font-size: 16px;
            flex-shrink: 0;
        }
        .student-info { flex: 1; min-width: 0; }
        .student-name { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0; }
        .student-course {
            font-size: 12px;
            color: #6b7280;
            margin: 2px 0 0 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .progress-bar-container { width: 120px; }
        .progress-bar-bg {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        .progress-bar-fill.low { background: #ef4444; }
        .progress-bar-fill.medium { background: #f59e0b; }
        .progress-bar-fill.high { background: #22c55e; }
        .progress-bar-fill.complete { background: linear-gradient(135deg, #10b981, #34d399); }
        .progress-text {
            font-size: 11px;
            color: #6b7280;
            text-align: center;
            margin-top: 3px;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-badge.completed { background: #dcfce7; color: #16a34a; }
        .status-badge.in-progress { background: #dbeafe; color: #2563eb; }
        .status-badge.not-started { background: #f3f4f6; color: #6b7280; }
        
        .chart-container { height: 200px; position: relative; }
        
        .distribution-bars { display: flex; flex-direction: column; gap: 10px; }
        .distribution-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .distribution-label {
            width: 60px;
            font-size: 12px;
            color: #6b7280;
            text-align: left;
        }
        .distribution-bar-bg {
            flex: 1;
            height: 24px;
            background: #f3f4f6;
            border-radius: 6px;
            overflow: hidden;
            position: relative;
        }
        .distribution-bar-fill {
            height: 100%;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 8px;
            font-size: 11px;
            font-weight: 600;
            color: white;
            transition: width 0.5s ease;
        }
        .distribution-bar-fill.d0 { background: #ef4444; }
        .distribution-bar-fill.d1 { background: #f97316; }
        .distribution-bar-fill.d2 { background: #eab308; }
        .distribution-bar-fill.d3 { background: #84cc16; }
        .distribution-bar-fill.d4 { background: #22c55e; }
        .distribution-bar-fill.d5 { background: #10b981; }
        
        .top-students { display: flex; flex-direction: column; gap: 8px; }
        .top-student-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .top-student-rank {
            width: 24px; height: 24px;
            background: #7c3aed;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 11px;
        }
        .top-student-name { flex: 1; font-size: 13px; font-weight: 500; color: #1f2937; }
        .top-student-date { font-size: 11px; color: #9ca3af; }
        
        .pagination-wrapper { padding: 16px 20px; border-top: 1px solid #e5e7eb; }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }
        
        @media (prefers-color-scheme: dark) {
            .stat-card, .card { background: #1f2937; border-color: #374151; }
            .stat-value { color: white !important; }
            .card-title, .student-name, .top-student-name { color: white; }
            .card-header { border-color: #374151; }
            .student-item { border-color: #374151; }
            .filter-tabs { background: #374151; }
            .filter-tab.active { background: #4b5563; color: #a78bfa; }
            .filter-select, .search-input { background: #374151; border-color: #4b5563; color: white; }
            .top-student-item { background: #374151; }
            .progress-bar-bg, .distribution-bar-bg { background: #374151; }
            .pagination-wrapper { border-color: #374151; }
        }
    </style>

    @php
        $stats = $this->overallStats;
        $distribution = $this->progressDistribution;
        $maxDist = max(array_values($distribution)) ?: 1;
    @endphp

    <div class="progress-reports-container">
        {{-- Header --}}
        <div class="progress-header">
            <div class="progress-header-top">
                <div class="progress-header-info">
                    <div class="progress-header-icon">
                        <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="progress-title">تقارير تقدم الطلاب</h1>
                        <p class="progress-subtitle">تتبع تقدم طلابك في الدورات</p>
                    </div>
                </div>
                
                <a href="#" wire:click.prevent="exportExcel" class="export-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    تصدير Excel
                </a>
            </div>
        </div>
        
        {{-- Stats Grid --}}
        <div class="stats-grid">
            <div class="stat-card purple">
                <p class="stat-value">{{ number_format($stats['total']) }}</p>
                <p class="stat-label">إجمالي التسجيلات</p>
            </div>
            <div class="stat-card green">
                <p class="stat-value">{{ number_format($stats['completed']) }}</p>
                <p class="stat-label">مكتمل</p>
            </div>
            <div class="stat-card blue">
                <p class="stat-value">{{ number_format($stats['in_progress']) }}</p>
                <p class="stat-label">قيد التقدم</p>
            </div>
            <div class="stat-card gray">
                <p class="stat-value">{{ number_format($stats['not_started']) }}</p>
                <p class="stat-label">لم يبدأ</p>
            </div>
            <div class="stat-card amber">
                <p class="stat-value">{{ $stats['avg_progress'] }}%</p>
                <p class="stat-label">متوسط التقدم</p>
            </div>
            <div class="stat-card green">
                <p class="stat-value">{{ $stats['completion_rate'] }}%</p>
                <p class="stat-label">معدل الإكمال</p>
            </div>
        </div>
        
        {{-- Content Grid --}}
        <div class="content-grid">
            {{-- Students List --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📋 قائمة الطلاب</h3>
                    <div class="filters-row">
                        <select wire:model.live="courseFilter" class="filter-select">
                            <option value="all">كل الدورات</option>
                            @foreach($this->courses as $course)
                                <option value="{{ $course->id }}">{{ Str::limit($course->title, 30) }}</option>
                            @endforeach
                        </select>
                        
                        <div class="filter-tabs">
                            <button wire:click="setProgressFilter('all')" class="filter-tab {{ $progressFilter === 'all' ? 'active' : '' }}">الكل</button>
                            <button wire:click="setProgressFilter('completed')" class="filter-tab {{ $progressFilter === 'completed' ? 'active' : '' }}">مكتمل</button>
                            <button wire:click="setProgressFilter('in_progress')" class="filter-tab {{ $progressFilter === 'in_progress' ? 'active' : '' }}">قيد التقدم</button>
                            <button wire:click="setProgressFilter('not_started')" class="filter-tab {{ $progressFilter === 'not_started' ? 'active' : '' }}">لم يبدأ</button>
                        </div>
                        
                        <input type="text" wire:model.live.debounce.300ms="search" class="search-input" placeholder="بحث عن طالب...">
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="students-list">
                        @forelse($this->enrollments as $enrollment)
                            @php
                                $progress = $enrollment->progress_percentage ?? 0;
                                $progressClass = match(true) {
                                    $progress >= 100 => 'complete',
                                    $progress >= 75 => 'high',
                                    $progress >= 25 => 'medium',
                                    default => 'low',
                                };
                                $statusClass = match(true) {
                                    $enrollment->completed_at !== null => 'completed',
                                    $progress > 0 => 'in-progress',
                                    default => 'not-started',
                                };
                                $statusLabel = match(true) {
                                    $enrollment->completed_at !== null => 'مكتمل',
                                    $progress > 0 => 'قيد التقدم',
                                    default => 'لم يبدأ',
                                };
                            @endphp
                            
                            <div class="student-item">
                                <div class="student-avatar">{{ mb_substr($enrollment->user->name ?? '?', 0, 1) }}</div>
                                
                                <div class="student-info">
                                    <p class="student-name">{{ $enrollment->user->name ?? 'غير معروف' }}</p>
                                    <p class="student-course">{{ $enrollment->course->title ?? 'غير معروف' }}</p>
                                </div>
                                
                                <div class="progress-bar-container">
                                    <div class="progress-bar-bg">
                                        <div class="progress-bar-fill {{ $progressClass }}" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <p class="progress-text">{{ round($progress) }}%</p>
                                </div>
                                
                                <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                        @empty
                            <div class="empty-state">
                                <p>لا توجد نتائج</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                
                @if($this->enrollments->hasPages())
                    <div class="pagination-wrapper">
                        {{ $this->enrollments->links() }}
                    </div>
                @endif
            </div>
            
            {{-- Side Panel --}}
            <div style="display: flex; flex-direction: column; gap: 20px;">
                {{-- Progress Distribution --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📊 توزيع التقدم</h3>
                    </div>
                    <div class="card-body">
                        <div class="distribution-bars">
                            @foreach($distribution as $range => $count)
                                @php
                                    $percent = ($count / $maxDist) * 100;
                                    $class = match($range) {
                                        '0%' => 'd0',
                                        '1-25%' => 'd1',
                                        '26-50%' => 'd2',
                                        '51-75%' => 'd3',
                                        '76-99%' => 'd4',
                                        '100%' => 'd5',
                                        default => 'd0',
                                    };
                                @endphp
                                <div class="distribution-item">
                                    <span class="distribution-label">{{ $range }}</span>
                                    <div class="distribution-bar-bg">
                                        <div class="distribution-bar-fill {{ $class }}" style="width: {{ max(10, $percent) }}%">
                                            {{ $count }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                {{-- Top Completions --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🎓 آخر الإكمالات</h3>
                    </div>
                    <div class="card-body">
                        <div class="top-students">
                            @forelse($this->topStudents as $index => $enrollment)
                                <div class="top-student-item">
                                    <div class="top-student-rank">{{ $index + 1 }}</div>
                                    <span class="top-student-name">{{ $enrollment->user->name ?? 'غير معروف' }}</span>
                                    <span class="top-student-date">{{ $enrollment->completed_at->diffForHumans() }}</span>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <p>لا توجد إكمالات بعد</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
