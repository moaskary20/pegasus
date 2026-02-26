<style>
    .support-complaints-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 50%, #d946ef 100%);
        border-radius: 16px;
        padding: 24px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
    }
    .support-complaints-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }
    .support-complaints-header-info { display: flex; align-items: center; gap: 14px; }
    .support-complaints-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .support-complaints-header-title { font-size: 24px; font-weight: 700; margin: 0; }
    .support-complaints-header-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .support-complaints-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
    }
    .support-complaints-stat {
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
        backdrop-filter: blur(5px);
    }
    .support-complaints-stat-value { font-size: 26px; font-weight: 800; margin: 0; }
    .support-complaints-stat-label { font-size: 11px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .support-complaints-add-btn {
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
    .support-complaints-add-btn:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-2px);
    }
</style>

<div class="support-complaints-header">
    <div class="support-complaints-header-top">
        <div class="support-complaints-header-info">
            <div class="support-complaints-header-icon">
                <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div>
                <h1 class="support-complaints-header-title">الشكاوى والاستفسارات</h1>
                <p class="support-complaints-header-subtitle">إدارة الشكاوى وطلبات التواصل من المستخدمين</p>
            </div>
        </div>
        <a href="{{ $createUrl }}" class="support-complaints-add-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            إضافة شكوى / استفسار
        </a>
    </div>
    
    <div class="support-complaints-stats">
        <div class="support-complaints-stat">
            <p class="support-complaints-stat-value">{{ number_format($total) }}</p>
            <p class="support-complaints-stat-label">إجمالي السجلات</p>
        </div>
        <div class="support-complaints-stat">
            <p class="support-complaints-stat-value">{{ number_format($complaintsCount) }}</p>
            <p class="support-complaints-stat-label">شكاوى</p>
        </div>
        <div class="support-complaints-stat">
            <p class="support-complaints-stat-value">{{ number_format($contactCount) }}</p>
            <p class="support-complaints-stat-label">تواصل / استفسار</p>
        </div>
        <div class="support-complaints-stat">
            <p class="support-complaints-stat-value">{{ number_format($pendingCount) }}</p>
            <p class="support-complaints-stat-label">قيد الانتظار</p>
        </div>
        <div class="support-complaints-stat">
            <p class="support-complaints-stat-value">{{ number_format($resolvedCount) }}</p>
            <p class="support-complaints-stat-label">تم الحل</p>
        </div>
    </div>
</div>
