<style>
    .ratings-header {
        background: linear-gradient(135deg, #eab308 0%, #f59e0b 50%, #f97316 100%);
        border-radius: 16px;
        padding: 24px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(234, 179, 8, 0.3);
    }
    .ratings-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }
    .ratings-header-info { display: flex; align-items: center; gap: 14px; }
    .ratings-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .ratings-header-title { font-size: 24px; font-weight: 700; margin: 0; }
    .ratings-header-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .ratings-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 12px;
    }
    .ratings-stat {
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
        backdrop-filter: blur(5px);
    }
    .ratings-stat-value { font-size: 26px; font-weight: 800; margin: 0; }
    .ratings-stat-label { font-size: 11px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .add-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 10px;
        color: white;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        backdrop-filter: blur(5px);
    }
    .add-btn:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-2px);
    }
</style>

<div class="ratings-header">
    <div class="ratings-header-top">
        <div class="ratings-header-info">
            <div class="ratings-header-icon">
                <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <div>
                <h1 class="ratings-header-title">{{ $isAdmin ? 'تقييمات الدورات' : 'تقييمات دوراتي' }}</h1>
                <p class="ratings-header-subtitle">مراجعة وإدارة تقييمات الطلاب</p>
            </div>
        </div>
        <a href="{{ $createUrl }}" class="add-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            إضافة تقييم
        </a>
    </div>
    
    <div class="ratings-stats">
        <div class="ratings-stat">
            <p class="ratings-stat-value">{{ number_format($totalRatings) }}</p>
            <p class="ratings-stat-label">إجمالي التقييمات</p>
        </div>
        <div class="ratings-stat">
            <p class="ratings-stat-value">{{ $avgRating }} ⭐</p>
            <p class="ratings-stat-label">المتوسط</p>
        </div>
        <div class="ratings-stat">
            <p class="ratings-stat-value">{{ number_format($fiveStars) }}</p>
            <p class="ratings-stat-label">⭐⭐⭐⭐⭐</p>
        </div>
        <div class="ratings-stat">
            <p class="ratings-stat-value">{{ number_format($fourStars) }}</p>
            <p class="ratings-stat-label">⭐⭐⭐⭐</p>
        </div>
        <div class="ratings-stat">
            <p class="ratings-stat-value">{{ number_format($threeStars) }}</p>
            <p class="ratings-stat-label">⭐⭐⭐</p>
        </div>
        <div class="ratings-stat">
            <p class="ratings-stat-value">{{ number_format($lowRatings) }}</p>
            <p class="ratings-stat-label">أقل من 3</p>
        </div>
    </div>
</div>
