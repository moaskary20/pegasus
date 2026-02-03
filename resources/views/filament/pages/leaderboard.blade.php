<x-filament-panels::page>
    <style>
        .leaderboard-container { max-width: 100%; }
        
        .leaderboard-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            border-radius: 16px;
            padding: 24px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
            text-align: center;
        }
        .leaderboard-title { font-size: 28px; font-weight: 800; margin: 0 0 8px 0; }
        .leaderboard-subtitle { font-size: 14px; opacity: 0.9; margin: 0; }
        
        .podium {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 16px;
            margin: 24px 0;
            padding: 20px;
        }
        .podium-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            border-radius: 16px;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            transition: transform 0.3s;
        }
        .podium-item:hover { transform: translateY(-5px); }
        .podium-item.first {
            background: linear-gradient(135deg, #fef3c7, #fcd34d);
            border-color: #f59e0b;
            order: 2;
            transform: scale(1.1);
        }
        .podium-item.first:hover { transform: scale(1.1) translateY(-5px); }
        .podium-item.second { order: 1; }
        .podium-item.third { order: 3; }
        
        .podium-rank {
            font-size: 32px;
            margin-bottom: 12px;
        }
        .podium-avatar {
            width: 64px; height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 12px;
        }
        .podium-avatar-placeholder {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 24px;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 12px;
        }
        .podium-name { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0; }
        .podium-points { font-size: 18px; font-weight: 800; color: #6366f1; margin: 4px 0 0 0; }
        .podium-item.first .podium-points { color: #92400e; }
        
        .your-rank-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #22c55e;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }
        .your-rank-badge {
            width: 50px; height: 50px;
            background: #22c55e;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 800; font-size: 20px;
        }
        .your-rank-info { flex: 1; }
        .your-rank-label { font-size: 12px; color: #16a34a; margin: 0; }
        .your-rank-name { font-size: 16px; font-weight: 600; color: #1f2937; margin: 2px 0 0 0; }
        .your-rank-points { font-size: 20px; font-weight: 800; color: #22c55e; }
        
        .leaderboard-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }
        .leaderboard-item:last-child { border-bottom: none; }
        .leaderboard-item:hover { background: #f9fafb; }
        .leaderboard-item.current-user { background: #f0fdf4; }
        
        .leaderboard-position {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; color: #6b7280;
            flex-shrink: 0;
        }
        .leaderboard-position.top-10 { background: #dbeafe; color: #2563eb; }
        
        .leaderboard-avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }
        .leaderboard-avatar-placeholder {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600; font-size: 16px;
            flex-shrink: 0;
        }
        
        .leaderboard-info { flex: 1; min-width: 0; }
        .leaderboard-name { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0; }
        .leaderboard-rank-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            margin-top: 2px;
        }
        
        .leaderboard-points {
            font-size: 16px;
            font-weight: 700;
            color: #6366f1;
        }
        
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
        
        @media (prefers-color-scheme: dark) {
            .podium-item { background: #1f2937; border-color: #374151; }
            .podium-item.first { background: linear-gradient(135deg, #78350f, #b45309); }
            .podium-name { color: white; }
            .your-rank-card { background: linear-gradient(135deg, #064e3b, #065f46); border-color: #10b981; }
            .your-rank-label { color: #6ee7b7; }
            .your-rank-name { color: white; }
            .leaderboard-list { background: #1f2937; border-color: #374151; }
            .leaderboard-item { border-color: #374151; }
            .leaderboard-item:hover { background: #374151; }
            .leaderboard-item.current-user { background: rgba(34, 197, 94, 0.1); }
            .leaderboard-position { background: #374151; color: #9ca3af; }
            .leaderboard-position.top-10 { background: #1e3a8a; color: #93c5fd; }
            .leaderboard-name { color: white; }
            .empty-icon { background: #374151; }
            .empty-title { color: white; }
        }
    </style>

    @php
        $rankIcons = [
            'bronze' => '🥉',
            'silver' => '🥈',
            'gold' => '🥇',
            'platinum' => '💎',
            'diamond' => '👑',
        ];
    @endphp

    <div class="leaderboard-container">
        {{-- Header --}}
        <div class="leaderboard-header">
            <h1 class="leaderboard-title">🏆 لوحة المتصدرين</h1>
            <p class="leaderboard-subtitle">تنافس مع الآخرين واحصل على أعلى النقاط</p>
        </div>
        
        {{-- Top 3 Podium --}}
        @if($this->topThree->count() >= 3)
            <div class="podium">
                @foreach($this->topThree as $index => $leader)
                    @php
                        $position = $index + 1;
                        $class = match($position) {
                            1 => 'first',
                            2 => 'second',
                            3 => 'third',
                            default => '',
                        };
                        $emoji = match($position) {
                            1 => '🥇',
                            2 => '🥈',
                            3 => '🥉',
                            default => '',
                        };
                    @endphp
                    <div class="podium-item {{ $class }}">
                        <span class="podium-rank">{{ $emoji }}</span>
                        @if($leader->avatar)
                            <img src="{{ Storage::url($leader->avatar) }}" class="podium-avatar" alt="{{ $leader->name }}">
                        @else
                            <div class="podium-avatar-placeholder">{{ mb_substr($leader->name, 0, 1) }}</div>
                        @endif
                        <p class="podium-name">{{ $leader->name }}</p>
                        <p class="podium-points">{{ number_format($leader->total_points) }}</p>
                    </div>
                @endforeach
            </div>
        @endif
        
        {{-- Your Rank --}}
        <div class="your-rank-card">
            <div class="your-rank-badge">#{{ $this->currentUserRank }}</div>
            <div class="your-rank-info">
                <p class="your-rank-label">ترتيبك الحالي</p>
                <p class="your-rank-name">{{ $this->currentUser->name }}</p>
            </div>
            <span class="your-rank-points">{{ number_format($this->currentUser->total_points) }} نقطة</span>
        </div>
        
        {{-- Full Leaderboard --}}
        <div class="leaderboard-list">
            @forelse($this->rest as $index => $leader)
                @php
                    $position = $index + 4;
                    $isCurrentUser = $leader->id === auth()->id();
                @endphp
                
                <div class="leaderboard-item {{ $isCurrentUser ? 'current-user' : '' }}">
                    <div class="leaderboard-position {{ $position <= 10 ? 'top-10' : '' }}">{{ $position }}</div>
                    
                    @if($leader->avatar)
                        <img src="{{ Storage::url($leader->avatar) }}" class="leaderboard-avatar" alt="{{ $leader->name }}">
                    @else
                        <div class="leaderboard-avatar-placeholder">{{ mb_substr($leader->name, 0, 1) }}</div>
                    @endif
                    
                    <div class="leaderboard-info">
                        <p class="leaderboard-name">{{ $leader->name }}</p>
                        <span class="leaderboard-rank-badge">
                            {{ $rankIcons[$leader->rank ?? 'bronze'] ?? '🥉' }}
                            {{ $leader->rank_label }}
                        </span>
                    </div>
                    
                    <span class="leaderboard-points">{{ number_format($leader->total_points) }}</span>
                </div>
            @empty
                @if($this->topThree->count() === 0)
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg width="28" height="28" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="empty-title">لا يوجد متسابقون بعد</p>
                        <p class="empty-text">كن أول من يجمع النقاط!</p>
                    </div>
                @endif
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
