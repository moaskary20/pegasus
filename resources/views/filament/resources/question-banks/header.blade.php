<style>
    .qbanks-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
        border-radius: 16px;
        padding: 24px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
    }
    .qbanks-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }
    .qbanks-header-info { display: flex; align-items: center; gap: 14px; }
    .qbanks-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .qbanks-header-title { font-size: 24px; font-weight: 700; margin: 0; }
    .qbanks-header-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .qbanks-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
    }
    .qbanks-stat {
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
        backdrop-filter: blur(5px);
    }
    .qbanks-stat-value { font-size: 26px; font-weight: 800; margin: 0; }
    .qbanks-stat-label { font-size: 11px; opacity: 0.9; margin: 4px 0 0 0; }
    
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

<div class="qbanks-header">
    <div class="qbanks-header-top">
        <div class="qbanks-header-info">
            <div class="qbanks-header-icon">
                <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <h1 class="qbanks-header-title">{{ $isAdmin ? 'بنوك الأسئلة' : 'بنوك أسئلتي' }}</h1>
                <p class="qbanks-header-subtitle">إدارة بنوك الأسئلة والاختبارات</p>
            </div>
        </div>
        <a href="{{ $createUrl }}" class="add-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            إضافة بنك أسئلة
        </a>
    </div>
    
    <div class="qbanks-stats">
        <div class="qbanks-stat">
            <p class="qbanks-stat-value">{{ number_format($totalBanks) }}</p>
            <p class="qbanks-stat-label">إجمالي البنوك</p>
        </div>
        <div class="qbanks-stat">
            <p class="qbanks-stat-value">{{ number_format($activeBanks) }}</p>
            <p class="qbanks-stat-label">بنوك نشطة</p>
        </div>
        <div class="qbanks-stat">
            <p class="qbanks-stat-value">{{ number_format($totalQuestions) }}</p>
            <p class="qbanks-stat-label">إجمالي الأسئلة</p>
        </div>
        <div class="qbanks-stat">
            <p class="qbanks-stat-value">{{ number_format($courseBanks) }}</p>
            <p class="qbanks-stat-label">بنوك الدورات</p>
        </div>
        <div class="qbanks-stat">
            <p class="qbanks-stat-value">{{ number_format($generalBanks) }}</p>
            <p class="qbanks-stat-label">بنوك عامة</p>
        </div>
    </div>
</div>
