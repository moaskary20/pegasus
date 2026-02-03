<style>
    .enrollments-header {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 50%, #0e7490 100%);
        border-radius: 16px;
        padding: 24px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(6, 182, 212, 0.3);
    }
    .enrollments-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }
    .enrollments-header-info { display: flex; align-items: center; gap: 14px; }
    .enrollments-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .enrollments-header-title { font-size: 24px; font-weight: 700; margin: 0; }
    .enrollments-header-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .enrollments-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
    }
    .enrollments-stat {
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
        backdrop-filter: blur(5px);
    }
    .enrollments-stat-value { font-size: 26px; font-weight: 800; margin: 0; }
    .enrollments-stat-label { font-size: 11px; opacity: 0.9; margin: 4px 0 0 0; }
    
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

<div class="enrollments-header">
    <div class="enrollments-header-top">
        <div class="enrollments-header-info">
            <div class="enrollments-header-icon">
                <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="enrollments-header-title">إدارة الاشتراكات</h1>
                <p class="enrollments-header-subtitle">عرض وإدارة اشتراكات الطلاب في الدورات</p>
            </div>
        </div>
        <a href="{{ $createUrl }}" class="add-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            إضافة اشتراك
        </a>
    </div>
    
    <div class="enrollments-stats">
        <div class="enrollments-stat">
            <p class="enrollments-stat-value">{{ number_format($totalEnrollments) }}</p>
            <p class="enrollments-stat-label">إجمالي الاشتراكات</p>
        </div>
        <div class="enrollments-stat">
            <p class="enrollments-stat-value">{{ number_format($completedEnrollments) }}</p>
            <p class="enrollments-stat-label">أكملوا الدورة</p>
        </div>
        <div class="enrollments-stat">
            <p class="enrollments-stat-value">{{ number_format($inProgressEnrollments) }}</p>
            <p class="enrollments-stat-label">قيد التقدم</p>
        </div>
        <div class="enrollments-stat">
            <p class="enrollments-stat-value">{{ number_format($notStartedEnrollments) }}</p>
            <p class="enrollments-stat-label">لم يبدأوا</p>
        </div>
        <div class="enrollments-stat">
            <p class="enrollments-stat-value">{{ number_format($thisMonthEnrollments) }}</p>
            <p class="enrollments-stat-label">هذا الشهر</p>
        </div>
        <div class="enrollments-stat">
            <p class="enrollments-stat-value">{{ number_format($totalRevenue) }}</p>
            <p class="enrollments-stat-label">الإيرادات (ج.م)</p>
        </div>
    </div>
</div>
