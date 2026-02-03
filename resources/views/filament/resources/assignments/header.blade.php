<style>
    .assignments-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
        border-radius: 20px;
        padding: 28px 32px;
        color: white;
        margin-bottom: 24px;
        box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3);
        position: relative;
        overflow: hidden;
    }
    .assignments-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 80%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
    }
    .assignments-header-top {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 24px;
    }
    .assignments-header-info { display: flex; align-items: center; gap: 16px; }
    .assignments-header-icon {
        width: 60px; height: 60px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px;
    }
    .assignments-header-text h1 { font-size: 26px; font-weight: 800; margin: 0; }
    .assignments-header-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
    .add-assignment-btn {
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        display: flex; align-items: center; gap: 8px;
        border: 1px solid rgba(255,255,255,0.3);
        transition: all 0.3s;
    }
    .add-assignment-btn:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-2px);
    }
    .assignments-stats {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 16px;
    }
    .assignment-stat {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 16px 20px;
        text-align: center;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .assignment-stat-value { font-size: 28px; font-weight: 800; margin: 0; line-height: 1; }
    .assignment-stat-label { font-size: 12px; opacity: 0.9; margin-top: 6px; }
</style>

<div class="assignments-header">
    <div class="assignments-header-top">
        <div class="assignments-header-info">
            <div class="assignments-header-icon">📋</div>
            <div class="assignments-header-text">
                <h1>الواجبات والمشاريع</h1>
                <p>إدارة الواجبات وتقييم تسليمات الطلاب</p>
            </div>
        </div>
        
        <a href="{{ $createUrl }}" class="add-assignment-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            إضافة واجب
        </a>
    </div>
    
    <div class="assignments-stats">
        <div class="assignment-stat">
            <p class="assignment-stat-value">{{ $totalAssignments }}</p>
            <p class="assignment-stat-label">إجمالي الواجبات</p>
        </div>
        <div class="assignment-stat">
            <p class="assignment-stat-value">{{ $totalProjects }}</p>
            <p class="assignment-stat-label">المشاريع</p>
        </div>
        <div class="assignment-stat">
            <p class="assignment-stat-value">{{ $totalSubmissions }}</p>
            <p class="assignment-stat-label">التسليمات</p>
        </div>
        <div class="assignment-stat" style="background: rgba(251, 191, 36, 0.3);">
            <p class="assignment-stat-value">{{ $pendingGrading }}</p>
            <p class="assignment-stat-label">بانتظار التقييم</p>
        </div>
        <div class="assignment-stat" style="background: rgba(34, 197, 94, 0.3);">
            <p class="assignment-stat-value">{{ $gradedCount }}</p>
            <p class="assignment-stat-label">تم تقييمها</p>
        </div>
    </div>
</div>
