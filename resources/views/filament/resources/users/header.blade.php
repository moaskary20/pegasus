<style>
    .users-header {
        background: linear-gradient(135deg, #3b82f6 0%, #6366f1 50%, #8b5cf6 100%);
        border-radius: 16px;
        padding: 24px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
    }
    .users-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }
    .users-header-info { display: flex; align-items: center; gap: 14px; }
    .users-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .users-header-title { font-size: 24px; font-weight: 700; margin: 0; }
    .users-header-subtitle { font-size: 13px; opacity: 0.9; margin: 4px 0 0 0; }
    
    .users-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
    }
    .users-stat {
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
        backdrop-filter: blur(5px);
    }
    .users-stat-value { font-size: 26px; font-weight: 800; margin: 0; }
    .users-stat-label { font-size: 11px; opacity: 0.9; margin: 4px 0 0 0; }
    
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

<div class="users-header">
    <div class="users-header-top">
        <div class="users-header-info">
            <div class="users-header-icon">
                <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="users-header-title">إدارة المستخدمين</h1>
                <p class="users-header-subtitle">عرض وإدارة جميع المستخدمين المسجلين</p>
            </div>
        </div>
        <a href="{{ $createUrl }}" class="add-btn">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            إضافة مستخدم
        </a>
    </div>
    
    <div class="users-stats">
        <div class="users-stat">
            <p class="users-stat-value">{{ number_format($totalUsers) }}</p>
            <p class="users-stat-label">إجمالي المستخدمين</p>
        </div>
        <div class="users-stat">
            <p class="users-stat-value">{{ number_format($admins) }}</p>
            <p class="users-stat-label">المدراء</p>
        </div>
        <div class="users-stat">
            <p class="users-stat-value">{{ number_format($instructors) }}</p>
            <p class="users-stat-label">المدرسون</p>
        </div>
        <div class="users-stat">
            <p class="users-stat-value">{{ number_format($students) }}</p>
            <p class="users-stat-label">الطلاب</p>
        </div>
        <div class="users-stat">
            <p class="users-stat-value">{{ number_format($verifiedUsers) }}</p>
            <p class="users-stat-label">بريد مؤكد</p>
        </div>
        <div class="users-stat">
            <p class="users-stat-value">{{ number_format($newThisMonth) }}</p>
            <p class="users-stat-label">جديد هذا الشهر</p>
        </div>
    </div>
</div>
