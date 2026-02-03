<style>
    .categories-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 50%, #d946ef 100%);
        border-radius: 16px;
        padding: 24px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
    }
    .categories-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }
    .categories-header-info { display: flex; align-items: center; gap: 14px; }
    .categories-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .categories-header-title { font-size: 24px; font-weight: 700; margin: 0; }
    .categories-header-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .categories-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
    }
    .categories-stat {
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
        backdrop-filter: blur(5px);
    }
    .categories-stat-value { font-size: 26px; font-weight: 800; margin: 0; }
    .categories-stat-label { font-size: 11px; opacity: 0.9; margin: 4px 0 0 0; }
    
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

<div class="categories-header">
    <div class="categories-header-top">
        <div class="categories-header-info">
            <div class="categories-header-icon">
                <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <div>
                <h1 class="categories-header-title">إدارة التصنيفات</h1>
                <p class="categories-header-subtitle">تنظيم الدورات في تصنيفات وفئات</p>
            </div>
        </div>
        <a href="{{ $createUrl }}" class="add-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            إضافة تصنيف
        </a>
    </div>
    
    <div class="categories-stats">
        <div class="categories-stat">
            <p class="categories-stat-value">{{ number_format($totalCategories) }}</p>
            <p class="categories-stat-label">إجمالي التصنيفات</p>
        </div>
        <div class="categories-stat">
            <p class="categories-stat-value">{{ number_format($activeCategories) }}</p>
            <p class="categories-stat-label">تصنيفات نشطة</p>
        </div>
        <div class="categories-stat">
            <p class="categories-stat-value">{{ number_format($parentCategories) }}</p>
            <p class="categories-stat-label">تصنيفات رئيسية</p>
        </div>
        <div class="categories-stat">
            <p class="categories-stat-value">{{ number_format($subCategories) }}</p>
            <p class="categories-stat-label">تصنيفات فرعية</p>
        </div>
        <div class="categories-stat">
            <p class="categories-stat-value">{{ number_format($coursesWithCategory) }}</p>
            <p class="categories-stat-label">دورات مصنفة</p>
        </div>
    </div>
</div>
