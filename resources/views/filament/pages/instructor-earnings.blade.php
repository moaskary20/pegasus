<x-filament-panels::page>
    <style>
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
        .earnings-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .earnings-info { display: flex; align-items: center; gap: 16px; }
        .earnings-icon {
            width: 60px; height: 60px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
        }
        .earnings-text h1 { font-size: 28px; font-weight: 800; margin: 0; }
        .earnings-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        
        .earnings-total {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 20px 28px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .earnings-total-label { font-size: 12px; opacity: 0.9; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .earnings-total-value { font-size: 36px; font-weight: 800; margin: 8px 0 0 0; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 54px; height: 54px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .stat-icon.green { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .stat-icon.blue { background: linear-gradient(135deg, #3b82f6, #6366f1); }
        .stat-icon.purple { background: linear-gradient(135deg, #8b5cf6, #a855f7); }
        .stat-icon.amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-icon.rose { background: linear-gradient(135deg, #f43f5e, #e11d48); }
        
        .stat-content { flex: 1; }
        .stat-value { font-size: 28px; font-weight: 800; color: #1f2937; margin: 0; }
        .stat-label { font-size: 13px; color: #6b7280; margin: 4px 0 0 0; }
        
        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .table-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 18px 24px;
            border-bottom: 1px solid #e5e7eb;
        }
        .table-title { font-size: 18px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 10px; }
        
        @media (prefers-color-scheme: dark) {
            .stat-card, .table-container { background: #1f2937; border-color: #374151; }
            .stat-value, .table-title { color: #f9fafb; }
            .table-header { background: linear-gradient(135deg, #1f2937, #374151); border-color: #374151; }
        }
    </style>
    
    @php
        $totalEarnings = $this->getTotalEarnings();
        $totalPayments = $this->getTotalPayments();
        $earningsData = $this->getEarningsData();
        $coursesCount = $earningsData->count();
        $studentsCount = $earningsData->sum(fn($e) => $e->getStudentsCount());
        $isAdmin = auth()->user()->hasRole('admin');
    @endphp
    
    {{-- Header --}}
    <div class="earnings-header">
        <div class="earnings-content">
            <div class="earnings-info">
                <div class="earnings-icon">
                    <svg width="30" height="30" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="earnings-text">
                    <h1>{{ $isAdmin ? 'تقرير الأرباح' : 'أرباحي' }}</h1>
                    <p>{{ $isAdmin ? 'متابعة أرباح جميع المدرسين' : 'متابعة أرباحك من الدورات' }}</p>
                </div>
            </div>
            
            <div class="earnings-total">
                <p class="earnings-total-label">إجمالي الأرباح</p>
                <p class="earnings-total-value">{{ number_format($totalEarnings, 2) }} ج.م</p>
            </div>
        </div>
    </div>
    
    {{-- Stats Grid --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon green">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-value">{{ number_format($totalEarnings, 2) }}</p>
                <p class="stat-label">إجمالي الأرباح (ج.م)</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon blue">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-value">{{ number_format($totalPayments, 2) }}</p>
                <p class="stat-label">إجمالي المدفوعات (ج.م)</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon purple">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-value">{{ number_format($coursesCount) }}</p>
                <p class="stat-label">الدورات المربحة</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon amber">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-value">{{ number_format($studentsCount) }}</p>
                <p class="stat-label">إجمالي الطلاب</p>
            </div>
        </div>
        
        @if($totalPayments > 0)
        <div class="stat-card">
            <div class="stat-icon rose">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-value">{{ number_format(($totalEarnings / $totalPayments) * 100, 1) }}%</p>
                <p class="stat-label">نسبة الأرباح</p>
            </div>
        </div>
        @endif
    </div>
    
    {{-- Table --}}
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                تفاصيل الأرباح
            </h3>
        </div>
        <div style="padding: 0;">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
