<x-filament-panels::page>
    <style>
        .reports-container { max-width: 100%; }
        
        .reports-header {
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
            border-radius: 16px;
            padding: 24px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(5, 150, 105, 0.3);
        }
        .reports-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
        }
        .reports-header-info { display: flex; align-items: center; gap: 14px; }
        .reports-header-icon {
            width: 52px; height: 52px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .reports-title { font-size: 24px; font-weight: 700; margin: 0; }
        .reports-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
        
        .export-buttons { display: flex; gap: 10px; }
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
        }
        .export-btn:hover { background: rgba(255,255,255,0.25); }
        
        .period-tabs {
            display: flex;
            gap: 8px;
            background: rgba(255,255,255,0.1);
            padding: 6px;
            border-radius: 10px;
        }
        .period-tab {
            padding: 8px 16px;
            border: none;
            background: transparent;
            color: rgba(255,255,255,0.8);
            font-size: 13px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .period-tab.active { background: white; color: #059669; }
        .period-tab:hover:not(.active) { background: rgba(255,255,255,0.1); }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .stat-icon.green { background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #16a34a; }
        .stat-icon.blue { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #2563eb; }
        .stat-icon.purple { background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #7c3aed; }
        .stat-icon.amber { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706; }
        
        .stat-content { flex: 1; }
        .stat-value { font-size: 26px; font-weight: 700; color: #1f2937; margin: 0; }
        .stat-label { font-size: 13px; color: #6b7280; margin: 4px 0 0 0; }
        .stat-change {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 6px;
            padding: 3px 8px;
            border-radius: 6px;
        }
        .stat-change.positive { background: #dcfce7; color: #16a34a; }
        .stat-change.negative { background: #fee2e2; color: #dc2626; }
        
        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 1024px) {
            .charts-section { grid-template-columns: 1fr; }
        }
        
        .chart-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
        }
        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .chart-title { font-size: 16px; font-weight: 600; color: #1f2937; margin: 0; }
        .chart-container { height: 280px; position: relative; }
        
        .top-courses-list { display: flex; flex-direction: column; gap: 10px; }
        .top-course-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 10px;
            transition: background 0.2s;
        }
        .top-course-item:hover { background: #f3f4f6; }
        .top-course-rank {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 12px;
            flex-shrink: 0;
        }
        .top-course-rank.gold { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .top-course-rank.silver { background: linear-gradient(135deg, #9ca3af, #d1d5db); }
        .top-course-rank.bronze { background: linear-gradient(135deg, #b45309, #d97706); }
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
        .top-course-enrollments { font-size: 11px; color: #6b7280; margin: 2px 0 0 0; }
        
        .recent-orders {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
        }
        .recent-orders-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .recent-orders-title { font-size: 16px; font-weight: 600; color: #1f2937; margin: 0; }
        
        .orders-table { width: 100%; border-collapse: collapse; }
        .orders-table th {
            text-align: right;
            padding: 12px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .orders-table td {
            padding: 14px 16px;
            font-size: 13px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }
        .orders-table tr:last-child td { border-bottom: none; }
        .orders-table tr:hover td { background: #f9fafb; }
        
        .order-user { display: flex; align-items: center; gap: 10px; }
        .order-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600; font-size: 12px;
        }
        .order-amount { font-weight: 600; color: #059669; }
        .order-date { color: #9ca3af; font-size: 12px; }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }
        .empty-icon {
            width: 60px; height: 60px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 12px;
        }
        
        @media (prefers-color-scheme: dark) {
            .stat-card, .chart-card, .recent-orders { background: #1f2937; border-color: #374151; }
            .stat-value, .chart-title, .recent-orders-title { color: white; }
            .top-course-item { background: #374151; }
            .top-course-item:hover { background: #4b5563; }
            .top-course-name { color: white; }
            .orders-table th { background: #374151; color: #9ca3af; border-color: #4b5563; }
            .orders-table td { color: #d1d5db; border-color: #374151; }
            .orders-table tr:hover td { background: #374151; }
            .empty-icon { background: #374151; }
        }
    </style>

    <div class="reports-container">
        {{-- Header --}}
        <div class="reports-header">
            <div class="reports-header-top">
                <div class="reports-header-info">
                    <div class="reports-header-icon">
                        <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="reports-title">تقارير المبيعات</h1>
                        <p class="reports-subtitle">تحليل شامل لمبيعات دوراتك</p>
                    </div>
                </div>
                
                <div class="export-buttons">
                    <a href="#" wire:click.prevent="exportExcel" class="export-btn">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        تصدير Excel
                    </a>
                </div>
            </div>
            
            <div class="period-tabs">
                <button wire:click="setPeriod('week')" class="period-tab {{ $period === 'week' ? 'active' : '' }}">أسبوع</button>
                <button wire:click="setPeriod('month')" class="period-tab {{ $period === 'month' ? 'active' : '' }}">شهر</button>
                <button wire:click="setPeriod('quarter')" class="period-tab {{ $period === 'quarter' ? 'active' : '' }}">ربع سنة</button>
                <button wire:click="setPeriod('year')" class="period-tab {{ $period === 'year' ? 'active' : '' }}">سنة</button>
                <button wire:click="setPeriod('all')" class="period-tab {{ $period === 'all' ? 'active' : '' }}">الكل</button>
            </div>
        </div>
        
        {{-- Stats Grid --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">{{ number_format($this->totalRevenue, 2) }} ج.م</p>
                    <p class="stat-label">إجمالي الإيرادات</p>
                    @if($this->monthlyGrowth != 0)
                        <span class="stat-change {{ $this->monthlyGrowth > 0 ? 'positive' : 'negative' }}">
                            @if($this->monthlyGrowth > 0) ↑ @else ↓ @endif
                            {{ abs($this->monthlyGrowth) }}%
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">{{ number_format($this->totalOrders) }}</p>
                    <p class="stat-label">إجمالي الطلبات</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">{{ number_format($this->totalEnrollments) }}</p>
                    <p class="stat-label">إجمالي التسجيلات</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon amber">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">{{ number_format($this->averageOrderValue, 2) }} ج.م</p>
                    <p class="stat-label">متوسط قيمة الطلب</p>
                </div>
            </div>
        </div>
        
        {{-- Charts Section --}}
        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">📈 الإيرادات عبر الزمن</h3>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">🏆 أفضل الدورات</h3>
                </div>
                <div class="top-courses-list">
                    @forelse($this->topCourses as $index => $item)
                        @php
                            $rankClass = match($index) {
                                0 => 'gold',
                                1 => 'silver',
                                2 => 'bronze',
                                default => '',
                            };
                        @endphp
                        <div class="top-course-item">
                            <div class="top-course-rank {{ $rankClass }}">{{ $index + 1 }}</div>
                            <div class="top-course-info">
                                <p class="top-course-name">{{ $item->course->title ?? 'غير معروف' }}</p>
                                <p class="top-course-enrollments">{{ number_format($item->enrollments_count) }} تسجيل</p>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-icon">
                                <svg width="24" height="24" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <p>لا توجد بيانات</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        {{-- Recent Orders --}}
        <div class="recent-orders">
            <div class="recent-orders-header">
                <h3 class="recent-orders-title">🧾 آخر الطلبات</h3>
            </div>
            
            @if($this->recentOrders->count() > 0)
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>الدورة</th>
                            <th>المبلغ</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->recentOrders as $order)
                            <tr>
                                <td>
                                    <div class="order-user">
                                        <div class="order-avatar">{{ mb_substr($order->user->name ?? '?', 0, 1) }}</div>
                                        <span>{{ $order->user->name ?? 'غير معروف' }}</span>
                                    </div>
                                </td>
                                <td>{{ Str::limit($order->items->first()?->course?->title ?? 'غير معروف', 40) }}</td>
                                <td class="order-amount">{{ number_format($order->total, 2) }} ج.م</td>
                                <td class="order-date">{{ $order->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="24" height="24" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <p>لا توجد طلبات في هذه الفترة</p>
                </div>
            @endif
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($this->revenueChartData);
            
            if (chartData.labels.length > 0) {
                const ctx = document.getElementById('revenueChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'الإيرادات',
                            data: chartData.values,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-filament-panels::page>
