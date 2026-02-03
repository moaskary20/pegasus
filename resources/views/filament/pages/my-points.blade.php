<x-filament-panels::page>
    <style>
        .points-container { max-width: 100%; }
        
        .points-header {
            background: linear-gradient(135deg, #f59e0b 0%, #eab308 50%, #f59e0b 100%);
            border-radius: 16px;
            padding: 24px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
            position: relative;
            overflow: hidden;
        }
        .points-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }
        .points-header-content { position: relative; z-index: 1; }
        .points-header-top { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 20px; }
        .points-main {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .points-icon {
            width: 64px; height: 64px;
            background: rgba(255,255,255,0.2);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
        }
        .points-value { font-size: 42px; font-weight: 800; line-height: 1; }
        .points-label { font-size: 14px; opacity: 0.9; margin-top: 4px; }
        
        .rank-badge {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px 24px;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        .rank-icon { font-size: 32px; margin-bottom: 4px; }
        .rank-name { font-size: 14px; font-weight: 600; }
        .rank-position { font-size: 11px; opacity: 0.8; }
        
        .points-stats {
            display: flex;
            gap: 24px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }
        .stat-item { flex: 1; }
        .stat-value { font-size: 24px; font-weight: 700; }
        .stat-label { font-size: 12px; opacity: 0.8; }
        
        .progress-section { margin-top: 20px; }
        .progress-label { font-size: 12px; opacity: 0.9; margin-bottom: 8px; display: flex; justify-content: space-between; }
        .progress-bar {
            height: 8px;
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: white;
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .filters-bar {
            background: white;
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
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
            color: #f59e0b;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .transactions-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        .transaction-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }
        .transaction-item:last-child { border-bottom: none; }
        .transaction-item:hover { background: #f9fafb; }
        
        .transaction-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .transaction-content { flex: 1; min-width: 0; }
        .transaction-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }
        .transaction-desc {
            font-size: 12px;
            color: #6b7280;
            margin: 2px 0 0 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .transaction-time {
            font-size: 11px;
            color: #9ca3af;
        }
        .transaction-points {
            font-size: 16px;
            font-weight: 700;
            white-space: nowrap;
        }
        .transaction-points.positive { color: #22c55e; }
        .transaction-points.negative { color: #ef4444; }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-icon {
            width: 70px; height: 70px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
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
        .bg-purple { background: #a855f7; }
        .bg-green { background: #22c55e; }
        .bg-amber { background: #f59e0b; }
        .bg-red { background: #ef4444; }
        .bg-blue { background: #3b82f6; }
        .bg-teal { background: #14b8a6; }
        .bg-indigo { background: #6366f1; }
        .bg-emerald { background: #10b981; }
        .bg-gray { background: #6b7280; }
        
        @media (prefers-color-scheme: dark) {
            .filters-bar, .transactions-list, .pagination-wrapper { background: #1f2937; border-color: #374151; }
            .filter-tabs { background: #374151; }
            .filter-tab { color: #9ca3af; }
            .filter-tab.active { background: #4b5563; color: #fbbf24; }
            .transaction-item { border-color: #374151; }
            .transaction-item:hover { background: #374151; }
            .transaction-title { color: white; }
            .empty-icon { background: #374151; }
            .empty-title { color: white; }
        }
    </style>

    @php
        $user = auth()->user();
        $rankIcons = [
            'bronze' => '🥉',
            'silver' => '🥈',
            'gold' => '🥇',
            'platinum' => '💎',
            'diamond' => '👑',
        ];
        $rankThresholds = [
            'bronze' => 500,
            'silver' => 2000,
            'gold' => 5000,
            'platinum' => 10000,
        ];
        $currentThreshold = $rankThresholds[$user->rank ?? 'bronze'] ?? 10000;
        $prevThreshold = match($user->rank ?? 'bronze') {
            'silver' => 500,
            'gold' => 2000,
            'platinum' => 5000,
            'diamond' => 10000,
            default => 0,
        };
        $progressPercent = $user->rank === 'diamond' ? 100 : min(100, (($user->total_points - $prevThreshold) / max(1, $currentThreshold - $prevThreshold)) * 100);
    @endphp

    <div class="points-container">
        {{-- Header --}}
        <div class="points-header">
            <div class="points-header-content">
                <div class="points-header-top">
                    <div class="points-main">
                        <div class="points-icon">
                            <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="points-value">{{ number_format($user->available_points) }}</p>
                            <p class="points-label">نقطة متاحة</p>
                        </div>
                    </div>
                    
                    <div class="rank-badge">
                        <span class="rank-icon">{{ $rankIcons[$user->rank ?? 'bronze'] ?? '🥉' }}</span>
                        <span class="rank-name">{{ $user->rank_label }}</span>
                        <span class="rank-position">المركز #{{ $this->rankPosition }}</span>
                    </div>
                </div>
                
                <div class="points-stats">
                    <div class="stat-item">
                        <p class="stat-value">{{ number_format($user->total_points) }}</p>
                        <p class="stat-label">إجمالي النقاط</p>
                    </div>
                    <div class="stat-item">
                        <p class="stat-value">{{ number_format($this->totalEarned) }}</p>
                        <p class="stat-label">نقاط مكتسبة</p>
                    </div>
                    <div class="stat-item">
                        <p class="stat-value">{{ number_format($this->totalSpent) }}</p>
                        <p class="stat-label">نقاط مستبدلة</p>
                    </div>
                </div>
                
                @if($user->rank !== 'diamond')
                    <div class="progress-section">
                        <div class="progress-label">
                            <span>التقدم نحو الرتبة التالية</span>
                            <span>{{ $this->pointsForNextRank ? number_format($this->pointsForNextRank) . ' نقطة متبقية' : '' }}</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $progressPercent }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Filters --}}
        <div class="filters-bar">
            <div class="filter-tabs">
                <button wire:click="setFilter('all')" class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">الكل</button>
                <button wire:click="setFilter('earned')" class="filter-tab {{ $filter === 'earned' ? 'active' : '' }}">مكتسبة</button>
                <button wire:click="setFilter('spent')" class="filter-tab {{ $filter === 'spent' ? 'active' : '' }}">مستبدلة</button>
            </div>
        </div>
        
        {{-- Transactions --}}
        <div class="transactions-list">
            @forelse($this->transactions as $transaction)
                @php
                    $color = $transaction->type_color;
                @endphp
                
                <div class="transaction-item">
                    <div class="transaction-icon bg-{{ $color }}">
                        <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $transaction->type_icon }}"/>
                        </svg>
                    </div>
                    
                    <div class="transaction-content">
                        <p class="transaction-title">{{ $transaction->type_label }}</p>
                        <p class="transaction-desc">{{ $transaction->description }}</p>
                    </div>
                    
                    <span class="transaction-time">{{ $transaction->created_at->diffForHumans() }}</span>
                    
                    <span class="transaction-points {{ $transaction->points > 0 ? 'positive' : 'negative' }}">
                        {{ $transaction->points > 0 ? '+' : '' }}{{ number_format($transaction->points) }}
                    </span>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="28" height="28" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="empty-title">لا توجد معاملات</p>
                    <p class="empty-text">ابدأ بإكمال الدروس والاختبارات لكسب النقاط</p>
                </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if($this->transactions->hasPages())
            <div class="pagination-wrapper">
                {{ $this->transactions->links() }}
            </div>
        @endif
    </div>
</x-filament-panels::page>
