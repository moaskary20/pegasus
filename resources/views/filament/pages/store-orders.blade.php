<x-filament-panels::page>
    <style>
        .store-header { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%); border-radius: 20px; padding: 28px 32px; color: white; margin-bottom: 24px; box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3); position: relative; overflow: hidden; }
        .store-header::before { content: ''; position: absolute; top: -50%; right: -30%; width: 80%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%); }
        .store-header-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .store-header-info { display: flex; align-items: center; gap: 16px; }
        .store-header-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 16px; display: flex; align-items: center; justify-content: center; }
        .store-header-text h1 { font-size: 26px; font-weight: 800; margin: 0; }
        .store-header-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        .header-stats { display: flex; gap: 24px; }
        .header-stat { text-align: center; }
        .header-stat-value { font-size: 28px; font-weight: 800; }
        .header-stat-label { font-size: 12px; opacity: 0.9; }
        
        .orders-container { display: flex; gap: 24px; min-height: 600px; }
        .orders-list { flex: 1; }
        .order-detail { width: 420px; flex-shrink: 0; }
        
        .filters-bar { background: white; border-radius: 14px; padding: 16px 20px; margin-bottom: 20px; display: flex; gap: 16px; flex-wrap: wrap; align-items: center; border: 1px solid #e5e7eb; }
        .filter-input { padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 13px; min-width: 200px; }
        .filter-select { padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 13px; background: white; }
        
        .panel { background: white; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; overflow: hidden; }
        .panel-header { background: linear-gradient(135deg, #f8fafc, #f1f5f9); padding: 18px 24px; border-bottom: 1px solid #e5e7eb; }
        .panel-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; }
        
        .order-item { padding: 16px 20px; border-bottom: 1px solid #f3f4f6; cursor: pointer; transition: all 0.2s; }
        .order-item:hover { background: #f9fafb; }
        .order-item.active { background: linear-gradient(135deg, #ede9fe, #ddd6fe); border-right: 3px solid #8b5cf6; }
        .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .order-number { font-weight: 700; color: #1f2937; font-size: 14px; }
        .order-date { font-size: 12px; color: #6b7280; }
        .order-customer { font-size: 13px; color: #374151; margin-bottom: 6px; }
        .order-footer { display: flex; justify-content: space-between; align-items: center; }
        .order-total { font-weight: 700; color: #8b5cf6; }
        
        .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .status-badge.pending { background: #fef3c7; color: #92400e; }
        .status-badge.processing { background: #dbeafe; color: #1e40af; }
        .status-badge.shipped { background: #e0e7ff; color: #3730a3; }
        .status-badge.delivered { background: #dcfce7; color: #166534; }
        .status-badge.cancelled { background: #fee2e2; color: #991b1b; }
        .status-badge.paid { background: #dcfce7; color: #166534; }
        .status-badge.unpaid { background: #fef3c7; color: #92400e; }
        
        .detail-section { padding: 20px; border-bottom: 1px solid #f3f4f6; }
        .detail-section:last-child { border-bottom: none; }
        .detail-title { font-size: 14px; font-weight: 700; color: #1f2937; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px; }
        .detail-label { color: #6b7280; }
        .detail-value { color: #1f2937; font-weight: 500; }
        
        .order-items-table { width: 100%; font-size: 13px; }
        .order-items-table td { padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
        .item-name { font-weight: 600; color: #1f2937; }
        .item-qty { color: #6b7280; }
        .item-price { font-weight: 600; color: #8b5cf6; text-align: left; }
        
        .totals-section { background: #f9fafb; padding: 16px; border-radius: 10px; margin-top: 16px; }
        .total-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px; }
        .total-row.grand { font-size: 16px; font-weight: 700; color: #1f2937; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb; }
        
        .action-buttons { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
        .action-btn { padding: 10px 16px; border: none; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px; }
        .action-btn.primary { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }
        .action-btn.success { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; }
        .action-btn.warning { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
        .action-btn.danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
        .action-btn.secondary { background: #f3f4f6; color: #374151; }
        .action-btn:hover { transform: translateY(-1px); }
        
        .form-group { margin-bottom: 12px; }
        .form-label { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; display: block; }
        .form-input, .form-textarea { width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; }
        
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-icon { width: 80px; height: 80px; background: linear-gradient(135deg, #f3f4f6, #e5e7eb); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .empty-title { font-size: 18px; font-weight: 700; color: #374151; margin: 0 0 8px 0; }
        .empty-text { font-size: 14px; color: #6b7280; margin: 0; }
        .success-message { background: linear-gradient(135deg, #dcfce7, #bbf7d0); border: 1px solid #86efac; border-radius: 12px; padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: #166534; font-weight: 600; }
        
        @media (max-width: 1200px) { .orders-container { flex-direction: column; } .order-detail { width: 100%; } }
        @media (prefers-color-scheme: dark) {
            .panel, .filters-bar { background: #1f2937; border-color: #374151; }
            .panel-header { background: linear-gradient(135deg, #1f2937, #374151); }
            .panel-title, .order-number, .detail-title, .item-name { color: #f9fafb; }
            .order-item { border-color: #374151; }
            .order-item:hover { background: #374151; }
            .order-item.active { background: linear-gradient(135deg, #4c1d95, #5b21b6); }
            .filter-input, .filter-select, .form-input, .form-textarea { background: #374151; border-color: #4b5563; color: #f9fafb; }
            .totals-section { background: #374151; }
        }
    </style>
    
    @php
        $orders = $this->orders;
        $selectedOrder = $this->selectedOrder;
        $stats = $this->stats;
    @endphp
    
    {{-- Header --}}
    <div class="store-header">
        <div class="store-header-content">
            <div class="store-header-info">
                <div class="store-header-icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="store-header-text">
                    <h1>إدارة الطلبات</h1>
                    <p>متابعة ومعالجة طلبات المتجر</p>
                </div>
            </div>
            <div class="header-stats">
                <div class="header-stat">
                    <p class="header-stat-value">{{ $stats['pending'] }}</p>
                    <p class="header-stat-label">قيد الانتظار</p>
                </div>
                <div class="header-stat">
                    <p class="header-stat-value">{{ number_format($stats['total_sales'], 0) }}</p>
                    <p class="header-stat-label">إجمالي المبيعات</p>
                </div>
            </div>
        </div>
    </div>
    
    @if(session('success'))
    <div class="success-message">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    
    {{-- Filters --}}
    <div class="filters-bar">
        <input type="text" class="filter-input" wire:model.live.debounce.300ms="search" placeholder="البحث برقم الطلب أو اسم العميل...">
        <select class="filter-select" wire:model.live="filterStatus">
            <option value="all">كل الحالات</option>
            <option value="pending">قيد الانتظار</option>
            <option value="processing">قيد المعالجة</option>
            <option value="shipped">تم الشحن</option>
            <option value="delivered">تم التسليم</option>
            <option value="cancelled">ملغي</option>
        </select>
        <select class="filter-select" wire:model.live="filterPayment">
            <option value="all">كل حالات الدفع</option>
            <option value="pending">في انتظار الدفع</option>
            <option value="paid">مدفوع</option>
        </select>
    </div>
    
    <div class="orders-container">
        {{-- Orders List --}}
        <div class="orders-list">
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title">الطلبات ({{ $orders->total() }})</h3>
                </div>
                @forelse($orders as $order)
                <div class="order-item {{ $selectedOrderId === $order->id ? 'active' : '' }}" wire:click="selectOrder({{ $order->id }})">
                    <div class="order-header">
                        <span class="order-number">{{ $order->order_number }}</span>
                        <span class="order-date">{{ $order->created_at->format('Y/m/d H:i') }}</span>
                    </div>
                    <div class="order-customer">{{ $order->customer_name }}</div>
                    <div class="order-footer">
                        <div style="display: flex; gap: 8px;">
                            <span class="status-badge {{ $order->status }}">{{ $order->status_label }}</span>
                            <span class="status-badge {{ $order->payment_status === 'paid' ? 'paid' : 'unpaid' }}">{{ $order->payment_status_label }}</span>
                        </div>
                        <span class="order-total">{{ number_format($order->total, 2) }} ج.م</span>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-icon"><svg width="32" height="32" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                    <p class="empty-title">لا توجد طلبات</p>
                </div>
                @endforelse
                @if($orders->hasPages())
                <div style="padding: 16px;">{{ $orders->links() }}</div>
                @endif
            </div>
        </div>
        
        {{-- Order Detail --}}
        <div class="order-detail">
            <div class="panel">
                @if($selectedOrder)
                <div class="panel-header">
                    <h3 class="panel-title">تفاصيل الطلب {{ $selectedOrder->order_number }}</h3>
                </div>
                
                <div class="detail-section">
                    <h4 class="detail-title">معلومات العميل</h4>
                    <div class="detail-row"><span class="detail-label">الاسم:</span><span class="detail-value">{{ $selectedOrder->customer_name }}</span></div>
                    <div class="detail-row"><span class="detail-label">البريد:</span><span class="detail-value">{{ $selectedOrder->customer_email }}</span></div>
                    <div class="detail-row"><span class="detail-label">الهاتف:</span><span class="detail-value">{{ $selectedOrder->customer_phone ?? '-' }}</span></div>
                </div>
                
                <div class="detail-section">
                    <h4 class="detail-title">عنوان الشحن</h4>
                    <div class="detail-row"><span class="detail-label">العنوان:</span><span class="detail-value">{{ $selectedOrder->shipping_address }}</span></div>
                    <div class="detail-row"><span class="detail-label">المدينة:</span><span class="detail-value">{{ $selectedOrder->shipping_city }}</span></div>
                    @if($selectedOrder->tracking_number)
                    <div class="detail-row"><span class="detail-label">رقم التتبع:</span><span class="detail-value">{{ $selectedOrder->tracking_number }}</span></div>
                    @endif
                </div>
                
                <div class="detail-section">
                    <h4 class="detail-title">المنتجات</h4>
                    <table class="order-items-table">
                        @foreach($selectedOrder->items as $item)
                        <tr>
                            <td>
                                <span class="item-name">{{ $item->product_name }}</span>
                                <br><span class="item-qty">× {{ $item->quantity }}</span>
                            </td>
                            <td class="item-price">{{ number_format($item->total, 2) }} ج.م</td>
                        </tr>
                        @endforeach
                    </table>
                    
                    <div class="totals-section">
                        <div class="total-row"><span>المجموع الفرعي:</span><span>{{ number_format($selectedOrder->subtotal, 2) }} ج.م</span></div>
                        <div class="total-row"><span>الشحن:</span><span>{{ number_format($selectedOrder->shipping_cost, 2) }} ج.م</span></div>
                        @if($selectedOrder->discount_amount > 0)
                        <div class="total-row"><span>الخصم:</span><span style="color: #22c55e;">-{{ number_format($selectedOrder->discount_amount, 2) }} ج.م</span></div>
                        @endif
                        <div class="total-row grand"><span>الإجمالي:</span><span>{{ number_format($selectedOrder->total, 2) }} ج.م</span></div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="form-group">
                        <label class="form-label">رقم التتبع</label>
                        <input type="text" class="form-input" wire:model="trackingNumber" placeholder="أدخل رقم تتبع الشحنة">
                    </div>
                    <div class="form-group">
                        <label class="form-label">ملاحظات المدير</label>
                        <textarea class="form-textarea" wire:model="adminNotes" rows="2" placeholder="ملاحظات داخلية..."></textarea>
                    </div>
                    <button class="action-btn secondary" wire:click="saveNotes" style="width: 100%;">حفظ الملاحظات</button>
                </div>
                
                <div class="detail-section">
                    <div class="action-buttons">
                        @if($selectedOrder->payment_status !== 'paid')
                        <button class="action-btn success" wire:click="markAsPaid">تأكيد الدفع</button>
                        @endif
                        @if($selectedOrder->status === 'pending')
                        <button class="action-btn primary" wire:click="updateStatus('processing')">بدء المعالجة</button>
                        @endif
                        @if($selectedOrder->status === 'processing')
                        <button class="action-btn warning" wire:click="updateStatus('shipped')">تم الشحن</button>
                        @endif
                        @if($selectedOrder->status === 'shipped')
                        <button class="action-btn success" wire:click="updateStatus('delivered')">تم التسليم</button>
                        @endif
                        @if(!in_array($selectedOrder->status, ['delivered', 'cancelled']))
                        <button class="action-btn danger" wire:click="updateStatus('cancelled')" wire:confirm="هل أنت متأكد من إلغاء الطلب؟">إلغاء الطلب</button>
                        @endif
                    </div>
                </div>
                @else
                <div class="empty-state">
                    <div class="empty-icon"><svg width="32" height="32" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg></div>
                    <p class="empty-title">اختر طلب</p>
                    <p class="empty-text">اختر طلب من القائمة لعرض التفاصيل</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
