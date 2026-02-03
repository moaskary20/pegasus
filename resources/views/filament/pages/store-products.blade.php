<x-filament-panels::page>
    <style>
        .store-header { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%); border-radius: 20px; padding: 28px 32px; color: white; margin-bottom: 24px; box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3); position: relative; overflow: hidden; }
        .store-header::before { content: ''; position: absolute; top: -50%; right: -30%; width: 80%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%); }
        .store-header-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .store-header-info { display: flex; align-items: center; gap: 16px; }
        .store-header-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 16px; display: flex; align-items: center; justify-content: center; }
        .store-header-text h1 { font-size: 26px; font-weight: 800; margin: 0; }
        .store-header-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 14px; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .stat-icon.purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .stat-icon.green { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .stat-icon.amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-icon.red { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .stat-value { font-size: 24px; font-weight: 800; color: #1f2937; margin: 0; }
        .stat-label { font-size: 12px; color: #6b7280; margin: 4px 0 0 0; }
        
        .filters-bar { background: white; border-radius: 14px; padding: 16px 20px; margin-bottom: 20px; display: flex; gap: 16px; flex-wrap: wrap; align-items: center; border: 1px solid #e5e7eb; }
        .filter-input { padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 13px; min-width: 200px; }
        .filter-select { padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 13px; background: white; }
        
        .products-panel { background: white; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; overflow: hidden; }
        .panel-header { background: linear-gradient(135deg, #f8fafc, #f1f5f9); padding: 18px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .panel-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; }
        
        .products-table { width: 100%; border-collapse: collapse; }
        .products-table th { text-align: right; padding: 14px 16px; font-size: 12px; font-weight: 600; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        .products-table td { padding: 14px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        .products-table tr:hover { background: #fafafa; }
        .product-image { width: 50px; height: 50px; border-radius: 10px; object-fit: cover; background: #f3f4f6; }
        .product-name { font-weight: 600; color: #1f2937; }
        .product-sku { font-size: 11px; color: #9ca3af; margin-top: 2px; }
        .price { font-weight: 700; color: #8b5cf6; }
        .compare-price { font-size: 11px; color: #9ca3af; text-decoration: line-through; }
        .stock-badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .stock-badge.in-stock { background: #dcfce7; color: #166534; }
        .stock-badge.low-stock { background: #fef3c7; color: #92400e; }
        .stock-badge.out-stock { background: #fee2e2; color: #991b1b; }
        .status-badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .status-badge.active { background: #dcfce7; color: #166534; }
        .status-badge.inactive { background: #f3f4f6; color: #6b7280; }
        
        .action-btn { padding: 6px 10px; border: none; border-radius: 6px; font-size: 11px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px; }
        .action-btn.edit { background: #f3f4f6; color: #374151; }
        .action-btn.delete { background: #fef2f2; color: #dc2626; }
        .action-btn.copy { background: #ede9fe; color: #7c3aed; }
        .add-btn { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; padding: 10px 20px; border: none; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4); }
        
        .product-form { background: linear-gradient(135deg, #f5f3ff, #ede9fe); border: 1px solid #c4b5fd; border-radius: 14px; padding: 24px; margin-bottom: 24px; }
        .form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .form-title { font-size: 18px; font-weight: 700; color: #5b21b6; margin: 0; }
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: span 3; }
        .form-group.half { grid-column: span 2; }
        .form-label { font-size: 13px; font-weight: 600; color: #5b21b6; }
        .form-input, .form-select, .form-textarea { padding: 12px 14px; border: 2px solid #c4b5fd; border-radius: 10px; font-size: 14px; background: white; transition: border-color 0.2s; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #8b5cf6; }
        .form-checkbox-group { display: flex; flex-wrap: wrap; gap: 20px; padding-top: 8px; }
        .form-checkbox { width: 18px; height: 18px; accent-color: #8b5cf6; }
        .form-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn-save { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; padding: 12px 24px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .btn-cancel { background: white; color: #374151; padding: 12px 24px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; }
        
        .success-message { background: linear-gradient(135deg, #dcfce7, #bbf7d0); border: 1px solid #86efac; border-radius: 12px; padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: #166534; font-weight: 600; }
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-icon { width: 80px; height: 80px; background: linear-gradient(135deg, #f3f4f6, #e5e7eb); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .empty-title { font-size: 18px; font-weight: 700; color: #374151; margin: 0 0 8px 0; }
        .empty-text { font-size: 14px; color: #6b7280; margin: 0; }
        
        @media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .form-grid { grid-template-columns: 1fr; } .form-group.full, .form-group.half { grid-column: span 1; } }
        @media (prefers-color-scheme: dark) {
            .stat-card, .products-panel, .filters-bar { background: #1f2937; border-color: #374151; }
            .stat-value, .panel-title, .product-name { color: #f9fafb; }
            .panel-header { background: linear-gradient(135deg, #1f2937, #374151); }
            .products-table th { background: #374151; color: #d1d5db; }
            .products-table td { color: #e5e7eb; border-color: #374151; }
            .filter-input, .filter-select { background: #374151; border-color: #4b5563; color: #f9fafb; }
            .product-form { background: linear-gradient(135deg, #374151, #4b5563); border-color: #6b7280; }
            .form-title, .form-label { color: #e9d5ff; }
            .form-input, .form-select, .form-textarea { background: #1f2937; border-color: #6b7280; color: #f9fafb; }
        }
    </style>
    
    @php
        $products = $this->products;
        $categories = $this->categories;
        $stats = $this->stats;
    @endphp
    
    {{-- Header --}}
    <div class="store-header">
        <div class="store-header-content">
            <div class="store-header-info">
                <div class="store-header-icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="store-header-text">
                    <h1>إدارة المنتجات</h1>
                    <p>إضافة وتعديل وحذف منتجات المتجر</p>
                </div>
            </div>
            <button class="add-btn" wire:click="openAddForm">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                إضافة منتج
            </button>
        </div>
    </div>
    
    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon purple"><svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
            <div><p class="stat-value">{{ number_format($stats['total']) }}</p><p class="stat-label">إجمالي المنتجات</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div><p class="stat-value">{{ number_format($stats['active']) }}</p><p class="stat-label">المنتجات النشطة</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
            <div><p class="stat-value">{{ number_format($stats['low_stock']) }}</p><p class="stat-label">مخزون منخفض</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></div>
            <div><p class="stat-value">{{ number_format($stats['out_of_stock']) }}</p><p class="stat-label">غير متوفر</p></div>
        </div>
    </div>
    
    @if(session('success'))
    <div class="success-message">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    
    {{-- Form --}}
    @if($showForm)
    <div class="product-form">
        <div class="form-header">
            <h3 class="form-title">{{ $editingId ? 'تعديل المنتج' : 'إضافة منتج جديد' }}</h3>
            <button wire:click="resetForm" style="background: none; border: none; cursor: pointer; color: #5b21b6;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="form-grid">
            <div class="form-group half">
                <label class="form-label">اسم المنتج *</label>
                <input type="text" class="form-input" wire:model="name" placeholder="أدخل اسم المنتج">
                @error('name') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">رمز المنتج (SKU)</label>
                <input type="text" class="form-input" wire:model="sku" placeholder="PRD-XXXXX">
            </div>
            <div class="form-group full">
                <label class="form-label">وصف مختصر</label>
                <input type="text" class="form-input" wire:model="shortDescription" placeholder="وصف مختصر للمنتج">
            </div>
            <div class="form-group full">
                <label class="form-label">الوصف الكامل</label>
                <textarea class="form-textarea" wire:model="description" rows="4" placeholder="وصف تفصيلي للمنتج..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">السعر *</label>
                <input type="number" class="form-input" wire:model="price" step="0.01" min="0" placeholder="0.00">
                @error('price') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">السعر قبل الخصم</label>
                <input type="number" class="form-input" wire:model="comparePrice" step="0.01" min="0" placeholder="0.00">
            </div>
            <div class="form-group">
                <label class="form-label">سعر التكلفة</label>
                <input type="number" class="form-input" wire:model="costPrice" step="0.01" min="0" placeholder="0.00">
            </div>
            <div class="form-group">
                <label class="form-label">الكمية</label>
                <input type="number" class="form-input" wire:model="quantity" min="0" placeholder="0">
            </div>
            <div class="form-group">
                <label class="form-label">حد التنبيه</label>
                <input type="number" class="form-input" wire:model="lowStockThreshold" min="0" placeholder="5">
            </div>
            <div class="form-group">
                <label class="form-label">التصنيف</label>
                <select class="form-select" wire:model="categoryId">
                    <option value="">بدون تصنيف</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @foreach($cat->children as $sub)
                    <option value="{{ $sub->id }}">-- {{ $sub->name }}</option>
                    @endforeach
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">الصورة الرئيسية</label>
                <input type="file" class="form-input" wire:model="mainImage" accept="image/*">
                @if($existingMainImage)
                <img src="{{ asset('storage/' . $existingMainImage) }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-top: 8px;">
                @endif
            </div>
            <div class="form-group">
                <label class="form-label">الوزن (جرام)</label>
                <input type="number" class="form-input" wire:model="weight" step="0.01" min="0" placeholder="0">
            </div>
            <div class="form-group full">
                <div class="form-checkbox-group">
                    <label style="display: flex; align-items: center; gap: 8px;"><input type="checkbox" class="form-checkbox" wire:model="isActive"><span style="font-size: 14px;">نشط</span></label>
                    <label style="display: flex; align-items: center; gap: 8px;"><input type="checkbox" class="form-checkbox" wire:model="isFeatured"><span style="font-size: 14px;">مميز</span></label>
                    <label style="display: flex; align-items: center; gap: 8px;"><input type="checkbox" class="form-checkbox" wire:model="trackQuantity"><span style="font-size: 14px;">تتبع المخزون</span></label>
                    <label style="display: flex; align-items: center; gap: 8px;"><input type="checkbox" class="form-checkbox" wire:model="requiresShipping"><span style="font-size: 14px;">يتطلب شحن</span></label>
                    <label style="display: flex; align-items: center; gap: 8px;"><input type="checkbox" class="form-checkbox" wire:model="isDigital"><span style="font-size: 14px;">منتج رقمي</span></label>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn-save" wire:click="saveProduct">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                حفظ المنتج
            </button>
            <button class="btn-cancel" wire:click="resetForm">إلغاء</button>
        </div>
    </div>
    @endif
    
    {{-- Filters --}}
    <div class="filters-bar">
        <input type="text" class="filter-input" wire:model.live.debounce.300ms="search" placeholder="البحث بالاسم أو الرمز...">
        <select class="filter-select" wire:model.live="filterCategory">
            <option value="">كل التصنيفات</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
        <select class="filter-select" wire:model.live="filterStatus">
            <option value="all">كل الحالات</option>
            <option value="active">نشط</option>
            <option value="inactive">غير نشط</option>
        </select>
        <select class="filter-select" wire:model.live="filterStock">
            <option value="all">كل المخزون</option>
            <option value="in_stock">متوفر</option>
            <option value="low_stock">مخزون منخفض</option>
            <option value="out_of_stock">غير متوفر</option>
        </select>
    </div>
    
    {{-- Products Table --}}
    <div class="products-panel">
        <div class="panel-header">
            <h3 class="panel-title">قائمة المنتجات ({{ $products->total() }})</h3>
        </div>
        @if($products->count() > 0)
        <table class="products-table">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>التصنيف</th>
                    <th>السعر</th>
                    <th>الكمية</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            @if($product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}" class="product-image" alt="">
                            @else
                            <div class="product-image" style="display: flex; align-items: center; justify-content: center;">
                                <svg width="20" height="20" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            @endif
                            <div>
                                <p class="product-name">{{ Str::limit($product->name, 40) }}</p>
                                <p class="product-sku">{{ $product->sku }}</p>
                            </div>
                        </div>
                    </td>
                    <td>{{ $product->category?->name ?? '-' }}</td>
                    <td>
                        <span class="price">{{ number_format($product->price, 2) }} ج.م</span>
                        @if($product->compare_price)
                        <br><span class="compare-price">{{ number_format($product->compare_price, 2) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($product->track_quantity)
                        <span class="stock-badge {{ $product->stock_status === 'in_stock' ? 'in-stock' : ($product->stock_status === 'low_stock' ? 'low-stock' : 'out-stock') }}">
                            {{ $product->quantity }} {{ $product->stock_status_label }}
                        </span>
                        @else
                        <span class="stock-badge in-stock">متوفر دائماً</span>
                        @endif
                    </td>
                    <td><span class="status-badge {{ $product->is_active ? 'active' : 'inactive' }}">{{ $product->is_active ? 'نشط' : 'غير نشط' }}</span></td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button class="action-btn edit" wire:click="editProduct({{ $product->id }})">تعديل</button>
                            <button class="action-btn copy" wire:click="duplicateProduct({{ $product->id }})">نسخ</button>
                            <button class="action-btn delete" wire:click="deleteProduct({{ $product->id }})" wire:confirm="هل أنت متأكد؟">حذف</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding: 16px;">{{ $products->links() }}</div>
        @else
        <div class="empty-state">
            <div class="empty-icon"><svg width="32" height="32" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
            <p class="empty-title">لا توجد منتجات</p>
            <p class="empty-text">ابدأ بإضافة منتج جديد</p>
        </div>
        @endif
    </div>
</x-filament-panels::page>
