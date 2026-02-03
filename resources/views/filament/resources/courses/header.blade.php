<style>
    .courses-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
        border-radius: 16px;
        padding: 24px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
    }
    .courses-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }
    .courses-header-info { display: flex; align-items: center; gap: 14px; }
    .courses-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .courses-header-title { font-size: 24px; font-weight: 700; margin: 0; }
    .courses-header-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .courses-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
    }
    .courses-stat {
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
        backdrop-filter: blur(5px);
    }
    .courses-stat-value { font-size: 26px; font-weight: 800; margin: 0; }
    .courses-stat-label { font-size: 11px; opacity: 0.9; margin: 4px 0 0 0; }
    
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

<div class="courses-header">
    <div class="courses-header-top">
        <div class="courses-header-info">
            <div class="courses-header-icon">
                <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                </svg>
            </div>
            <div>
                <h1 class="courses-header-title">{{ $isAdmin ? 'إدارة الدورات' : 'دوراتي' }}</h1>
                <p class="courses-header-subtitle">{{ $isAdmin ? 'عرض وإدارة جميع الدورات التعليمية' : 'إدارة وتحرير دوراتك التعليمية' }}</p>
            </div>
        </div>
        <a href="{{ $createUrl }}" class="add-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            إضافة دورة
        </a>
    </div>
    
    <div class="courses-stats">
        <div class="courses-stat">
            <p class="courses-stat-value">{{ number_format($totalCourses) }}</p>
            <p class="courses-stat-label">إجمالي الدورات</p>
        </div>
        <div class="courses-stat">
            <p class="courses-stat-value">{{ number_format($publishedCourses) }}</p>
            <p class="courses-stat-label">دورات منشورة</p>
        </div>
        <div class="courses-stat">
            <p class="courses-stat-value">{{ number_format($draftCourses) }}</p>
            <p class="courses-stat-label">مسودات</p>
        </div>
        <div class="courses-stat">
            <p class="courses-stat-value">{{ number_format($totalStudents) }}</p>
            <p class="courses-stat-label">إجمالي الطلاب</p>
        </div>
        <div class="courses-stat">
            <p class="courses-stat-value">{{ number_format($completedStudents) }}</p>
            <p class="courses-stat-label">أكملوا الدورات</p>
        </div>
        <div class="courses-stat">
            <p class="courses-stat-value">{{ number_format($totalRevenue) }}</p>
            <p class="courses-stat-label">الإيرادات (ج.م)</p>
        </div>
    </div>
</div>
