<x-filament-panels::page>
    <style>
        .store-header { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%); border-radius: 20px; padding: 28px 32px; color: white; margin-bottom: 24px; box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3); position: relative; overflow: hidden; }
        .store-header::before { content: ''; position: absolute; top: -50%; right: -30%; width: 80%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%); }
        .store-header-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; }
        .store-header-info { display: flex; align-items: center; gap: 16px; }
        .store-header-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 16px; display: flex; align-items: center; justify-content: center; }
        .store-header-text h1 { font-size: 26px; font-weight: 800; margin: 0; }
        .store-header-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        
        .settings-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
        .settings-tab { background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s; }
        .settings-tab:hover { background: rgba(255,255,255,0.3); }
        .settings-tab.active { background: white; color: #7c3aed; }
        
        .settings-panel { background: white; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; overflow: hidden; }
        .panel-header { background: linear-gradient(135deg, #f8fafc, #f1f5f9); padding: 18px 24px; border-bottom: 1px solid #e5e7eb; }
        .panel-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 10px; }
        .panel-body { padding: 24px; }
        
        .settings-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: span 2; }
        .form-label { font-size: 13px; font-weight: 600; color: #374151; }
        .form-input, .form-select, .form-textarea { padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: border-color 0.2s; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #8b5cf6; }
        .form-hint { font-size: 11px; color: #9ca3af; }
        
        .toggle-group { display: flex; align-items: center; justify-content: space-between; padding: 16px; background: #f9fafb; border-radius: 10px; margin-bottom: 12px; }
        .toggle-info h4 { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0 0 4px 0; }
        .toggle-info p { font-size: 12px; color: #6b7280; margin: 0; }
        .toggle-switch { width: 48px; height: 26px; background: #e5e7eb; border-radius: 13px; position: relative; cursor: pointer; transition: background 0.2s; }
        .toggle-switch.active { background: #8b5cf6; }
        .toggle-switch::after { content: ''; position: absolute; width: 22px; height: 22px; background: white; border-radius: 50%; top: 2px; left: 2px; transition: transform 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .toggle-switch.active::after { transform: translateX(22px); }
        
        .save-btn { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; padding: 14px 32px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; margin-top: 20px; transition: all 0.2s; }
        .save-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4); }
        
        .success-message { background: linear-gradient(135deg, #dcfce7, #bbf7d0); border: 1px solid #86efac; border-radius: 12px; padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: #166534; font-weight: 600; }
        
        .shipping-zones { margin-top: 20px; }
        .zone-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 12px; }
        .zone-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .zone-name { font-weight: 700; color: #1f2937; }
        .zone-actions { display: flex; gap: 6px; }
        .method-item { background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-top: 8px; display: flex; justify-content: space-between; align-items: center; }
        .method-info { display: flex; flex-direction: column; gap: 2px; }
        .method-name { font-weight: 600; color: #374151; font-size: 13px; }
        .method-details { font-size: 11px; color: #6b7280; }
        
        .action-btn { padding: 6px 12px; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .action-btn.primary { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }
        .action-btn.edit { background: #f3f4f6; color: #374151; }
        .action-btn.delete { background: #fef2f2; color: #dc2626; }
        
        .form-modal { background: linear-gradient(135deg, #f5f3ff, #ede9fe); border: 1px solid #c4b5fd; border-radius: 14px; padding: 20px; margin-bottom: 20px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .modal-title { font-size: 16px; font-weight: 700; color: #5b21b6; margin: 0; }
        .modal-actions { display: flex; gap: 10px; margin-top: 16px; }
        .btn-save { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; padding: 10px 20px; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-cancel { background: white; color: #374151; padding: 10px 20px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        
        @media (max-width: 768px) { .settings-grid { grid-template-columns: 1fr; } .form-group.full { grid-column: span 1; } }
        @media (prefers-color-scheme: dark) {
            .settings-panel { background: #1f2937; border-color: #374151; }
            .panel-header { background: linear-gradient(135deg, #1f2937, #374151); }
            .panel-title, .form-label, .toggle-info h4, .zone-name { color: #f9fafb; }
            .form-input, .form-select, .form-textarea { background: #374151; border-color: #4b5563; color: #f9fafb; }
            .toggle-group, .zone-card { background: #374151; border-color: #4b5563; }
            .method-item { background: #1f2937; border-color: #4b5563; }
        }
    </style>
    
    @php
        $shippingZones = $this->shippingZones;
    @endphp
    
    {{-- Header --}}
    <div class="store-header">
        <div class="store-header-content">
            <div class="store-header-info">
                <div class="store-header-icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="store-header-text">
                    <h1>إعدادات المتجر</h1>
                    <p>تكوين إعدادات المتجر والشحن والضرائب</p>
                </div>
            </div>
            <div class="settings-tabs">
                <button class="settings-tab {{ $activeTab === 'general' ? 'active' : '' }}" wire:click="setTab('general')">عام</button>
                <button class="settings-tab {{ $activeTab === 'shipping' ? 'active' : '' }}" wire:click="setTab('shipping')">الشحن</button>
                <button class="settings-tab {{ $activeTab === 'tax' ? 'active' : '' }}" wire:click="setTab('tax')">الضرائب</button>
                <button class="settings-tab {{ $activeTab === 'orders' ? 'active' : '' }}" wire:click="setTab('orders')">الطلبات</button>
                <button class="settings-tab {{ $activeTab === 'inventory' ? 'active' : '' }}" wire:click="setTab('inventory')">المخزون</button>
            </div>
        </div>
    </div>
    
    @if(session('success'))
    <div class="success-message">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    
    @if($activeTab === 'general')
    <div class="settings-panel">
        <div class="panel-header"><h3 class="panel-title">الإعدادات العامة</h3></div>
        <div class="panel-body">
            <div class="settings-grid">
                <div class="form-group"><label class="form-label">اسم المتجر</label><input type="text" class="form-input" wire:model="storeName"></div>
                <div class="form-group"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-input" wire:model="storeEmail"></div>
                <div class="form-group full"><label class="form-label">وصف المتجر</label><textarea class="form-textarea" wire:model="storeDescription" rows="2"></textarea></div>
                <div class="form-group"><label class="form-label">رقم الهاتف</label><input type="text" class="form-input" wire:model="storePhone"></div>
                <div class="form-group"><label class="form-label">العنوان</label><input type="text" class="form-input" wire:model="storeAddress"></div>
                <div class="form-group"><label class="form-label">العملة</label><input type="text" class="form-input" wire:model="currency"></div>
                <div class="form-group"><label class="form-label">رمز العملة</label><input type="text" class="form-input" wire:model="currencySymbol"></div>
            </div>
            <button class="save-btn" wire:click="saveGeneralSettings"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>حفظ الإعدادات</button>
        </div>
    </div>
    @endif
    
    @if($activeTab === 'shipping')
    @php
        $governorates = $this->governorates;
        $regions = $this->regions;
    @endphp
    <style>
        .shipping-mode-selector { display: flex; gap: 16px; margin-bottom: 24px; }
        .shipping-mode-card { flex: 1; background: white; border: 2px solid #e5e7eb; border-radius: 16px; padding: 20px; cursor: pointer; transition: all 0.3s; text-align: center; }
        .shipping-mode-card:hover { border-color: #c4b5fd; transform: translateY(-2px); }
        .shipping-mode-card.active { border-color: #8b5cf6; background: linear-gradient(135deg, #f5f3ff, #ede9fe); }
        .shipping-mode-icon { width: 56px; height: 56px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; }
        .shipping-mode-card.active .shipping-mode-icon { box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4); }
        .shipping-mode-title { font-size: 15px; font-weight: 700; color: #1f2937; margin: 0 0 6px 0; }
        .shipping-mode-desc { font-size: 12px; color: #6b7280; margin: 0; }
        
        .shipping-section { margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section-title { font-size: 18px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 10px; }
        .section-title svg { width: 24px; height: 24px; color: #8b5cf6; }
        
        .gov-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
        .gov-card { background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px; transition: all 0.2s; }
        .gov-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-2px); }
        .gov-card.inactive { opacity: 0.6; background: #f9fafb; }
        .gov-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
        .gov-info { flex: 1; }
        .gov-name { font-size: 15px; font-weight: 700; color: #1f2937; margin: 0; }
        .gov-region-badge { font-size: 10px; color: #6b7280; background: #f3f4f6; padding: 3px 8px; border-radius: 4px; display: inline-block; margin-top: 4px; }
        .gov-price-display { text-align: left; }
        .gov-price-value { font-size: 20px; font-weight: 800; color: #8b5cf6; }
        .gov-price-unit { font-size: 11px; color: #6b7280; }
        .gov-details { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
        .gov-detail-item { font-size: 11px; color: #6b7280; display: flex; align-items: center; gap: 4px; }
        .gov-detail-item svg { width: 14px; height: 14px; }
        .gov-actions { display: flex; gap: 8px; justify-content: flex-end; }
        .gov-action-btn { padding: 6px 12px; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .gov-action-btn.edit { background: #f3f4f6; color: #374151; }
        .gov-action-btn.edit:hover { background: #e5e7eb; }
        .gov-action-btn.toggle { background: #fee2e2; color: #dc2626; }
        .gov-action-btn.toggle.active { background: #dcfce7; color: #166534; }
        
        .gov-filter-bar { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; margin-bottom: 20px; padding: 16px; background: #f9fafb; border-radius: 12px; }
        .gov-filter-select { padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; background: white; min-width: 160px; }
        .gov-bulk-section { margin-right: auto; display: flex; gap: 8px; align-items: center; }
        .gov-bulk-input { padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 12px; width: 100px; background: white; }
        .gov-bulk-btn { padding: 8px 14px; border: none; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }
        
        .gov-edit-modal { background: linear-gradient(135deg, #f5f3ff, #ede9fe); border: 2px solid #c4b5fd; border-radius: 16px; padding: 24px; margin-bottom: 24px; }
        .gov-edit-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .gov-edit-title { font-size: 18px; font-weight: 700; color: #5b21b6; margin: 0; display: flex; align-items: center; gap: 10px; }
        .gov-edit-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .gov-edit-actions { display: flex; gap: 10px; margin-top: 20px; }
        
        @media (max-width: 768px) { 
            .shipping-mode-selector { flex-direction: column; } 
            .gov-grid { grid-template-columns: 1fr; } 
            .gov-edit-grid { grid-template-columns: 1fr; }
        }
        @media (prefers-color-scheme: dark) {
            .shipping-mode-card { background: #1f2937; border-color: #374151; }
            .shipping-mode-card.active { border-color: #8b5cf6; background: linear-gradient(135deg, #4c1d95, #5b21b6); }
            .shipping-mode-title, .section-title, .gov-name { color: #f9fafb; }
            .gov-card { background: #1f2937; border-color: #374151; }
            .gov-filter-bar { background: #374151; }
            .gov-filter-select, .gov-bulk-input { background: #1f2937; border-color: #4b5563; color: #f9fafb; }
        }
    </style>
    
    <div class="settings-panel">
        <div class="panel-header"><h3 class="panel-title">إعدادات الشحن</h3></div>
        <div class="panel-body">
            {{-- Enable Shipping Toggle --}}
            <div class="toggle-group">
                <div class="toggle-info"><h4>تفعيل الشحن</h4><p>تفعيل خدمة الشحن للطلبات</p></div>
                <div class="toggle-switch {{ $enableShipping ? 'active' : '' }}" wire:click="$toggle('enableShipping')"></div>
            </div>
            
            {{-- Shipping Mode Selector --}}
            <h4 style="font-size: 14px; font-weight: 600; color: #374151; margin: 20px 0 12px;">طريقة حساب الشحن</h4>
            <div class="shipping-mode-selector">
                <div class="shipping-mode-card {{ $shippingCalculation === 'flat' ? 'active' : '' }}" wire:click="$set('shippingCalculation', 'flat')">
                    <div class="shipping-mode-icon">
                        <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="shipping-mode-title">سعر ثابت</p>
                    <p class="shipping-mode-desc">تكلفة شحن موحدة لجميع الطلبات</p>
                </div>
                <div class="shipping-mode-card {{ $shippingCalculation === 'governorate' ? 'active' : '' }}" wire:click="$set('shippingCalculation', 'governorate')">
                    <div class="shipping-mode-icon">
                        <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <p class="shipping-mode-title">حسب المحافظة</p>
                    <p class="shipping-mode-desc">سعر شحن مختلف لكل محافظة مصرية</p>
                </div>
                <div class="shipping-mode-card {{ $shippingCalculation === 'per_weight' ? 'active' : '' }}" wire:click="$set('shippingCalculation', 'per_weight')">
                    <div class="shipping-mode-icon">
                        <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    </div>
                    <p class="shipping-mode-title">حسب الوزن</p>
                    <p class="shipping-mode-desc">تكلفة الشحن بناءً على وزن المنتجات</p>
                </div>
            </div>
            
            {{-- Flat Rate Settings --}}
            @if($shippingCalculation === 'flat')
            <div class="shipping-section">
                <div class="settings-grid">
                    <div class="form-group"><label class="form-label">تكلفة الشحن الثابتة (ج.م)</label><input type="number" class="form-input" wire:model="defaultShippingCost" step="0.01" min="0"></div>
                    <div class="form-group"><label class="form-label">الحد الأدنى للشحن المجاني (ج.م)</label><input type="number" class="form-input" wire:model="freeShippingThreshold" step="0.01" min="0" placeholder="اتركه فارغاً لتعطيل"></div>
                </div>
            </div>
            @endif
            
            {{-- Per Weight Settings --}}
            @if($shippingCalculation === 'per_weight')
            <div class="shipping-section">
                <div class="settings-grid">
                    <div class="form-group"><label class="form-label">تكلفة الكيلو الواحد (ج.م)</label><input type="number" class="form-input" wire:model="defaultShippingCost" step="0.01" min="0"></div>
                    <div class="form-group"><label class="form-label">الحد الأدنى للشحن المجاني (ج.م)</label><input type="number" class="form-input" wire:model="freeShippingThreshold" step="0.01" min="0"></div>
                </div>
            </div>
            @endif
            
            {{-- Governorate Based Shipping --}}
            @if($shippingCalculation === 'governorate')
            <div class="shipping-section">
                <div class="section-header">
                    <h3 class="section-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        أسعار الشحن حسب المحافظة
                    </h3>
                </div>
                
                {{-- Filter Bar --}}
                <div class="gov-filter-bar">
                    <select class="gov-filter-select" wire:model.live="filterRegion">
                        <option value="all">🇪🇬 كل المحافظات ({{ $governorates->count() }})</option>
                        @foreach($regions as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                        @endforeach
                    </select>
                    
                    <div class="gov-bulk-section">
                        <span style="font-size: 12px; color: #6b7280;">تحديث الكل:</span>
                        <input type="number" class="gov-bulk-input" placeholder="السعر" id="bulkCostInput" step="0.01" min="0">
                        <button class="gov-bulk-btn" onclick="document.getElementById('bulkCostInput').value && @this.call('updateAllGovernorates', 'shipping_cost', document.getElementById('bulkCostInput').value)">تطبيق</button>
                    </div>
                </div>
                
                {{-- Edit Modal --}}
                @if($showGovernorateForm && $editingGovernorateId)
                @php $editingGov = \App\Models\GovernorateShippingRate::find($editingGovernorateId); @endphp
                <div class="gov-edit-modal">
                    <div class="gov-edit-header">
                        <h4 class="gov-edit-title">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            تعديل: {{ $editingGov?->name_ar }}
                        </h4>
                        <button wire:click="resetGovernorateForm" style="background: none; border: none; cursor: pointer; color: #5b21b6; padding: 8px;">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="gov-edit-grid">
                        <div class="form-group">
                            <label class="form-label">تكلفة الشحن (ج.م) *</label>
                            <input type="number" class="form-input" wire:model="govShippingCost" step="0.01" min="0">
                            @error('govShippingCost') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">الحد الأدنى للشحن المجاني</label>
                            <input type="number" class="form-input" wire:model="govFreeThreshold" step="0.01" min="0" placeholder="اتركه فارغاً لتعطيل">
                        </div>
                        <div class="form-group">
                            <label class="form-label">أيام التوصيل (من - إلى)</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="number" class="form-input" wire:model="govEstDaysMin" min="1" placeholder="من" style="flex: 1;">
                                <input type="number" class="form-input" wire:model="govEstDaysMax" min="1" placeholder="إلى" style="flex: 1;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">الحالة</label>
                            <div style="display: flex; gap: 16px; padding-top: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" style="width: 18px; height: 18px; accent-color: #8b5cf6;" wire:model="govIsActive">
                                    <span style="font-size: 13px;">نشط</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" style="width: 18px; height: 18px; accent-color: #8b5cf6;" wire:model="govCashOnDelivery">
                                    <span style="font-size: 13px;">الدفع عند الاستلام</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">ملاحظات</label>
                            <input type="text" class="form-input" wire:model="govNotes" placeholder="ملاحظات إضافية...">
                        </div>
                    </div>
                    <div class="gov-edit-actions">
                        <button class="save-btn" wire:click="saveGovernorate" style="margin-top: 0;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            حفظ التغييرات
                        </button>
                        <button class="btn-cancel" wire:click="resetGovernorateForm" style="padding: 12px 24px; background: white; border: 1px solid #e5e7eb; border-radius: 10px; cursor: pointer; font-weight: 600;">إلغاء</button>
                    </div>
                </div>
                @endif
                
                {{-- Governorates Grid --}}
                <div class="gov-grid">
                    @foreach($governorates as $gov)
                    <div class="gov-card {{ !$gov->is_active ? 'inactive' : '' }}">
                        <div class="gov-card-header">
                            <div class="gov-info">
                                <p class="gov-name">{{ $gov->name_ar }}</p>
                                <span class="gov-region-badge">{{ $gov->region }}</span>
                            </div>
                            <div class="gov-price-display">
                                <span class="gov-price-value">{{ number_format($gov->shipping_cost, 0) }}</span>
                                <span class="gov-price-unit">ج.م</span>
                            </div>
                        </div>
                        <div class="gov-details">
                            <span class="gov-detail-item">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $gov->estimated_delivery ?? '---' }}
                            </span>
                            @if($gov->free_shipping_threshold)
                            <span class="gov-detail-item" style="color: #22c55e;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                مجاني فوق {{ number_format($gov->free_shipping_threshold, 0) }} ج.م
                            </span>
                            @endif
                            <span class="gov-detail-item" style="color: {{ $gov->cash_on_delivery ? '#3b82f6' : '#9ca3af' }};">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                {{ $gov->cash_on_delivery ? 'COD متاح' : 'COD غير متاح' }}
                            </span>
                        </div>
                        <div class="gov-actions">
                            <button class="gov-action-btn toggle {{ $gov->is_active ? 'active' : '' }}" wire:click="toggleGovernorateStatus({{ $gov->id }})">
                                {{ $gov->is_active ? '✓ نشط' : '✗ متوقف' }}
                            </button>
                            <button class="gov-action-btn edit" wire:click="editGovernorate({{ $gov->id }})">تعديل</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            <button class="save-btn" wire:click="saveShippingSettings" style="margin-top: 24px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                حفظ إعدادات الشحن
            </button>
        </div>
    </div>
    @endif
    
    @if($activeTab === 'tax')
    <div class="settings-panel">
        <div class="panel-header"><h3 class="panel-title">إعدادات الضرائب</h3></div>
        <div class="panel-body">
            <div class="toggle-group">
                <div class="toggle-info"><h4>تفعيل الضرائب</h4><p>تطبيق الضرائب على المنتجات</p></div>
                <div class="toggle-switch {{ $enableTax ? 'active' : '' }}" wire:click="$toggle('enableTax')"></div>
            </div>
            <div class="settings-grid">
                <div class="form-group"><label class="form-label">نسبة الضريبة (%)</label><input type="number" class="form-input" wire:model="taxRate" step="0.1"></div>
            </div>
            <div class="toggle-group">
                <div class="toggle-info"><h4>الضريبة مشمولة في السعر</h4><p>أسعار المنتجات تشمل الضريبة</p></div>
                <div class="toggle-switch {{ $taxIncludedInPrice ? 'active' : '' }}" wire:click="$toggle('taxIncludedInPrice')"></div>
            </div>
            <button class="save-btn" wire:click="saveTaxSettings"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>حفظ إعدادات الضرائب</button>
        </div>
    </div>
    @endif
    
    @if($activeTab === 'orders')
    <div class="settings-panel">
        <div class="panel-header"><h3 class="panel-title">إعدادات الطلبات</h3></div>
        <div class="panel-body">
            <div class="settings-grid">
                <div class="form-group"><label class="form-label">بادئة رقم الطلب</label><input type="text" class="form-input" wire:model="orderPrefix"></div>
                <div class="form-group"><label class="form-label">الحد الأدنى للطلب</label><input type="number" class="form-input" wire:model="minOrderAmount" step="0.01"></div>
            </div>
            <div class="toggle-group">
                <div class="toggle-info"><h4>تأكيد الطلبات تلقائياً</h4><p>تأكيد الطلبات بدون مراجعة يدوية</p></div>
                <div class="toggle-switch {{ $autoConfirmOrders ? 'active' : '' }}" wire:click="$toggle('autoConfirmOrders')"></div>
            </div>
            <div class="toggle-group">
                <div class="toggle-info"><h4>السماح بالشراء كزائر</h4><p>السماح للزوار بالشراء بدون تسجيل</p></div>
                <div class="toggle-switch {{ $allowGuestCheckout ? 'active' : '' }}" wire:click="$toggle('allowGuestCheckout')"></div>
            </div>
            <button class="save-btn" wire:click="saveOrderSettings"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>حفظ إعدادات الطلبات</button>
        </div>
    </div>
    @endif
    
    @if($activeTab === 'inventory')
    <div class="settings-panel">
        <div class="panel-header"><h3 class="panel-title">إعدادات المخزون</h3></div>
        <div class="panel-body">
            <div class="toggle-group">
                <div class="toggle-info"><h4>تتبع المخزون</h4><p>تتبع كميات المنتجات تلقائياً</p></div>
                <div class="toggle-switch {{ $trackInventory ? 'active' : '' }}" wire:click="$toggle('trackInventory')"></div>
            </div>
            <div class="toggle-group">
                <div class="toggle-info"><h4>إشعار انخفاض المخزون</h4><p>إرسال إشعار عند انخفاض المخزون</p></div>
                <div class="toggle-switch {{ $lowStockNotification ? 'active' : '' }}" wire:click="$toggle('lowStockNotification')"></div>
            </div>
            <div class="toggle-group">
                <div class="toggle-info"><h4>إظهار المنتجات غير المتوفرة</h4><p>عرض المنتجات غير المتوفرة للعملاء</p></div>
                <div class="toggle-switch {{ $outOfStockVisibility ? 'active' : '' }}" wire:click="$toggle('outOfStockVisibility')"></div>
            </div>
            <div class="toggle-group">
                <div class="toggle-info"><h4>السماح بالطلب المسبق</h4><p>السماح بالطلب للمنتجات غير المتوفرة</p></div>
                <div class="toggle-switch {{ $allowBackorders ? 'active' : '' }}" wire:click="$toggle('allowBackorders')"></div>
            </div>
            <button class="save-btn" wire:click="saveInventorySettings"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>حفظ إعدادات المخزون</button>
        </div>
    </div>
    @endif
</x-filament-panels::page>
