<x-filament-panels::page>
    <style>
        .payout-mgmt { max-width: 100%; }
        
        .mgmt-header {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 50%, #5b21b6 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 10px 40px rgba(124, 58, 237, 0.3);
            position: relative;
            overflow: hidden;
        }
        .mgmt-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        }
        .header-content { position: relative; z-index: 1; }
        .header-top { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 24px; }
        .header-info { display: flex; align-items: center; gap: 16px; }
        .header-icon {
            width: 60px; height: 60px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
        }
        .header-text h1 { font-size: 26px; font-weight: 800; margin: 0; }
        .header-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 16px;
        }
        .stat-card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            padding: 16px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .stat-card:hover { background: rgba(255,255,255,0.25); }
        .stat-card.active { border-color: white; background: rgba(255,255,255,0.3); }
        .stat-value { font-size: 28px; font-weight: 800; margin: 0; }
        .stat-label { font-size: 12px; opacity: 0.9; margin-top: 4px; }
        
        .content-grid {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 24px;
        }
        @media (max-width: 1200px) {
            .content-grid { grid-template-columns: 1fr; }
        }
        
        .requests-list-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            max-height: 700px;
            display: flex;
            flex-direction: column;
        }
        .list-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .list-title { font-size: 15px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 8px; }
        .list-body { flex: 1; overflow-y: auto; }
        
        .request-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: all 0.2s;
        }
        .request-item:hover { background: #f9fafb; }
        .request-item.active { background: #ede9fe; border-right: 3px solid #7c3aed; }
        .request-avatar {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }
        .request-info { flex: 1; min-width: 0; }
        .request-name { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0; }
        .request-number { font-size: 12px; color: #6b7280; margin-top: 2px; }
        .request-meta { display: flex; justify-content: space-between; align-items: center; margin-top: 6px; }
        .request-amount { font-size: 15px; font-weight: 700; color: #059669; }
        .request-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .request-status.pending { background: #fef3c7; color: #92400e; }
        .request-status.approved { background: #dbeafe; color: #1e40af; }
        .request-status.processing { background: #e0e7ff; color: #4338ca; }
        .request-status.completed { background: #dcfce7; color: #166534; }
        .request-status.rejected { background: #fee2e2; color: #991b1b; }
        
        .detail-panel {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .detail-header {
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            color: white;
            padding: 24px;
        }
        .detail-user { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; }
        .detail-avatar {
            width: 56px; height: 56px;
            border-radius: 14px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            font-weight: 700;
        }
        .detail-name { font-size: 18px; font-weight: 700; margin: 0; }
        .detail-email { font-size: 13px; opacity: 0.9; margin-top: 4px; }
        .detail-amount { text-align: center; }
        .amount-value { font-size: 36px; font-weight: 800; }
        .amount-label { font-size: 13px; opacity: 0.9; }
        
        .detail-body { padding: 24px; }
        
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 24px; }
        .info-item { background: #f9fafb; padding: 14px 16px; border-radius: 10px; }
        .info-label { font-size: 12px; color: #6b7280; margin-bottom: 4px; }
        .info-value { font-size: 14px; font-weight: 600; color: #1f2937; }
        
        .action-section {
            background: linear-gradient(135deg, #faf5ff, #f3e8ff);
            border: 1px solid #e9d5ff;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .action-title { font-size: 15px; font-weight: 700; color: #6b21a8; margin: 0 0 16px 0; }
        .action-btns { display: flex; gap: 12px; flex-wrap: wrap; }
        .action-btn {
            flex: 1;
            min-width: 140px;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .action-btn.approve {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
        }
        .action-btn.approve:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4); }
        .action-btn.reject {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
        }
        .action-btn.reject:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4); }
        .action-btn.process {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
        }
        .action-btn.process:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4); }
        .action-btn.complete {
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            color: white;
        }
        .action-btn.complete:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(124, 58, 237, 0.4); }
        
        .form-group { margin-bottom: 16px; }
        .form-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; display: block; }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9d5ff;
            border-radius: 10px;
            font-size: 14px;
        }
        .form-input:focus { outline: none; border-color: #7c3aed; }
        
        .voucher-box {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1px solid #6ee7b7;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
        }
        .voucher-icon { font-size: 48px; margin-bottom: 12px; }
        .voucher-number { font-size: 18px; font-weight: 700; color: #065f46; }
        .voucher-date { font-size: 13px; color: #6b7280; margin-top: 4px; }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            text-align: center;
        }
        .empty-icon { font-size: 64px; margin-bottom: 16px; }
        .empty-title { font-size: 18px; font-weight: 600; color: #374151; margin: 0 0 8px 0; }
        .empty-text { font-size: 14px; color: #6b7280; }
        
        @media (prefers-color-scheme: dark) {
            .requests-list-card, .detail-panel { background: #1f2937; border-color: #374151; }
            .list-header { background: linear-gradient(135deg, #1f2937, #374151); border-color: #374151; }
            .list-title, .request-name, .info-value { color: #f9fafb; }
            .request-item { border-color: #374151; }
            .request-item:hover { background: #374151; }
            .request-item.active { background: #4c1d95; }
            .info-item { background: #374151; }
        }
    </style>

    @php
        $stats = $this->stats;
        $requests = $this->requests;
        $selected = $this->selectedRequest;
        $statuses = \App\Models\PayoutRequest::getStatuses();
        $methods = \App\Models\InstructorPayoutSetting::getPaymentMethods();
    @endphp

    <div class="payout-mgmt">
        {{-- Header --}}
        <div class="mgmt-header">
            <div class="header-content">
                <div class="header-top">
                    <div class="header-info">
                        <div class="header-icon">🏦</div>
                        <div class="header-text">
                            <h1>إدارة طلبات السحب</h1>
                            <p>مراجعة والموافقة على طلبات سحب المدرسين</p>
                        </div>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card {{ $activeFilter === 'pending' ? 'active' : '' }}" wire:click="setFilter('pending')">
                        <div class="stat-value">{{ $stats['pending'] }}</div>
                        <div class="stat-label">قيد الانتظار</div>
                    </div>
                    <div class="stat-card {{ $activeFilter === 'approved' ? 'active' : '' }}" wire:click="setFilter('approved')">
                        <div class="stat-value">{{ $stats['approved'] }}</div>
                        <div class="stat-label">تمت الموافقة</div>
                    </div>
                    <div class="stat-card {{ $activeFilter === 'processing' ? 'active' : '' }}" wire:click="setFilter('processing')">
                        <div class="stat-value">{{ $stats['processing'] }}</div>
                        <div class="stat-label">قيد المعالجة</div>
                    </div>
                    <div class="stat-card {{ $activeFilter === 'completed' ? 'active' : '' }}" wire:click="setFilter('completed')">
                        <div class="stat-value">{{ $stats['completed'] }}</div>
                        <div class="stat-label">مكتملة</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format($stats['total_pending_amount'], 0) }}</div>
                        <div class="stat-label">المعلق (ج.م)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format($stats['total_paid'], 0) }}</div>
                        <div class="stat-label">المدفوع (ج.م)</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-grid">
            {{-- Requests List --}}
            <div class="requests-list-card">
                <div class="list-header">
                    <h3 class="list-title">
                        <span>📋</span>
                        الطلبات ({{ $requests->count() }})
                    </h3>
                </div>
                <div class="list-body">
                    @forelse($requests as $request)
                        <div 
                            class="request-item {{ $selectedRequestId === $request->id ? 'active' : '' }}"
                            wire:click="selectRequest({{ $request->id }})"
                        >
                            <div class="request-avatar">
                                {{ mb_substr($request->user?->name ?? '?', 0, 1) }}
                            </div>
                            <div class="request-info">
                                <p class="request-name">{{ $request->user?->name }}</p>
                                <p class="request-number">{{ $request->request_number }}</p>
                                <div class="request-meta">
                                    <span class="request-amount">{{ number_format($request->net_amount, 2) }} ج.م</span>
                                    <span class="request-status {{ $request->status }}">
                                        {{ $statuses[$request->status] ?? $request->status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <p class="empty-title">لا توجد طلبات</p>
                            <p class="empty-text">لا توجد طلبات في هذا التصنيف</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
            {{-- Detail Panel --}}
            <div class="detail-panel">
                @if($selected)
                    <div class="detail-header">
                        <div class="detail-user">
                            <div class="detail-avatar">
                                {{ mb_substr($selected->user?->name ?? '?', 0, 1) }}
                            </div>
                            <div>
                                <p class="detail-name">{{ $selected->user?->name }}</p>
                                <p class="detail-email">{{ $selected->user?->email }}</p>
                            </div>
                        </div>
                        <div class="detail-amount">
                            <div class="amount-value">{{ number_format($selected->net_amount, 2) }} ج.م</div>
                            <div class="amount-label">صافي المبلغ</div>
                        </div>
                    </div>
                    
                    <div class="detail-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">رقم الطلب</div>
                                <div class="info-value">{{ $selected->request_number }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">تاريخ الطلب</div>
                                <div class="info-value">{{ $selected->requested_at->format('Y/m/d H:i') }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">المبلغ المطلوب</div>
                                <div class="info-value">{{ number_format($selected->requested_amount, 2) }} ج.م</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">رسوم المعالجة</div>
                                <div class="info-value">{{ number_format($selected->admin_fee, 2) }} ج.م</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">طريقة الدفع</div>
                                <div class="info-value">{{ $methods[$selected->payment_method] ?? $selected->payment_method }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">الحالة</div>
                                <div class="info-value">
                                    <span class="request-status {{ $selected->status }}">
                                        {{ $statuses[$selected->status] ?? $selected->status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        @if($selected->payment_details)
                            <div class="info-grid" style="margin-bottom: 24px;">
                                @foreach($selected->payment_details as $key => $value)
                                    @if($value)
                                        <div class="info-item">
                                            <div class="info-label">{{ str_replace('_', ' ', ucfirst($key)) }}</div>
                                            <div class="info-value">{{ $value }}</div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        
                        @if($selected->status === 'pending')
                            <div class="action-section">
                                <h4 class="action-title">إجراءات</h4>
                                <div class="action-btns">
                                    <button class="action-btn approve" wire:click="approveRequest">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        الموافقة
                                    </button>
                                    <button class="action-btn reject" wire:click="$set('showRejectForm', true)">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        رفض
                                    </button>
                                </div>
                                
                                <div class="form-group" style="margin-top: 16px;">
                                    <label class="form-label">سبب الرفض (إن وجد)</label>
                                    <textarea class="form-input" wire:model="rejectionReason" rows="2" placeholder="أدخل سبب الرفض..."></textarea>
                                </div>
                                <button class="action-btn reject" wire:click="rejectRequest" style="width: 100%; margin-top: 8px;">
                                    تأكيد الرفض
                                </button>
                            </div>
                        @endif
                        
                        @if($selected->status === 'approved')
                            <div class="action-section">
                                <h4 class="action-title">بدء المعالجة</h4>
                                <button class="action-btn process" wire:click="startProcessing" style="width: 100%;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    بدء معالجة الدفع
                                </button>
                            </div>
                        @endif
                        
                        @if(in_array($selected->status, ['approved', 'processing']))
                            <div class="action-section">
                                <h4 class="action-title">إصدار سند الدفع</h4>
                                <div class="form-group">
                                    <label class="form-label">رقم المعاملة/التحويل</label>
                                    <input type="text" class="form-input" wire:model="transactionReference" placeholder="رقم التحويل البنكي أو المعاملة">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">ملاحظات</label>
                                    <textarea class="form-input" wire:model="voucherNotes" rows="2" placeholder="أي ملاحظات إضافية..."></textarea>
                                </div>
                                <button class="action-btn complete" wire:click="completeAndIssueVoucher" style="width: 100%;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    إتمام الدفع وإصدار السند
                                </button>
                            </div>
                        @endif
                        
                        @if($selected->voucher)
                            <div class="voucher-box">
                                <div class="voucher-icon">🧾</div>
                                <div class="voucher-number">سند دفع: {{ $selected->voucher->voucher_number }}</div>
                                <div class="voucher-date">تاريخ الإصدار: {{ $selected->voucher->issued_at->format('Y/m/d H:i') }}</div>
                                @if($selected->voucher->transaction_reference)
                                    <div class="voucher-date">رقم المعاملة: {{ $selected->voucher->transaction_reference }}</div>
                                @endif
                            </div>
                        @endif
                        
                        @if($selected->status === 'rejected' && $selected->rejection_reason)
                            <div style="background: #fee2e2; border: 1px solid #fecaca; border-radius: 12px; padding: 16px; margin-top: 16px;">
                                <p style="font-weight: 600; color: #991b1b; margin: 0 0 8px 0;">❌ سبب الرفض:</p>
                                <p style="color: #7f1d1d; margin: 0;">{{ $selected->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">👈</div>
                        <p class="empty-title">اختر طلباً للعرض</p>
                        <p class="empty-text">اختر طلب سحب من القائمة لعرض تفاصيله ومعالجته</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
