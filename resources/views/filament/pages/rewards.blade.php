<x-filament-panels::page>
    <style>
        .rewards-container { max-width: 100%; }
        
        .rewards-header {
            background: linear-gradient(135deg, #ec4899 0%, #f43f5e 50%, #ef4444 100%);
            border-radius: 16px;
            padding: 24px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(236, 72, 153, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }
        .rewards-header-info { display: flex; align-items: center; gap: 16px; }
        .rewards-header-icon {
            width: 56px; height: 56px;
            background: rgba(255,255,255,0.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
        }
        .rewards-title { font-size: 24px; font-weight: 700; margin: 0; }
        .rewards-subtitle { font-size: 14px; opacity: 0.9; margin: 4px 0 0 0; }
        
        .points-balance {
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            padding: 16px 24px;
            text-align: center;
            backdrop-filter: blur(10px);
        }
        .points-balance-value { font-size: 32px; font-weight: 800; }
        .points-balance-label { font-size: 12px; opacity: 0.9; }
        
        .tabs-bar {
            background: white;
            border-radius: 12px;
            padding: 6px;
            display: inline-flex;
            gap: 4px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        .tab-btn {
            padding: 10px 20px;
            border: none;
            background: transparent;
            font-size: 14px;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .tab-btn.active {
            background: linear-gradient(135deg, #ec4899, #f43f5e);
            color: white;
        }
        .tab-btn:hover:not(.active) { background: #f3f4f6; }
        
        .rewards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .reward-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            transition: all 0.3s;
        }
        .reward-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .reward-card.unavailable { opacity: 0.6; }
        
        .reward-image {
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .reward-image.discount { background: linear-gradient(135deg, #f97316, #fb923c); }
        .reward-image.free_course { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
        .reward-image.badge { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .reward-image.certificate { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        
        .reward-type-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 6px 12px;
            background: rgba(255,255,255,0.2);
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            color: white;
            backdrop-filter: blur(5px);
        }
        .reward-quantity-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 6px 12px;
            background: rgba(0,0,0,0.3);
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            color: white;
        }
        
        .reward-body { padding: 18px; }
        .reward-name { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; }
        .reward-desc { font-size: 13px; color: #6b7280; margin: 0 0 16px 0; line-height: 1.5; }
        
        .reward-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .reward-points {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 18px;
            font-weight: 700;
            color: #ec4899;
        }
        .redeem-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .redeem-btn.available {
            background: linear-gradient(135deg, #ec4899, #f43f5e);
            color: white;
        }
        .redeem-btn.available:hover { transform: scale(1.05); }
        .redeem-btn.insufficient {
            background: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
        }
        .redeem-btn.unavailable {
            background: #e5e7eb;
            color: #9ca3af;
            cursor: not-allowed;
        }
        
        .my-rewards-list { display: flex; flex-direction: column; gap: 12px; }
        .redemption-item {
            background: white;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        .redemption-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .redemption-icon.discount { background: linear-gradient(135deg, #f97316, #fb923c); }
        .redemption-icon.free_course { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
        .redemption-icon.badge { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .redemption-icon.certificate { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        
        .redemption-content { flex: 1; min-width: 0; }
        .redemption-name { font-size: 15px; font-weight: 600; color: #1f2937; margin: 0; }
        .redemption-meta { font-size: 12px; color: #6b7280; margin: 4px 0 0 0; }
        
        .redemption-code {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 10px 16px;
            font-family: monospace;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            letter-spacing: 1px;
        }
        
        .redemption-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .redemption-status.completed { background: #dcfce7; color: #16a34a; }
        .redemption-status.pending { background: #fef3c7; color: #d97706; }
        .redemption-status.used { background: #dbeafe; color: #2563eb; }
        .redemption-status.expired { background: #f3f4f6; color: #6b7280; }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
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
            .tabs-bar, .reward-card, .redemption-item, .empty-state { background: #1f2937; border-color: #374151; }
            .tab-btn { color: #9ca3af; }
            .tab-btn:hover:not(.active) { background: #374151; }
            .reward-name, .redemption-name { color: white; }
            .redeem-btn.insufficient, .redeem-btn.unavailable { background: #374151; }
            .redemption-code { background: #374151; color: white; }
            .empty-icon { background: #374151; }
            .empty-title { color: white; }
        }
    </style>

    <div class="rewards-container">
        {{-- Header --}}
        <div class="rewards-header">
            <div class="rewards-header-info">
                <div class="rewards-header-icon">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                    </svg>
                </div>
                <div>
                    <h1 class="rewards-title">المكافآت</h1>
                    <p class="rewards-subtitle">استبدل نقاطك بمكافآت رائعة</p>
                </div>
            </div>
            
            <div class="points-balance">
                <p class="points-balance-value">{{ number_format($this->userPoints) }}</p>
                <p class="points-balance-label">نقطة متاحة</p>
            </div>
        </div>
        
        {{-- Tabs --}}
        <div class="tabs-bar">
            <button wire:click="setTab('available')" class="tab-btn {{ $tab === 'available' ? 'active' : '' }}">
                المكافآت المتاحة
            </button>
            <button wire:click="setTab('my-rewards')" class="tab-btn {{ $tab === 'my-rewards' ? 'active' : '' }}">
                مكافآتي
            </button>
        </div>
        
        {{-- Available Rewards --}}
        @if($tab === 'available')
            @if($this->availableRewards->count() > 0)
                <div class="rewards-grid">
                    @foreach($this->availableRewards as $reward)
                        @php
                            $canRedeem = $this->userPoints >= $reward->points_required;
                            $isAvailable = $reward->isAvailable();
                        @endphp
                        
                        <div class="reward-card {{ !$isAvailable ? 'unavailable' : '' }}">
                            <div class="reward-image {{ $reward->type }}">
                                <svg width="48" height="48" fill="none" stroke="white" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $reward->type_icon }}"/>
                                </svg>
                                
                                <span class="reward-type-badge">{{ $reward->type_label }}</span>
                                
                                @if($reward->remaining_quantity !== null)
                                    <span class="reward-quantity-badge">متبقي: {{ $reward->remaining_quantity }}</span>
                                @endif
                            </div>
                            
                            <div class="reward-body">
                                <h3 class="reward-name">{{ $reward->name }}</h3>
                                <p class="reward-desc">{{ $reward->description }}</p>
                                
                                <div class="reward-footer">
                                    <span class="reward-points">
                                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                        {{ number_format($reward->points_required) }}
                                    </span>
                                    
                                    @if(!$isAvailable)
                                        <button class="redeem-btn unavailable" disabled>غير متاح</button>
                                    @elseif($canRedeem)
                                        <button 
                                            class="redeem-btn available"
                                            wire:click="redeemReward({{ $reward->id }})"
                                            wire:confirm="هل تريد استبدال {{ number_format($reward->points_required) }} نقطة بهذه المكافأة؟"
                                        >
                                            استبدال
                                        </button>
                                    @else
                                        <button class="redeem-btn insufficient" disabled>
                                            تحتاج {{ number_format($reward->points_required - $this->userPoints) }} نقطة
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="28" height="28" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                        </svg>
                    </div>
                    <p class="empty-title">لا توجد مكافآت متاحة حالياً</p>
                    <p class="empty-text">تحقق لاحقاً للحصول على مكافآت جديدة</p>
                </div>
            @endif
        @endif
        
        {{-- My Rewards --}}
        @if($tab === 'my-rewards')
            @if($this->myRedemptions->count() > 0)
                <div class="my-rewards-list">
                    @foreach($this->myRedemptions as $redemption)
                        <div class="redemption-item">
                            <div class="redemption-icon {{ $redemption->reward->type }}">
                                <svg width="24" height="24" fill="none" stroke="white" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $redemption->reward->type_icon }}"/>
                                </svg>
                            </div>
                            
                            <div class="redemption-content">
                                <p class="redemption-name">{{ $redemption->reward->name }}</p>
                                <p class="redemption-meta">
                                    تم الاستبدال {{ $redemption->created_at->diffForHumans() }}
                                    • {{ number_format($redemption->points_spent) }} نقطة
                                </p>
                            </div>
                            
                            @if($redemption->code)
                                <span class="redemption-code">{{ $redemption->code }}</span>
                            @endif
                            
                            <span class="redemption-status {{ $redemption->status }}">
                                {{ $redemption->status_label }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="28" height="28" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                        </svg>
                    </div>
                    <p class="empty-title">لم تستبدل أي مكافآت بعد</p>
                    <p class="empty-text">اجمع النقاط واستبدلها بمكافآت رائعة</p>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
