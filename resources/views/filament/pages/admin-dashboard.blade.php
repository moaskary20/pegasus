<x-filament-panels::page>
    <style>
        .dashboard-container { max-width: 100%; }
        
        .welcome-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }
        .welcome-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        }
        .welcome-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .welcome-text h1 { font-size: 28px; font-weight: 800; margin: 0; }
        .welcome-text p { font-size: 15px; opacity: 0.9; margin: 8px 0 0 0; }
        .welcome-date {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 14px 20px;
            border-radius: 14px;
            text-align: center;
        }
        .welcome-date-day { font-size: 32px; font-weight: 800; line-height: 1; }
        .welcome-date-month { font-size: 13px; opacity: 0.9; margin-top: 4px; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            align-items: flex-start;
            gap: 14px;
            transition: all 0.3s;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .stat-icon.blue { background: linear-gradient(135deg, #3b82f6, #6366f1); }
        .stat-icon.green { background: linear-gradient(135deg, #10b981, #34d399); }
        .stat-icon.purple { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
        .stat-icon.amber { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .stat-icon.rose { background: linear-gradient(135deg, #f43f5e, #fb7185); }
        .stat-icon.cyan { background: linear-gradient(135deg, #06b6d4, #22d3ee); }
        .stat-icon.indigo { background: linear-gradient(135deg, #6366f1, #818cf8); }
        .stat-icon.emerald { background: linear-gradient(135deg, #059669, #10b981); }
        
        .stat-content { flex: 1; }
        .stat-value { font-size: 26px; font-weight: 800; color: #1f2937; margin: 0; }
        .stat-label { font-size: 13px; color: #6b7280; margin: 4px 0 0 0; }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        @media (max-width: 1024px) {
            .content-grid { grid-template-columns: 1fr; }
        }
        
        .card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 8px; }
        .card-body { padding: 18px 22px; }
        
        .chart-container { height: 280px; position: relative; }
        
        .enrollment-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .enrollment-item:last-child { border-bottom: none; }
        .enrollment-avatar {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 16px;
            flex-shrink: 0;
        }
        .enrollment-info { flex: 1; min-width: 0; }
        .enrollment-name { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0; }
        .enrollment-course {
            font-size: 12px;
            color: #6b7280;
            margin: 3px 0 0 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .enrollment-time { font-size: 11px; color: #9ca3af; white-space: nowrap; }
        
        .top-course-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .top-course-item:last-child { border-bottom: none; }
        .top-course-rank {
            width: 28px; height: 28px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 12px;
            flex-shrink: 0;
        }
        .top-course-rank.r1 { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; }
        .top-course-rank.r2 { background: linear-gradient(135deg, #9ca3af, #6b7280); color: white; }
        .top-course-rank.r3 { background: linear-gradient(135deg, #d97706, #b45309); color: white; }
        .top-course-rank.r4, .top-course-rank.r5 { background: #f3f4f6; color: #6b7280; }
        .top-course-info { flex: 1; min-width: 0; }
        .top-course-name {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .top-course-count { font-size: 14px; font-weight: 700; color: #6366f1; white-space: nowrap; }
        
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }
        .quick-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 18px 14px;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 14px;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid #e5e7eb;
        }
        .quick-link:hover { background: linear-gradient(135deg, #e0e7ff, #c7d2fe); border-color: #a5b4fc; }
        .quick-link-icon {
            width: 42px; height: 42px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .quick-link-icon.blue { background: linear-gradient(135deg, #3b82f6, #6366f1); }
        .quick-link-icon.green { background: linear-gradient(135deg, #10b981, #34d399); }
        .quick-link-icon.purple { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
        .quick-link-icon.amber { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .quick-link-label { font-size: 12px; font-weight: 600; color: #374151; text-align: center; }
        
        .empty-state { text-align: center; padding: 30px; color: #9ca3af; }
        
        @media (prefers-color-scheme: dark) {
            .stat-card, .card { background: #1f2937; border-color: #374151; }
            .stat-value, .card-title, .enrollment-name, .top-course-name { color: white; }
            .card-header { border-color: #374151; }
            .enrollment-item, .top-course-item { border-color: #374151; }
            .quick-link { background: linear-gradient(135deg, #374151, #4b5563); border-color: #4b5563; }
            .quick-link:hover { background: linear-gradient(135deg, #4b5563, #6b7280); }
            .quick-link-label { color: #d1d5db; }
        }
    </style>

    @php
        $stats = $this->stats;
        $user = auth()->user();
    @endphp

    <div class="dashboard-container">
        {{-- Welcome Header --}}
        <div class="welcome-header">
            <div class="welcome-content">
                <div class="welcome-text">
                    <h1>مرحباً، {{ $user->name }} 👋</h1>
                    <p>{{ $this->isAdmin ? 'مرحباً بك في لوحة تحكم المدير' : 'مرحباً بك في لوحة تحكم المدرس' }}</p>
                </div>
                <div class="welcome-date">
                    <div class="welcome-date-day">{{ now()->format('d') }}</div>
                    <div class="welcome-date-month">{{ now()->translatedFormat('F Y') }}</div>
                </div>
            </div>
        </div>
        
        {{-- Stats Grid --}}
        <div class="stats-grid">
            @if($this->isAdmin)
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <p class="stat-value">{{ number_format($stats['users']) }}</p>
                        <p class="stat-label">إجمالي المستخدمين</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <p class="stat-value">{{ number_format($stats['categories']) }}</p>
                        <p class="stat-label">التصنيفات</p>
                    </div>
                </div>
            @endif
            
            <div class="stat-card">
                <div class="stat-icon indigo">
                    <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">{{ number_format($stats['courses']) }}</p>
                    <p class="stat-label">{{ $this->isAdmin ? 'إجمالي الدورات' : 'دوراتي' }}</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">{{ number_format($stats['published_courses']) }}</p>
                    <p class="stat-label">الدورات المنشورة</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon cyan">
                    <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">{{ number_format($stats['enrollments']) }}</p>
                    <p class="stat-label">التسجيلات</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon emerald">
                    <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">{{ number_format($stats['completed_courses']) }}</p>
                    <p class="stat-label">دورات مكتملة</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon amber">
                    <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">{{ number_format($stats['revenue'] ?? 0) }} ج.م</p>
                    <p class="stat-label">الإيرادات</p>
                </div>
            </div>
        </div>
        
        {{-- Content Grid --}}
        <div class="content-grid">
            {{-- Chart --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span>📈</span>
                        التسجيلات الجديدة
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="enrollmentChart"></canvas>
                    </div>
                </div>
            </div>
            
            {{-- Quick Links --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span>⚡</span>
                        وصول سريع
                    </h3>
                </div>
                <div class="card-body">
                    <div class="quick-links">
                        @if($this->isAdmin)
                            <a href="{{ route('filament.admin.resources.users.index') }}" class="quick-link">
                                <div class="quick-link-icon blue">
                                    <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                                <span class="quick-link-label">المستخدمون</span>
                            </a>
                            <a href="{{ route('filament.admin.resources.categories.index') }}" class="quick-link">
                                <div class="quick-link-icon purple">
                                    <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                </div>
                                <span class="quick-link-label">التصنيفات</span>
                            </a>
                        @endif
                        <a href="{{ route('filament.admin.resources.courses.index') }}" class="quick-link">
                            <div class="quick-link-icon green">
                                <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <span class="quick-link-label">الدورات</span>
                        </a>
                        <a href="{{ route('filament.admin.pages.sales-reports') }}" class="quick-link">
                            <div class="quick-link-icon amber">
                                <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <span class="quick-link-label">التقارير</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Bottom Grid --}}
        <div class="content-grid">
            {{-- Recent Enrollments --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span>🎓</span>
                        آخر التسجيلات
                    </h3>
                </div>
                <div class="card-body">
                    @forelse($this->recentEnrollments as $enrollment)
                        <div class="enrollment-item">
                            <div class="enrollment-avatar">{{ mb_substr($enrollment->user->name ?? '?', 0, 1) }}</div>
                            <div class="enrollment-info">
                                <p class="enrollment-name">{{ $enrollment->user->name ?? 'غير معروف' }}</p>
                                <p class="enrollment-course">{{ $enrollment->course->title ?? 'غير معروف' }}</p>
                            </div>
                            <span class="enrollment-time">{{ $enrollment->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="empty-state">لا توجد تسجيلات حديثة</div>
                    @endforelse
                </div>
            </div>
            
            {{-- Top Courses --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span>🏆</span>
                        أفضل الدورات
                    </h3>
                </div>
                <div class="card-body">
                    @forelse($this->topCourses as $index => $course)
                        <div class="top-course-item">
                            <div class="top-course-rank r{{ $index + 1 }}">{{ $index + 1 }}</div>
                            <div class="top-course-info">
                                <p class="top-course-name">{{ $course->title }}</p>
                            </div>
                            <span class="top-course-count">{{ $course->enrollments_count }}</span>
                        </div>
                    @empty
                        <div class="empty-state">لا توجد دورات</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($this->enrollmentChart);
            
            if (chartData.labels.length > 0) {
                new Chart(document.getElementById('enrollmentChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'التسجيلات',
                            data: chartData.values,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#6366f1',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
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
