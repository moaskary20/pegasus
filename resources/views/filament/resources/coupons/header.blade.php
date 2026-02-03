<style>
    .coupons-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
        border-radius: 16px;
        padding: 24px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
    }
    .coupons-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }
    .coupons-header-info { display: flex; align-items: center; gap: 14px; }
    .coupons-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .coupons-header-title { font-size: 24px; font-weight: 700; margin: 0; }
    .coupons-header-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .coupons-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
    }
    .coupons-stat {
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
        backdrop-filter: blur(5px);
    }
    .coupons-stat-value { font-size: 26px; font-weight: 800; margin: 0; }
    .coupons-stat-label { font-size: 11px; opacity: 0.9; margin: 4px 0 0 0; }
    
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

<div class="coupons-header">
    <div class="coupons-header-top">
        <div class="coupons-header-info">
            <div class="coupons-header-icon">
                <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
            <div>
                <h1 class="coupons-header-title">إدارة الكوبونات</h1>
                <p class="coupons-header-subtitle">إنشاء وإدارة كوبونات الخصم</p>
            </div>
        </div>
        <a href="{{ $createUrl }}" class="add-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            إضافة كوبون
        </a>
    </div>
    
    <div class="coupons-stats">
        <div class="coupons-stat">
            <p class="coupons-stat-value">{{ number_format($totalCoupons) }}</p>
            <p class="coupons-stat-label">إجمالي الكوبونات</p>
        </div>
        <div class="coupons-stat">
            <p class="coupons-stat-value">{{ number_format($activeCoupons) }}</p>
            <p class="coupons-stat-label">كوبونات نشطة</p>
        </div>
        <div class="coupons-stat">
            <p class="coupons-stat-value">{{ number_format($expiredCoupons) }}</p>
            <p class="coupons-stat-label">منتهية الصلاحية</p>
        </div>
        <div class="coupons-stat">
            <p class="coupons-stat-value">{{ number_format($percentCoupons) }}</p>
            <p class="coupons-stat-label">نسبة مئوية</p>
        </div>
        <div class="coupons-stat">
            <p class="coupons-stat-value">{{ number_format($fixedCoupons) }}</p>
            <p class="coupons-stat-label">مبلغ ثابت</p>
        </div>
        <div class="coupons-stat">
            <p class="coupons-stat-value">{{ number_format($totalUsage) }}</p>
            <p class="coupons-stat-label">مرات الاستخدام</p>
        </div>
    </div>
</div>
