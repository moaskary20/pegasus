<x-filament-panels::page>
    <style>
        .payouts-container { max-width: 100%; }
        
        .payouts-header {
            background: linear-gradient(135deg, #059669 0%, #047857 50%, #065f46 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 10px 40px rgba(5, 150, 105, 0.3);
            position: relative;
            overflow: hidden;
        }
        .payouts-header::before {
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
        
        .balance-box {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 20px 28px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .balance-label { font-size: 13px; opacity: 0.9; margin-bottom: 4px; }
        .balance-value { font-size: 36px; font-weight: 800; }
        .balance-value span { font-size: 16px; font-weight: 500; }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 16px;
        }
        .stat-box {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 16px 20px;
            text-align: center;
        }
        .stat-value { font-size: 24px; font-weight: 800; margin: 0; }
        .stat-label { font-size: 12px; opacity: 0.9; margin-top: 4px; }
        
        .tabs-nav {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            background: white;
            padding: 8px;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .tab-btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .tab-btn:hover { background: #f3f4f6; }
        .tab-btn.active {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }
        
        .card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }
        .card-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
        }
        .card-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 10px; }
        .card-body { padding: 24px; }
        
        .course-earnings { display: flex; flex-direction: column; gap: 16px; }
        .course-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: #f9fafb;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
        .course-icon {
            width: 50px; height: 50px;
            background: linear-gradient(135deg, #059669, #047857);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white;
            font-size: 20px;
        }
        .course-info { flex: 1; }
        .course-title { font-size: 15px; font-weight: 600; color: #1f2937; margin: 0; }
        .course-meta { font-size: 12px; color: #6b7280; margin-top: 4px; }
        .course-earnings-value { text-align: left; }
        .earnings-amount { font-size: 20px; font-weight: 800; color: #059669; }
        .earnings-label { font-size: 11px; color: #6b7280; }
        
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
        .form-group { margin-bottom: 16px; }
        .form-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; display: block; }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-input:focus { outline: none; border-color: #059669; }
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            background: white;
        }
        .form-select:focus { outline: none; border-color: #059669; }
        
        .payment-methods { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
        .method-btn {
            flex: 1;
            min-width: 140px;
            padding: 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: white;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }
        .method-btn:hover { border-color: #059669; }
        .method-btn.active { border-color: #059669; background: #ecfdf5; }
        .method-icon { font-size: 24px; margin-bottom: 8px; }
        .method-name { font-size: 13px; font-weight: 600; color: #374151; }
        
        .save-btn {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .save-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4); }
        
        .payout-btn {
            width: 100%;
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            padding: 18px;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
        }
        .payout-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4); }
        .payout-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        
        .requests-list { display: flex; flex-direction: column; gap: 16px; }
        .request-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px 24px;
            background: #f9fafb;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
        }
        .request-icon {
            width: 50px; height: 50px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        .request-icon.pending { background: #fef3c7; }
        .request-icon.approved { background: #dbeafe; }
        .request-icon.processing { background: #e0e7ff; }
        .request-icon.completed { background: #dcfce7; }
        .request-icon.rejected { background: #fee2e2; }
        .request-info { flex: 1; }
        .request-number { font-size: 14px; font-weight: 700; color: #1f2937; }
        .request-date { font-size: 12px; color: #6b7280; margin-top: 2px; }
        .request-amount { text-align: left; }
        .request-value { font-size: 18px; font-weight: 800; color: #059669; }
        .request-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .request-status.pending { background: #fef3c7; color: #92400e; }
        .request-status.approved { background: #dbeafe; color: #1e40af; }
        .request-status.processing { background: #e0e7ff; color: #4338ca; }
        .request-status.completed { background: #dcfce7; color: #166534; }
        .request-status.rejected { background: #fee2e2; color: #991b1b; }
        
        .empty-state {
            text-align: center;
            padding: 60px 40px;
        }
        .empty-icon { font-size: 64px; margin-bottom: 16px; }
        .empty-title { font-size: 18px; font-weight: 600; color: #374151; margin: 0 0 8px 0; }
        .empty-text { font-size: 14px; color: #6b7280; }
        
        @media (prefers-color-scheme: dark) {
            .card, .tabs-nav { background: #1f2937; border-color: #374151; }
            .card-header { background: linear-gradient(135deg, #1f2937, #374151); border-color: #374151; }
            .card-title, .course-title, .form-label { color: #f9fafb; }
            .tab-btn { color: #9ca3af; }
            .tab-btn:hover { background: #374151; }
            .course-item, .request-item { background: #374151; border-color: #4b5563; }
            .form-input, .form-select { background: #374151; border-color: #4b5563; color: #f9fafb; }
            .method-btn { background: #374151; border-color: #4b5563; }
            .method-btn.active { background: #065f46; }
        }
    </style>

    @php
        $stats = $this->earningsStats;
        $courses = $this->courseEarnings;
        $requests = $this->payoutRequests;
        $settings = $this->payoutSettings;
        $methods = \App\Models\InstructorPayoutSetting::getPaymentMethods();
        $statuses = \App\Models\PayoutRequest::getStatuses();
    @endphp

    <div class="payouts-container">
        {{-- Header --}}
        <div class="payouts-header">
            <div class="header-content">
                <div class="header-top">
                    <div class="header-info">
                        <div class="header-icon">💰</div>
                        <div class="header-text">
                            <h1>أرباحي وطلبات السحب</h1>
                            <p>إدارة أرباحك وطلب سحب الرصيد</p>
                        </div>
                    </div>
                    
                    <div class="balance-box">
                        <div class="balance-label">الرصيد المتاح للسحب</div>
                        <div class="balance-value">
                            {{ number_format($stats['available_balance'], 2) }}
                            <span>ج.م</span>
                        </div>
                    </div>
                </div>
                
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-value">{{ number_format($stats['total_earnings'], 2) }}</div>
                        <div class="stat-label">إجمالي الأرباح</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value">{{ number_format($stats['pending_payout'], 2) }}</div>
                        <div class="stat-label">قيد السحب</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value">{{ number_format($stats['paid_out'], 2) }}</div>
                        <div class="stat-label">تم سحبه</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value">{{ $stats['commission_rate'] }}%</div>
                        <div class="stat-label">نسبة العمولة</div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Tabs --}}
        <div class="tabs-nav">
            <button class="tab-btn {{ $activeTab === 'overview' ? 'active' : '' }}" wire:click="setTab('overview')">
                <span>📊</span>
                نظرة عامة
            </button>
            <button class="tab-btn {{ $activeTab === 'request' ? 'active' : '' }}" wire:click="setTab('request')">
                <span>💸</span>
                طلب سحب
            </button>
            <button class="tab-btn {{ $activeTab === 'requests' ? 'active' : '' }}" wire:click="setTab('requests')">
                <span>📋</span>
                طلباتي
            </button>
            <button class="tab-btn {{ $activeTab === 'settings' ? 'active' : '' }}" wire:click="setTab('settings')">
                <span>⚙️</span>
                إعدادات الدفع
            </button>
        </div>
        
        {{-- Tab Content --}}
        @if($activeTab === 'overview')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span>📚</span>
                        أرباح الدورات
                    </h3>
                </div>
                <div class="card-body">
                    @if($courses->count() > 0)
                        <div class="course-earnings">
                            @foreach($courses as $course)
                                <div class="course-item">
                                    <div class="course-icon">📖</div>
                                    <div class="course-info">
                                        <p class="course-title">{{ $course['title'] }}</p>
                                        <p class="course-meta">
                                            {{ $course['students'] }} طالب • 
                                            إجمالي المبيعات: {{ number_format($course['total_sales'], 2) }} ج.م •
                                            العمولة: {{ $course['commission_rate'] }}%
                                        </p>
                                    </div>
                                    <div class="course-earnings-value">
                                        <div class="earnings-amount">{{ number_format($course['commission_amount'], 2) }} ج.م</div>
                                        <div class="earnings-label">أرباحك</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">📚</div>
                            <p class="empty-title">لا توجد دورات</p>
                            <p class="empty-text">قم بإنشاء دورات وبيعها لبدء تحقيق الأرباح</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        
        @if($activeTab === 'request')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span>💸</span>
                        طلب سحب جديد
                    </h3>
                </div>
                <div class="card-body">
                    <div style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 1px solid #6ee7b7; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                            <div>
                                <p style="font-size: 14px; color: #065f46; margin: 0;">المبلغ المتاح للسحب</p>
                                <p style="font-size: 32px; font-weight: 800; color: #059669; margin: 8px 0 0 0;">{{ number_format($stats['available_balance'], 2) }} ج.م</p>
                            </div>
                            <div style="text-align: left;">
                                <p style="font-size: 13px; color: #6b7280;">الحد الأدنى للسحب: {{ number_format($stats['minimum_payout'], 2) }} ج.م</p>
                                <p style="font-size: 13px; color: #6b7280;">رسوم المعالجة: {{ $settings->admin_fee_rate }}%</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">ملاحظات (اختياري)</label>
                        <textarea 
                            class="form-input" 
                            wire:model="requestNotes"
                            rows="3"
                            placeholder="أي ملاحظات تريد إضافتها لطلب السحب..."
                        ></textarea>
                    </div>
                    
                    <div style="background: #f9fafb; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: #6b7280;">المبلغ المطلوب:</span>
                            <span style="font-weight: 600;">{{ number_format($stats['available_balance'], 2) }} ج.م</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: #6b7280;">رسوم المعالجة ({{ $settings->admin_fee_rate }}%):</span>
                            <span style="font-weight: 600; color: #dc2626;">- {{ number_format($stats['available_balance'] * $settings->admin_fee_rate / 100, 2) }} ج.م</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                            <span style="font-weight: 700; color: #1f2937;">صافي المبلغ:</span>
                            <span style="font-weight: 800; font-size: 18px; color: #059669;">{{ number_format($stats['available_balance'] * (100 - $settings->admin_fee_rate) / 100, 2) }} ج.م</span>
                        </div>
                    </div>
                    
                    <button 
                        class="payout-btn" 
                        wire:click="requestPayout"
                        {{ $stats['available_balance'] < $stats['minimum_payout'] ? 'disabled' : '' }}
                    >
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        طلب سحب الرصيد
                    </button>
                    
                    @if($stats['available_balance'] < $stats['minimum_payout'])
                        <p style="text-align: center; color: #dc2626; font-size: 13px; margin-top: 12px;">
                            الرصيد المتاح أقل من الحد الأدنى للسحب ({{ number_format($stats['minimum_payout'], 2) }} ج.م)
                        </p>
                    @endif
                </div>
            </div>
        @endif
        
        @if($activeTab === 'requests')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span>📋</span>
                        طلبات السحب السابقة
                    </h3>
                </div>
                <div class="card-body">
                    @if($requests->count() > 0)
                        <div class="requests-list">
                            @foreach($requests as $request)
                                <div class="request-item">
                                    <div class="request-icon {{ $request->status }}">
                                        @switch($request->status)
                                            @case('pending') ⏳ @break
                                            @case('approved') ✅ @break
                                            @case('processing') 🔄 @break
                                            @case('completed') 💚 @break
                                            @case('rejected') ❌ @break
                                        @endswitch
                                    </div>
                                    <div class="request-info">
                                        <p class="request-number">{{ $request->request_number }}</p>
                                        <p class="request-date">{{ $request->requested_at->format('Y/m/d H:i') }}</p>
                                    </div>
                                    <div class="request-amount">
                                        <div class="request-value">{{ number_format($request->net_amount, 2) }} ج.م</div>
                                    </div>
                                    <span class="request-status {{ $request->status }}">
                                        {{ $statuses[$request->status] ?? $request->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">📋</div>
                            <p class="empty-title">لا توجد طلبات</p>
                            <p class="empty-text">لم تقم بإرسال أي طلبات سحب بعد</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        
        @if($activeTab === 'settings')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span>⚙️</span>
                        إعدادات الدفع
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">طريقة الدفع المفضلة</label>
                        <div class="payment-methods">
                            @foreach($methods as $key => $label)
                                <button 
                                    type="button"
                                    class="method-btn {{ $paymentMethod === $key ? 'active' : '' }}"
                                    wire:click="$set('paymentMethod', '{{ $key }}')"
                                >
                                    <div class="method-icon">
                                        @switch($key)
                                            @case('bank_transfer') 🏦 @break
                                            @case('vodafone_cash') 📱 @break
                                            @case('instapay') 💳 @break
                                            @case('paypal') 💵 @break
                                        @endswitch
                                    </div>
                                    <div class="method-name">{{ $label }}</div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    
                    @if($paymentMethod === 'bank_transfer')
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">اسم البنك</label>
                                <input type="text" class="form-input" wire:model="bankName" placeholder="مثال: البنك الأهلي المصري">
                            </div>
                            <div class="form-group">
                                <label class="form-label">اسم صاحب الحساب</label>
                                <input type="text" class="form-input" wire:model="accountHolder" placeholder="الاسم كما في البنك">
                            </div>
                            <div class="form-group">
                                <label class="form-label">رقم الحساب</label>
                                <input type="text" class="form-input" wire:model="accountNumber" placeholder="رقم الحساب البنكي">
                            </div>
                            <div class="form-group">
                                <label class="form-label">IBAN (اختياري)</label>
                                <input type="text" class="form-input" wire:model="iban" placeholder="رقم IBAN">
                            </div>
                        </div>
                    @elseif($paymentMethod === 'vodafone_cash' || $paymentMethod === 'instapay')
                        <div class="form-group">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" class="form-input" wire:model="phoneNumber" placeholder="01xxxxxxxxx">
                        </div>
                    @elseif($paymentMethod === 'paypal')
                        <div class="form-group">
                            <label class="form-label">بريد PayPal</label>
                            <input type="email" class="form-input" wire:model="paypalEmail" placeholder="example@email.com">
                        </div>
                    @endif
                    
                    <button class="save-btn" wire:click="savePaymentSettings">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        حفظ الإعدادات
                    </button>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
