<x-filament-panels::page>
    <style>
        .store-header { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%); border-radius: 20px; padding: 28px 32px; color: white; margin-bottom: 24px; box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3); position: relative; overflow: hidden; }
        .store-header::before { content: ''; position: absolute; top: -50%; right: -30%; width: 80%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%); }
        .store-header-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .store-header-info { display: flex; align-items: center; gap: 16px; }
        .store-header-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 16px; display: flex; align-items: center; justify-content: center; }
        .store-header-text h1 { font-size: 26px; font-weight: 800; margin: 0; }
        .store-header-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        
        .report-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
        .report-tab { background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s; }
        .report-tab:hover { background: rgba(255,255,255,0.3); }
        .report-tab.active { background: white; color: #7c3aed; }
        
        .period-selector { display: flex; gap: 8px; margin-bottom: 20px; }
        .period-btn { padding: 8px 16px; border: 1px solid #e5e7eb; border-radius: 8px; background: white; font-size: 12px; font-weight: 600; color: #374151; cursor: pointer; transition: all 0.2s; }
        .period-btn:hover { border-color: #8b5cf6; }
        .period-btn.active { background: #8b5cf6; color: white; border-color: #8b5cf6; }
        .date-input { padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 12px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .stat-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        .stat-icon.purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .stat-icon.green { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .stat-icon.blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .stat-icon.amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-value { font-size: 28px; font-weight: 800; color: #1f2937; margin: 0; }
        .stat-label { font-size: 12px; color: #6b7280; margin: 4px 0 0 0; }
        
        .report-panel { background: white; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; overflow: hidden; margin-bottom: 24px; }
        .panel-header { background: linear-gradient(135deg, #f8fafc, #f1f5f9); padding: 18px 24px; border-bottom: 1px solid #e5e7eb; }
        .panel-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 10px; }
        .panel-body { padding: 24px; }
        
        .report-table { width: 100%; border-collapse: collapse; }
        .report-table th { text-align: right; padding: 14px 16px; font-size: 12px; font-weight: 600; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        .report-table td { padding: 14px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; }
        .report-table tr:hover { background: #fafafa; }
        
        .product-rank { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; }
        .product-rank.gold { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
        .product-rank.silver { background: linear-gradient(135deg, #f3f4f6, #e5e7eb); color: #374151; }
        .product-rank.bronze { background: linear-gradient(135deg, #fed7aa, #fdba74); color: #9a3412; }
        .product-rank.normal { background: #f3f4f6; color: #6b7280; }
        
        .charts-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .chart-container { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        .chart-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0 0 20px 0; }
        
        .status-distribution { display: flex; flex-wrap: wrap; gap: 12px; }
        .status-item { display: flex; align-items: center; gap: 8px; padding: 12px 16px; background: #f9fafb; border-radius: 10px; flex: 1; min-width: 150px; }
        .status-dot { width: 12px; height: 12px; border-radius: 50%; }
        .status-dot.pending { background: #f59e0b; }
        .status-dot.processing { background: #3b82f6; }
        .status-dot.shipped { background: #6366f1; }
        .status-dot.delivered { background: #22c55e; }
        .status-dot.cancelled { background: #ef4444; }
        .status-info { flex: 1; }
        .status-name { font-size: 12px; color: #6b7280; }
        .status-count { font-size: 18px; font-weight: 700; color: #1f2937; }
        
        .low-stock-item { display: flex; align-items: center; justify-content: space-between; padding: 12px; background: #fef3c7; border-radius: 8px; margin-bottom: 8px; }
        .low-stock-name { font-weight: 600; color: #92400e; }
        .low-stock-qty { font-size: 14px; font-weight: 700; color: #dc2626; }
        
        @media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .charts-grid { grid-template-columns: 1fr; } }
        @media (prefers-color-scheme: dark) {
            .stat-card, .report-panel, .chart-container { background: #1f2937; border-color: #374151; }
            .stat-value, .panel-title, .chart-title, .status-count { color: #f9fafb; }
            .panel-header { background: linear-gradient(135deg, #1f2937, #374151); }
            .report-table th { background: #374151; color: #d1d5db; }
            .report-table td { color: #e5e7eb; border-color: #374151; }
            .period-btn, .date-input { background: #374151; border-color: #4b5563; color: #f9fafb; }
            .status-item { background: #374151; }
        }
    </style>
    
    @php
        $salesStats = $this->salesStats;
        $dailySales = $this->dailySales;
        $topProducts = $this->topProducts;
        $inventoryStats = $this->inventoryStats;
        $lowStockProducts = $this->lowStockProducts;
        $ordersByStatus = $this->ordersByStatus;
    @endphp
    
    {{-- Header --}}
    <div class="store-header">
        <div class="store-header-content">
            <div class="store-header-info">
                <div class="store-header-icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="store-header-text">
                    <h1>تقارير المتجر</h1>
                    <p>إحصائيات ومؤشرات أداء المتجر</p>
                </div>
            </div>
            <div class="report-tabs">
                <button class="report-tab {{ $activeTab === 'overview' ? 'active' : '' }}" wire:click="setTab('overview')">نظرة عامة</button>
                <button class="report-tab {{ $activeTab === 'products' ? 'active' : '' }}" wire:click="setTab('products')">المنتجات</button>
                <button class="report-tab {{ $activeTab === 'inventory' ? 'active' : '' }}" wire:click="setTab('inventory')">المخزون</button>
            </div>
        </div>
    </div>
    
    {{-- Period Selector --}}
    <div class="period-selector">
        <button class="period-btn {{ $period === 'week' ? 'active' : '' }}" wire:click="setPeriod('week')">الأسبوع</button>
        <button class="period-btn {{ $period === 'month' ? 'active' : '' }}" wire:click="setPeriod('month')">الشهر</button>
        <button class="period-btn {{ $period === 'year' ? 'active' : '' }}" wire:click="setPeriod('year')">السنة</button>
        <input type="date" class="date-input" wire:model.live="startDate">
        <span style="color: #6b7280;">إلى</span>
        <input type="date" class="date-input" wire:model.live="endDate">
    </div>
    
    @if($activeTab === 'overview')
    {{-- Stats Grid --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon purple"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
            </div>
            <p class="stat-value">{{ number_format($salesStats['total_orders']) }}</p>
            <p class="stat-label">إجمالي الطلبات</p>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon green"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            </div>
            <p class="stat-value">{{ number_format($salesStats['total_sales'], 0) }}</p>
            <p class="stat-label">إجمالي المبيعات (ج.م)</p>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon blue"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></div>
            </div>
            <p class="stat-value">{{ number_format($salesStats['average_order'], 0) }}</p>
            <p class="stat-label">متوسط الطلب (ج.م)</p>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon amber"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            </div>
            <p class="stat-value">{{ number_format($salesStats['delivered_orders']) }}</p>
            <p class="stat-label">الطلبات المُسلّمة</p>
        </div>
    </div>
    
    {{-- Charts --}}
    <div class="charts-grid">
        <div class="chart-container">
            <h4 class="chart-title">المبيعات اليومية</h4>
            @if($dailySales->count() > 0)
            <table class="report-table">
                <thead><tr><th>التاريخ</th><th>الطلبات</th><th>المبيعات</th></tr></thead>
                <tbody>
                @foreach($dailySales->take(10) as $day)
                <tr>
                    <td>{{ $day->date }}</td>
                    <td>{{ $day->orders_count }}</td>
                    <td style="font-weight: 700; color: #8b5cf6;">{{ number_format($day->total_sales, 2) }} ج.م</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <p style="text-align: center; color: #9ca3af; padding: 40px;">لا توجد مبيعات في هذه الفترة</p>
            @endif
        </div>
        
        <div class="chart-container">
            <h4 class="chart-title">توزيع حالات الطلبات</h4>
            <div class="status-distribution">
                <div class="status-item">
                    <div class="status-dot pending"></div>
                    <div class="status-info"><p class="status-name">قيد الانتظار</p><p class="status-count">{{ $ordersByStatus['pending'] ?? 0 }}</p></div>
                </div>
                <div class="status-item">
                    <div class="status-dot processing"></div>
                    <div class="status-info"><p class="status-name">قيد المعالجة</p><p class="status-count">{{ $ordersByStatus['processing'] ?? 0 }}</p></div>
                </div>
                <div class="status-item">
                    <div class="status-dot shipped"></div>
                    <div class="status-info"><p class="status-name">تم الشحن</p><p class="status-count">{{ $ordersByStatus['shipped'] ?? 0 }}</p></div>
                </div>
                <div class="status-item">
                    <div class="status-dot delivered"></div>
                    <div class="status-info"><p class="status-name">تم التسليم</p><p class="status-count">{{ $ordersByStatus['delivered'] ?? 0 }}</p></div>
                </div>
                <div class="status-item">
                    <div class="status-dot cancelled"></div>
                    <div class="status-info"><p class="status-name">ملغي</p><p class="status-count">{{ $ordersByStatus['cancelled'] ?? 0 }}</p></div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    @if($activeTab === 'products')
    <div class="report-panel">
        <div class="panel-header"><h3 class="panel-title">المنتجات الأكثر مبيعاً</h3></div>
        <div class="panel-body" style="padding: 0;">
            @if($topProducts->count() > 0)
            <table class="report-table">
                <thead><tr><th>#</th><th>المنتج</th><th>الكمية المباعة</th><th>الإيرادات</th></tr></thead>
                <tbody>
                @foreach($topProducts as $index => $product)
                <tr>
                    <td>
                        <span class="product-rank {{ $index === 0 ? 'gold' : ($index === 1 ? 'silver' : ($index === 2 ? 'bronze' : 'normal')) }}">
                            {{ $index + 1 }}
                        </span>
                    </td>
                    <td style="font-weight: 600;">{{ $product->product_name }}</td>
                    <td>{{ number_format($product->total_quantity) }}</td>
                    <td style="font-weight: 700; color: #8b5cf6;">{{ number_format($product->total_revenue, 2) }} ج.م</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <p style="text-align: center; color: #9ca3af; padding: 60px;">لا توجد مبيعات في هذه الفترة</p>
            @endif
        </div>
    </div>
    @endif
    
    @if($activeTab === 'inventory')
    <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
        <div class="stat-card">
            <p class="stat-value">{{ number_format($inventoryStats['total_products']) }}</p>
            <p class="stat-label">إجمالي المنتجات</p>
        </div>
        <div class="stat-card">
            <p class="stat-value">{{ number_format($inventoryStats['active_products']) }}</p>
            <p class="stat-label">المنتجات النشطة</p>
        </div>
        <div class="stat-card">
            <p class="stat-value" style="color: #f59e0b;">{{ number_format($inventoryStats['low_stock']) }}</p>
            <p class="stat-label">مخزون منخفض</p>
        </div>
        <div class="stat-card">
            <p class="stat-value" style="color: #ef4444;">{{ number_format($inventoryStats['out_of_stock']) }}</p>
            <p class="stat-label">غير متوفر</p>
        </div>
        <div class="stat-card">
            <p class="stat-value" style="color: #22c55e;">{{ number_format($inventoryStats['total_stock_value'], 0) }}</p>
            <p class="stat-label">قيمة المخزون (ج.م)</p>
        </div>
    </div>
    
    <div class="report-panel">
        <div class="panel-header"><h3 class="panel-title">المنتجات منخفضة المخزون</h3></div>
        <div class="panel-body">
            @forelse($lowStockProducts as $product)
            <div class="low-stock-item">
                <div>
                    <span class="low-stock-name">{{ $product->name }}</span>
                    <span style="font-size: 11px; color: #9ca3af; margin-right: 8px;">{{ $product->sku }}</span>
                </div>
                <span class="low-stock-qty">{{ $product->quantity }} / {{ $product->low_stock_threshold }}</span>
            </div>
            @empty
            <p style="text-align: center; color: #22c55e; padding: 40px;">لا توجد منتجات منخفضة المخزون</p>
            @endforelse
        </div>
    </div>
    @endif
</x-filament-panels::page>
