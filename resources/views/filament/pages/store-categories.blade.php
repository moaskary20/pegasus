<x-filament-panels::page>
    <style>
        .store-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3);
            position: relative;
            overflow: hidden;
        }
        .store-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        }
        .store-header-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .store-header-info { display: flex; align-items: center; gap: 16px; }
        .store-header-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 16px; display: flex; align-items: center; justify-content: center; }
        .store-header-text h1 { font-size: 26px; font-weight: 800; margin: 0; }
        .store-header-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        
        .categories-container { display: flex; gap: 24px; min-height: 500px; }
        
        .categories-panel {
            flex: 1;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        .panel-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 18px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .panel-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 10px; }
        .panel-body { padding: 0; }
        
        .category-item {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .category-item:hover { background: #f9fafb; }
        .category-item.active { background: linear-gradient(135deg, #ede9fe, #ddd6fe); border-right: 3px solid #8b5cf6; }
        .category-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        .category-icon img { width: 100%; height: 100%; object-fit: cover; border-radius: 10px; }
        .category-info { flex: 1; min-width: 0; }
        .category-name { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0; }
        .category-meta { font-size: 12px; color: #6b7280; margin: 4px 0 0 0; display: flex; gap: 12px; }
        .category-actions { display: flex; gap: 6px; }
        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .action-btn.edit { background: #f3f4f6; color: #374151; }
        .action-btn.edit:hover { background: #e5e7eb; }
        .action-btn.delete { background: #fef2f2; color: #dc2626; }
        .action-btn.delete:hover { background: #fee2e2; }
        .action-btn.add { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }
        .action-btn.add:hover { transform: translateY(-1px); }
        
        .add-btn {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4); }
        
        .category-form {
            background: linear-gradient(135deg, #f5f3ff, #ede9fe);
            border: 1px solid #c4b5fd;
            border-radius: 14px;
            padding: 24px;
            margin: 20px;
        }
        .form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .form-title { font-size: 16px; font-weight: 700; color: #5b21b6; margin: 0; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: span 2; }
        .form-label { font-size: 13px; font-weight: 600; color: #5b21b6; }
        .form-input, .form-select, .form-textarea {
            padding: 12px 14px;
            border: 2px solid #c4b5fd;
            border-radius: 10px;
            font-size: 14px;
            background: white;
            transition: border-color 0.2s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #8b5cf6; }
        .form-checkbox-group { display: flex; align-items: center; gap: 20px; padding-top: 8px; }
        .form-checkbox { width: 18px; height: 18px; accent-color: #8b5cf6; }
        .form-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn-save {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-save:hover { transform: translateY(-2px); }
        .btn-cancel {
            background: white;
            color: #374151;
            padding: 12px 24px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-icon { width: 80px; height: 80px; background: linear-gradient(135deg, #f3f4f6, #e5e7eb); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .empty-title { font-size: 18px; font-weight: 700; color: #374151; margin: 0 0 8px 0; }
        .empty-text { font-size: 14px; color: #6b7280; margin: 0; }
        
        .success-message { background: linear-gradient(135deg, #dcfce7, #bbf7d0); border: 1px solid #86efac; border-radius: 12px; padding: 14px 20px; margin: 20px; display: flex; align-items: center; gap: 10px; color: #166534; font-weight: 600; }
        .status-badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .status-badge.active { background: #dcfce7; color: #166534; }
        .status-badge.inactive { background: #f3f4f6; color: #6b7280; }
        
        @media (max-width: 1024px) { .categories-container { flex-direction: column; } .form-grid { grid-template-columns: 1fr; } .form-group.full { grid-column: span 1; } }
        @media (prefers-color-scheme: dark) {
            .categories-panel { background: #1f2937; border-color: #374151; }
            .panel-header { background: linear-gradient(135deg, #1f2937, #374151); border-color: #374151; }
            .panel-title, .category-name { color: #f9fafb; }
            .category-item { border-color: #374151; }
            .category-item:hover { background: #374151; }
            .category-item.active { background: linear-gradient(135deg, #4c1d95, #5b21b6); }
            .category-form { background: linear-gradient(135deg, #374151, #4b5563); border-color: #6b7280; }
            .form-title, .form-label { color: #e9d5ff; }
            .form-input, .form-select, .form-textarea { background: #1f2937; border-color: #6b7280; color: #f9fafb; }
        }
    </style>
    
    @php
        $mainCategories = $this->mainCategories;
        $subCategories = $this->subCategories;
        $allCategories = $this->allCategories;
    @endphp
    
    {{-- Header --}}
    <div class="store-header">
        <div class="store-header-content">
            <div class="store-header-info">
                <div class="store-header-icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <div class="store-header-text">
                    <h1>تصنيفات المنتجات</h1>
                    <p>إدارة الأقسام الرئيسية والفرعية للمتجر</p>
                </div>
            </div>
            <button class="add-btn" wire:click="openAddForm">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                إضافة تصنيف
            </button>
        </div>
    </div>
    
    @if(session('success'))
    <div class="success-message">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    
    {{-- Form --}}
    @if($showForm)
    <div class="category-form">
        <div class="form-header">
            <h3 class="form-title">{{ $editingId ? 'تعديل التصنيف' : 'إضافة تصنيف جديد' }}</h3>
            <button wire:click="resetForm" style="background: none; border: none; cursor: pointer; color: #5b21b6;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">اسم التصنيف *</label>
                <input type="text" class="form-input" wire:model="name" placeholder="أدخل اسم التصنيف">
                @error('name') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">التصنيف الأب</label>
                <select class="form-select" wire:model="parentId">
                    <option value="">تصنيف رئيسي</option>
                    @foreach($allCategories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group full">
                <label class="form-label">الوصف</label>
                <textarea class="form-textarea" wire:model="description" rows="3" placeholder="وصف التصنيف..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">الصورة</label>
                <input type="file" class="form-input" wire:model="image" accept="image/*">
                @if($existingImage)
                <img src="{{ asset('storage/' . $existingImage) }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-top: 8px;">
                @endif
            </div>
            <div class="form-group">
                <label class="form-label">ترتيب العرض</label>
                <input type="number" class="form-input" wire:model="sortOrder" min="0">
            </div>
            <div class="form-group full">
                <div class="form-checkbox-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" class="form-checkbox" wire:model="isActive">
                        <span style="font-size: 14px; color: #5b21b6;">نشط</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" class="form-checkbox" wire:model="isFeatured">
                        <span style="font-size: 14px; color: #5b21b6;">مميز</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn-save" wire:click="saveCategory">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                حفظ
            </button>
            <button class="btn-cancel" wire:click="resetForm">إلغاء</button>
        </div>
    </div>
    @endif
    
    <div class="categories-container">
        {{-- Main Categories --}}
        <div class="categories-panel">
            <div class="panel-header">
                <h3 class="panel-title">الأقسام الرئيسية</h3>
                <button class="action-btn add" wire:click="openAddForm">+ إضافة</button>
            </div>
            <div class="panel-body">
                @forelse($mainCategories as $category)
                <div class="category-item {{ $selectedCategoryId === $category->id ? 'active' : '' }}" wire:click="selectCategory({{ $category->id }})">
                    <div class="category-icon">
                        @if($category->image)
                        <img src="{{ asset('storage/' . $category->image) }}" alt="">
                        @else
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        @endif
                    </div>
                    <div class="category-info">
                        <p class="category-name">{{ $category->name }}</p>
                        <p class="category-meta">
                            <span>{{ $category->children_count }} قسم فرعي</span>
                            <span>{{ $category->products_count }} منتج</span>
                        </p>
                    </div>
                    <span class="status-badge {{ $category->is_active ? 'active' : 'inactive' }}">
                        {{ $category->is_active ? 'نشط' : 'غير نشط' }}
                    </span>
                    <div class="category-actions" wire:click.stop>
                        <button class="action-btn edit" wire:click="editCategory({{ $category->id }})">تعديل</button>
                        <button class="action-btn delete" wire:click="deleteCategory({{ $category->id }})" wire:confirm="هل أنت متأكد؟">حذف</button>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="32" height="32" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <p class="empty-title">لا توجد تصنيفات</p>
                    <p class="empty-text">ابدأ بإضافة تصنيف جديد</p>
                </div>
                @endforelse
            </div>
        </div>
        
        {{-- Sub Categories --}}
        <div class="categories-panel">
            <div class="panel-header">
                <h3 class="panel-title">الأقسام الفرعية</h3>
                @if($selectedCategoryId)
                <button class="action-btn add" wire:click="openAddForm({{ $selectedCategoryId }})">+ إضافة فرعي</button>
                @endif
            </div>
            <div class="panel-body">
                @if($selectedCategoryId)
                    @forelse($subCategories as $category)
                    <div class="category-item">
                        <div class="category-icon" style="background: linear-gradient(135deg, #a78bfa, #8b5cf6);">
                            @if($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}" alt="">
                            @else
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            @endif
                        </div>
                        <div class="category-info">
                            <p class="category-name">{{ $category->name }}</p>
                            <p class="category-meta">
                                <span>{{ $category->products_count }} منتج</span>
                            </p>
                        </div>
                        <span class="status-badge {{ $category->is_active ? 'active' : 'inactive' }}">
                            {{ $category->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                        <div class="category-actions">
                            <button class="action-btn edit" wire:click="editCategory({{ $category->id }})">تعديل</button>
                            <button class="action-btn delete" wire:click="deleteCategory({{ $category->id }})" wire:confirm="هل أنت متأكد؟">حذف</button>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <p class="empty-title">لا توجد أقسام فرعية</p>
                        <p class="empty-text">أضف قسم فرعي لهذا التصنيف</p>
                    </div>
                    @endforelse
                @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="32" height="32" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/>
                        </svg>
                    </div>
                    <p class="empty-title">اختر تصنيف</p>
                    <p class="empty-text">اختر تصنيف رئيسي لعرض الأقسام الفرعية</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
