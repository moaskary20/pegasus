<style>
    .certs-header {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 50%, #0f766e 100%);
        border-radius: 16px;
        padding: 24px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(20, 184, 166, 0.3);
    }
    .certs-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }
    .certs-header-info { display: flex; align-items: center; gap: 14px; }
    .certs-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .certs-header-title { font-size: 24px; font-weight: 700; margin: 0; }
    .certs-header-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .certs-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
    }
    .certs-stat {
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
        backdrop-filter: blur(5px);
    }
    .certs-stat-value { font-size: 26px; font-weight: 800; margin: 0; }
    .certs-stat-label { font-size: 11px; opacity: 0.9; margin: 4px 0 0 0; }
    
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

<div class="certs-header">
    <div class="certs-header-top">
        <div class="certs-header-info">
            <div class="certs-header-icon">
                <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
            </div>
            <div>
                <h1 class="certs-header-title">{{ $isAdmin ? 'إدارة الشهادات' : 'شهادات طلابي' }}</h1>
                <p class="certs-header-subtitle">عرض وإدارة شهادات إتمام الدورات</p>
            </div>
        </div>
        <a href="{{ $createUrl }}" class="add-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            إصدار شهادة
        </a>
    </div>
    
    <div class="certs-stats">
        <div class="certs-stat">
            <p class="certs-stat-value">{{ number_format($totalCertificates) }}</p>
            <p class="certs-stat-label">إجمالي الشهادات</p>
        </div>
        <div class="certs-stat">
            <p class="certs-stat-value">{{ number_format($withPdf) }}</p>
            <p class="certs-stat-label">مع PDF</p>
        </div>
        <div class="certs-stat">
            <p class="certs-stat-value">{{ number_format($withoutPdf) }}</p>
            <p class="certs-stat-label">بدون PDF</p>
        </div>
        <div class="certs-stat">
            <p class="certs-stat-value">{{ number_format($thisMonth) }}</p>
            <p class="certs-stat-label">هذا الشهر</p>
        </div>
        <div class="certs-stat">
            <p class="certs-stat-value">{{ number_format($uniqueCourses) }}</p>
            <p class="certs-stat-label">دورات مختلفة</p>
        </div>
    </div>
</div>
