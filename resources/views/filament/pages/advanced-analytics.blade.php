<x-filament-panels::page>
    <style>
        .analytics-container { max-width: 100%; }
        
        .analytics-header {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 50%, #14b8a6 100%);
            border-radius: 16px;
            padding: 24px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(14, 165, 233, 0.3);
        }
        .analytics-header-top {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .analytics-header-icon {
            width: 52px; height: 52px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .analytics-title { font-size: 24px; font-weight: 700; margin: 0; }
        .analytics-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: white;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
            transition: transform 0.2s;
        }
        .stat-box:hover { transform: translateY(-2px); }
        .stat-icon {
            width: 40px; height: 40px;
            margin: 0 auto 10px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .stat-icon.cyan { background: linear-gradient(135deg, #ecfeff, #cffafe); color: #0891b2; }
        .stat-icon.purple { background: linear-gradient(135deg, #faf5ff, #f3e8ff); color: #9333ea; }
        .stat-icon.green { background: linear-gradient(135deg, #f0fdf4, #dcfce7); color: #16a34a; }
        .stat-icon.amber { background: linear-gradient(135deg, #fffbeb, #fef3c7); color: #d97706; }
        .stat-icon.blue { background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #2563eb; }
        .stat-icon.rose { background: linear-gradient(135deg, #fff1f2, #ffe4e6); color: #e11d48; }
        .stat-value { font-size: 24px; font-weight: 700; color: #1f2937; margin: 0; }
        .stat-label { font-size: 11px; color: #6b7280; margin: 4px 0 0 0; }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 1024px) {
            .charts-grid { grid-template-columns: 1fr; }
        }
        
        .chart-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
        }
        .chart-card.full-width { grid-column: span 2; }
        @media (max-width: 1024px) {
            .chart-card.full-width { grid-column: span 1; }
        }
        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .chart-title { font-size: 15px; font-weight: 600; color: #1f2937; margin: 0; }
        .chart-container { height: 260px; position: relative; }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
        }
        .metric-card {
            background: linear-gradient(135deg, #f0fdfa, #ccfbf1);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid #99f6e4;
        }
        .metric-value { font-size: 22px; font-weight: 700; color: #0f766e; margin: 0; }
        .metric-label { font-size: 12px; color: #14b8a6; margin: 4px 0 0 0; }
        
        .side-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 1024px) {
            .side-grid { grid-template-columns: 1fr; }
        }
        
        .list-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
        }
        .list-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .list-title { font-size: 15px; font-weight: 600; color: #1f2937; margin: 0; }
        .list-body { padding: 12px 20px; }
        
        .instructor-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .instructor-item:last-child { border-bottom: none; }
        .instructor-avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0ea5e9, #14b8a6);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600; font-size: 14px;
        }
        .instructor-info { flex: 1; }
        .instructor-name { font-size: 13px; font-weight: 600; color: #1f2937; margin: 0; }
        .instructor-stats { font-size: 11px; color: #6b7280; margin: 2px 0 0 0; }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .activity-icon.enrollment { background: #dbeafe; color: #2563eb; }
        .activity-icon.order { background: #dcfce7; color: #16a34a; }
        .activity-content { flex: 1; }
        .activity-text { font-size: 12px; color: #374151; margin: 0; }
        .activity-time { font-size: 10px; color: #9ca3af; margin: 2px 0 0 0; }
        
        .heatmap-container { display: flex; gap: 4px; flex-wrap: wrap; }
        .heatmap-cell {
            width: 32px; height: 32px;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 500;
        }
        .heatmap-cell.h0 { background: #f3f4f6; color: #9ca3af; }
        .heatmap-cell.h1 { background: #d1fae5; color: #059669; }
        .heatmap-cell.h2 { background: #6ee7b7; color: #047857; }
        .heatmap-cell.h3 { background: #34d399; color: #065f46; }
        .heatmap-cell.h4 { background: #10b981; color: white; }
        
        .day-bars { display: flex; flex-direction: column; gap: 8px; }
        .day-bar-item { display: flex; align-items: center; gap: 10px; }
        .day-label { width: 60px; font-size: 11px; color: #6b7280; }
        .day-bar-bg {
            flex: 1;
            height: 20px;
            background: #f3f4f6;
            border-radius: 4px;
            overflow: hidden;
        }
        .day-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #0ea5e9, #14b8a6);
            border-radius: 4px;
            transition: width 0.5s;
        }
        .day-count { font-size: 11px; font-weight: 600; color: #374151; width: 40px; text-align: left; }
        
        @media (prefers-color-scheme: dark) {
            .stat-box, .chart-card, .list-card { background: #1f2937; border-color: #374151; }
            .stat-value, .chart-title, .list-title, .instructor-name { color: white; }
            .list-header { border-color: #374151; }
            .instructor-item, .activity-item { border-color: #374151; }
            .metric-card { background: linear-gradient(135deg, #064e3b, #065f46); border-color: #10b981; }
            .metric-value { color: #6ee7b7; }
            .metric-label { color: #34d399; }
            .heatmap-cell.h0 { background: #374151; color: #6b7280; }
            .day-bar-bg { background: #374151; }
            .activity-text { color: #d1d5db; }
        }
    </style>

    @php
        $stats = $this->platformStats;
        $engagement = $this->engagementMetrics;
        $hourlyActivity = $this->hourlyActivity;
        $maxHourly = max($hourlyActivity) ?: 1;
        $dayActivity = $this->dayOfWeekActivity;
        $maxDay = max(array_values($dayActivity)) ?: 1;
    @endphp

    <div class="analytics-container">
        {{-- Header --}}
        <div class="analytics-header">
            <div class="analytics-header-top">
                <div class="analytics-header-icon">
                    <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="analytics-title">التحليلات المتقدمة</h1>
                    <p class="analytics-subtitle">نظرة شاملة على أداء المنصة</p>
                </div>
            </div>
        </div>
        
        {{-- Platform Stats --}}
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-icon cyan">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="stat-value">{{ number_format($stats['total_users']) }}</p>
                <p class="stat-label">إجمالي المستخدمين</p>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon purple">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <p class="stat-value">{{ number_format($stats['total_courses']) }}</p>
                <p class="stat-label">إجمالي الدورات</p>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon green">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="stat-value">{{ number_format($stats['total_enrollments']) }}</p>
                <p class="stat-label">إجمالي التسجيلات</p>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon amber">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="stat-value">{{ number_format($stats['total_revenue'], 0) }} ج.م</p>
                <p class="stat-label">إجمالي الإيرادات</p>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon blue">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <p class="stat-value">{{ number_format($stats['active_users_today']) }}</p>
                <p class="stat-label">نشطون اليوم</p>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon rose">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <p class="stat-value">{{ number_format($stats['new_users_this_month']) }}</p>
                <p class="stat-label">مستخدمون جدد هذا الشهر</p>
            </div>
        </div>
        
        {{-- Engagement Metrics --}}
        <div class="metrics-grid">
            <div class="metric-card">
                <p class="metric-value">{{ number_format($engagement['total_watch_hours']) }}h</p>
                <p class="metric-label">ساعات المشاهدة الكلية</p>
            </div>
            <div class="metric-card">
                <p class="metric-value">{{ $engagement['avg_watch_minutes'] }}m</p>
                <p class="metric-label">متوسط وقت المشاهدة</p>
            </div>
            <div class="metric-card">
                <p class="metric-value">{{ $engagement['lesson_completion_rate'] }}%</p>
                <p class="metric-label">معدل إكمال الدروس</p>
            </div>
            <div class="metric-card">
                <p class="metric-value">{{ number_format($engagement['completed_lessons']) }}</p>
                <p class="metric-label">دروس مكتملة</p>
            </div>
        </div>
        
        {{-- Charts --}}
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">📈 نمو المستخدمين</h3>
                </div>
                <div class="chart-container">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">💰 نمو الإيرادات</h3>
                </div>
                <div class="chart-container">
                    <canvas id="revenueGrowthChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">🕐 النشاط حسب الساعة</h3>
                </div>
                <div class="heatmap-container" style="padding: 10px;">
                    @foreach($hourlyActivity as $hour => $count)
                        @php
                            $intensity = $count / $maxHourly;
                            $class = match(true) {
                                $intensity >= 0.8 => 'h4',
                                $intensity >= 0.6 => 'h3',
                                $intensity >= 0.4 => 'h2',
                                $intensity > 0 => 'h1',
                                default => 'h0',
                            };
                        @endphp
                        <div class="heatmap-cell {{ $class }}" title="{{ $hour }}:00 - {{ $count }} نشاط">
                            {{ $hour }}
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">📅 النشاط حسب اليوم</h3>
                </div>
                <div class="day-bars" style="padding: 10px 0;">
                    @foreach($dayActivity as $day => $count)
                        <div class="day-bar-item">
                            <span class="day-label">{{ $day }}</span>
                            <div class="day-bar-bg">
                                <div class="day-bar-fill" style="width: {{ ($count / $maxDay) * 100 }}%"></div>
                            </div>
                            <span class="day-count">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        {{-- Side Lists --}}
        <div class="side-grid">
            <div class="list-card">
                <div class="list-header">
                    <h3 class="list-title">👨‍🏫 أفضل المدرسين</h3>
                </div>
                <div class="list-body">
                    @forelse($this->topInstructors as $instructor)
                        <div class="instructor-item">
                            <div class="instructor-avatar">{{ mb_substr($instructor->name, 0, 1) }}</div>
                            <div class="instructor-info">
                                <p class="instructor-name">{{ $instructor->name }}</p>
                                <p class="instructor-stats">{{ $instructor->courses_count }} دورة</p>
                            </div>
                        </div>
                    @empty
                        <p style="text-align: center; color: #9ca3af; padding: 20px;">لا توجد بيانات</p>
                    @endforelse
                </div>
            </div>
            
            <div class="list-card">
                <div class="list-header">
                    <h3 class="list-title">🔔 آخر النشاطات</h3>
                </div>
                <div class="list-body">
                    @forelse($this->recentActivity as $activity)
                        <div class="activity-item">
                            <div class="activity-icon {{ $activity['type'] }}">
                                @if($activity['type'] === 'enrollment')
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                @else
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="activity-content">
                                <p class="activity-text">
                                    <strong>{{ $activity['user'] ?? 'مستخدم' }}</strong>
                                    {{ $activity['type'] === 'enrollment' ? 'سجل في' : 'دفع' }}
                                    {{ $activity['detail'] }}
                                </p>
                                <p class="activity-time">{{ $activity['time']->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p style="text-align: center; color: #9ca3af; padding: 20px;">لا توجد نشاطات</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userGrowth = @json($this->userGrowth);
            const revenueGrowth = @json($this->revenueGrowth);
            
            // User Growth Chart
            if (userGrowth.labels.length > 0) {
                new Chart(document.getElementById('userGrowthChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: userGrowth.labels,
                        datasets: [{
                            label: 'المستخدمون الجدد',
                            data: userGrowth.values,
                            borderColor: '#0ea5e9',
                            backgroundColor: 'rgba(14, 165, 233, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }
            
            // Revenue Growth Chart
            if (revenueGrowth.labels.length > 0) {
                new Chart(document.getElementById('revenueGrowthChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: revenueGrowth.labels,
                        datasets: [{
                            label: 'الإيرادات',
                            data: revenueGrowth.values,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: '#10b981',
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }
        });
    </script>
</x-filament-panels::page>
