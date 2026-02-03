<style>
    .orders-header {
        background: linear-gradient(135deg, #ec4899 0%, #f43f5e 50%, #ef4444 100%);
        border-radius: 16px;
        padding: 24px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(236, 72, 153, 0.3);
    }
    .orders-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }
    .orders-header-info { display: flex; align-items: center; gap: 14px; }
    .orders-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .orders-header-title { font-size: 24px; font-weight: 700; margin: 0; }
    .orders-header-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .orders-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
    }
    .orders-stat {
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
        backdrop-filter: blur(5px);
    }
    .orders-stat-value { font-size: 26px; font-weight: 800; margin: 0; }
    .orders-stat-label { font-size: 11px; opacity: 0.9; margin: 4px 0 0 0; }
    
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

<div class="orders-header">
    <div class="orders-header-top">
        <div class="orders-header-info">
            <div class="orders-header-icon">
                <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="orders-header-title">إدارة الطلبات</h1>
                <p class="orders-header-subtitle">عرض ومتابعة جميع الطلبات</p>
            </div>
        </div>
        <a href="{{ $createUrl }}" class="add-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            إضافة طلب
        </a>
    </div>
    
    <div class="orders-stats">
        <div class="orders-stat">
            <p class="orders-stat-value">{{ number_format($totalOrders) }}</p>
            <p class="orders-stat-label">إجمالي الطلبات</p>
        </div>
        <div class="orders-stat">
            <p class="orders-stat-value">{{ number_format($paidOrders) }}</p>
            <p class="orders-stat-label">طلبات مدفوعة</p>
        </div>
        <div class="orders-stat">
            <p class="orders-stat-value">{{ number_format($pendingOrders) }}</p>
            <p class="orders-stat-label">قيد الانتظار</p>
        </div>
        <div class="orders-stat">
            <p class="orders-stat-value">{{ number_format($failedOrders) }}</p>
            <p class="orders-stat-label">فاشلة</p>
        </div>
        <div class="orders-stat">
            <p class="orders-stat-value">{{ number_format($totalRevenue) }}</p>
            <p class="orders-stat-label">الإيرادات (ج.م)</p>
        </div>
        <div class="orders-stat">
            <p class="orders-stat-value">{{ number_format($todayOrders) }}</p>
            <p class="orders-stat-label">اليوم</p>
        </div>
    </div>
</div>
