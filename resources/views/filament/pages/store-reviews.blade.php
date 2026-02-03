<x-filament-panels::page>
    <style>
        .store-header { background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%); border-radius: 20px; padding: 28px 32px; color: white; margin-bottom: 24px; box-shadow: 0 10px 40px rgba(245, 158, 11, 0.3); position: relative; overflow: hidden; }
        .store-header::before { content: ''; position: absolute; top: -50%; right: -30%; width: 80%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%); }
        .store-header-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .store-header-info { display: flex; align-items: center; gap: 16px; }
        .store-header-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 16px; display: flex; align-items: center; justify-content: center; }
        .store-header-text h1 { font-size: 26px; font-weight: 800; margin: 0; }
        .store-header-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
        .stat-icon.amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-icon.green { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .stat-icon.yellow { background: linear-gradient(135deg, #eab308, #ca8a04); }
        .stat-icon.red { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .stat-value { font-size: 28px; font-weight: 800; color: #1f2937; margin: 0; }
        .stat-label { font-size: 12px; color: #6b7280; margin: 4px 0 0 0; }
        
        .rating-distribution { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; margin-bottom: 24px; }
        .dist-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0 0 16px 0; }
        .dist-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
        .dist-stars { font-size: 13px; font-weight: 600; color: #f59e0b; min-width: 80px; }
        .dist-bar { flex: 1; height: 10px; background: #f3f4f6; border-radius: 5px; overflow: hidden; }
        .dist-bar-fill { height: 100%; background: linear-gradient(90deg, #f59e0b, #fbbf24); border-radius: 5px; transition: width 0.3s; }
        .dist-count { font-size: 12px; color: #6b7280; min-width: 50px; text-align: left; }
        
        .reviews-container { display: flex; gap: 24px; min-height: 500px; }
        .reviews-list { flex: 1; }
        .review-detail { width: 400px; flex-shrink: 0; }
        
        .filters-bar { background: white; border-radius: 14px; padding: 16px 20px; margin-bottom: 20px; display: flex; gap: 16px; flex-wrap: wrap; align-items: center; border: 1px solid #e5e7eb; }
        .filter-input { padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 13px; min-width: 200px; }
        .filter-select { padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 13px; background: white; }
        
        .panel { background: white; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; overflow: hidden; }
        .panel-header { background: linear-gradient(135deg, #f8fafc, #f1f5f9); padding: 18px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .panel-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; }
        
        .review-item { padding: 16px 20px; border-bottom: 1px solid #f3f4f6; cursor: pointer; transition: all 0.2s; }
        .review-item:hover { background: #f9fafb; }
        .review-item.active { background: linear-gradient(135deg, #fef3c7, #fde68a); border-right: 3px solid #f59e0b; }
        .review-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
        .review-product { font-weight: 700; color: #1f2937; font-size: 14px; }
        .review-user { font-size: 12px; color: #6b7280; }
        .review-rating { display: flex; gap: 2px; }
        .review-star { color: #f59e0b; font-size: 14px; }
        .review-star.empty { color: #e5e7eb; }
        .review-text { font-size: 13px; color: #374151; line-height: 1.5; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .review-footer { display: flex; justify-content: space-between; align-items: center; }
        .review-date { font-size: 11px; color: #9ca3af; }
        
        .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .status-badge.approved { background: #dcfce7; color: #166534; }
        .status-badge.pending { background: #fef3c7; color: #92400e; }
        .status-badge.verified { background: #dbeafe; color: #1e40af; }
        
        .detail-section { padding: 20px; border-bottom: 1px solid #f3f4f6; }
        .detail-section:last-child { border-bottom: none; }
        .detail-title { font-size: 14px; font-weight: 700; color: #1f2937; margin: 0 0 12px 0; }
        .detail-rating { display: flex; gap: 4px; margin-bottom: 12px; }
        .detail-star { color: #f59e0b; font-size: 24px; }
        .detail-star.empty { color: #e5e7eb; }
        .detail-comment { font-size: 14px; color: #374151; line-height: 1.7; background: #f9fafb; padding: 16px; border-radius: 10px; }
        .detail-meta { display: flex; flex-direction: column; gap: 8px; margin-top: 16px; }
        .meta-item { display: flex; justify-content: space-between; font-size: 13px; }
        .meta-label { color: #6b7280; }
        .meta-value { color: #1f2937; font-weight: 500; }
        
        .action-buttons { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
        .action-btn { padding: 10px 16px; border: none; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px; }
        .action-btn.approve { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; }
        .action-btn.reject { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
        .action-btn.edit { background: #f3f4f6; color: #374151; }
        .action-btn.delete { background: #fee2e2; color: #dc2626; }
        .action-btn:hover { transform: translateY(-1px); }
        
        .add-btn { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 10px 20px; border: none; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4); }
        .bulk-btn { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; padding: 8px 16px; border: none; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; }
        
        .review-form { background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1px solid #fcd34d; border-radius: 14px; padding: 24px; margin-bottom: 24px; }
        .form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .form-title { font-size: 18px; font-weight: 700; color: #92400e; margin: 0; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: span 2; }
        .form-label { font-size: 13px; font-weight: 600; color: #92400e; }
        .form-input, .form-select, .form-textarea { padding: 12px 14px; border: 2px solid #fcd34d; border-radius: 10px; font-size: 14px; background: white; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #f59e0b; }
        .rating-selector { display: flex; gap: 8px; padding-top: 8px; }
        .rating-star-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #e5e7eb; transition: all 0.2s; }
        .rating-star-btn.active { color: #f59e0b; }
        .rating-star-btn:hover { transform: scale(1.2); }
        .form-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn-save { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 12px 24px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-cancel { background: white; color: #374151; padding: 12px 24px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; }
        
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-icon { width: 80px; height: 80px; background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .empty-title { font-size: 18px; font-weight: 700; color: #374151; margin: 0 0 8px 0; }
        .empty-text { font-size: 14px; color: #6b7280; margin: 0; }
        .success-message { background: linear-gradient(135deg, #dcfce7, #bbf7d0); border: 1px solid #86efac; border-radius: 12px; padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: #166534; font-weight: 600; }
        
        @media (max-width: 1200px) { .reviews-container { flex-direction: column; } .review-detail { width: 100%; } .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (prefers-color-scheme: dark) {
            .stat-card, .panel, .filters-bar, .rating-distribution { background: #1f2937; border-color: #374151; }
            .stat-value, .panel-title, .review-product, .detail-title, .dist-title { color: #f9fafb; }
            .panel-header { background: linear-gradient(135deg, #1f2937, #374151); }
            .review-item { border-color: #374151; }
            .review-item:hover { background: #374151; }
            .review-item.active { background: linear-gradient(135deg, #78350f, #92400e); }
            .filter-input, .filter-select { background: #374151; border-color: #4b5563; color: #f9fafb; }
            .detail-comment { background: #374151; color: #e5e7eb; }
            .review-form { background: linear-gradient(135deg, #374151, #4b5563); border-color: #6b7280; }
            .form-title, .form-label { color: #fcd34d; }
            .form-input, .form-select, .form-textarea { background: #1f2937; border-color: #6b7280; color: #f9fafb; }
        }
    </style>
    
    @php
        $reviews = $this->reviews;
        $products = $this->products;
        $users = $this->users;
        $stats = $this->stats;
        $ratingDistribution = $this->ratingDistribution;
        $selectedReview = $this->selectedReview;
    @endphp
    
    {{-- Header --}}
    <div class="store-header">
        <div class="store-header-content">
            <div class="store-header-info">
                <div class="store-header-icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div class="store-header-text">
                    <h1>تقييمات المنتجات</h1>
                    <p>إدارة ومراجعة تقييمات العملاء</p>
                </div>
            </div>
            <div style="display: flex; gap: 12px;">
                @if($stats['pending'] > 0)
                <button class="bulk-btn" wire:click="bulkApprove" wire:confirm="هل تريد الموافقة على جميع التقييمات المعلقة؟">
                    ✓ الموافقة على الكل ({{ $stats['pending'] }})
                </button>
                @endif
                <button class="add-btn" wire:click="openAddForm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة تقييم
                </button>
            </div>
        </div>
    </div>
    
    @if(session('success'))
    <div class="success-message">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    
    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon amber"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg></div>
            <p class="stat-value">{{ number_format($stats['total']) }}</p>
            <p class="stat-label">إجمالي التقييمات</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <p class="stat-value">{{ number_format($stats['approved']) }}</p>
            <p class="stat-label">تقييمات معتمدة</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <p class="stat-value">{{ number_format($stats['pending']) }}</p>
            <p class="stat-label">في انتظار المراجعة</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><svg width="20" height="20" fill="white" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></div>
            <p class="stat-value">{{ $stats['average'] }}</p>
            <p class="stat-label">متوسط التقييم</p>
        </div>
    </div>
    
    {{-- Rating Distribution --}}
    <div class="rating-distribution">
        <h4 class="dist-title">توزيع التقييمات</h4>
        @foreach($ratingDistribution as $stars => $data)
        <div class="dist-row">
            <span class="dist-stars">{{ $stars }} ★</span>
            <div class="dist-bar">
                <div class="dist-bar-fill" style="width: {{ $data['percentage'] }}%;"></div>
            </div>
            <span class="dist-count">{{ $data['count'] }} ({{ $data['percentage'] }}%)</span>
        </div>
        @endforeach
    </div>
    
    {{-- Form --}}
    @if($showForm)
    <div class="review-form">
        <div class="form-header">
            <h3 class="form-title">{{ $editingId ? 'تعديل التقييم' : 'إضافة تقييم جديد' }}</h3>
            <button wire:click="resetForm" style="background: none; border: none; cursor: pointer; color: #92400e;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">المنتج *</label>
                <select class="form-select" wire:model="productId">
                    <option value="">اختر المنتج</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
                @error('productId') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">العميل *</label>
                <select class="form-select" wire:model="userId">
                    <option value="">اختر العميل</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('userId') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group full">
                <label class="form-label">التقييم *</label>
                <div class="rating-selector">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button" class="rating-star-btn {{ $rating >= $i ? 'active' : '' }}" wire:click="$set('rating', {{ $i }})">★</button>
                    @endfor
                </div>
            </div>
            <div class="form-group full">
                <label class="form-label">عنوان التقييم</label>
                <input type="text" class="form-input" wire:model="reviewTitle" placeholder="عنوان مختصر للتقييم">
            </div>
            <div class="form-group full">
                <label class="form-label">التعليق</label>
                <textarea class="form-textarea" wire:model="comment" rows="3" placeholder="تفاصيل التقييم..."></textarea>
            </div>
            <div class="form-group full">
                <div style="display: flex; gap: 24px; padding-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" style="width: 18px; height: 18px; accent-color: #f59e0b;" wire:model="isApproved">
                        <span style="font-size: 14px;">معتمد</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" style="width: 18px; height: 18px; accent-color: #f59e0b;" wire:model="isVerifiedPurchase">
                        <span style="font-size: 14px;">شراء موثق</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn-save" wire:click="saveReview">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                حفظ التقييم
            </button>
            <button class="btn-cancel" wire:click="resetForm">إلغاء</button>
        </div>
    </div>
    @endif
    
    {{-- Filters --}}
    <div class="filters-bar">
        <input type="text" class="filter-input" wire:model.live.debounce.300ms="search" placeholder="البحث في التقييمات...">
        <select class="filter-select" wire:model.live="filterStatus">
            <option value="all">كل الحالات</option>
            <option value="approved">معتمد</option>
            <option value="pending">في الانتظار</option>
        </select>
        <select class="filter-select" wire:model.live="filterRating">
            <option value="all">كل التقييمات</option>
            <option value="5">5 نجوم</option>
            <option value="4">4 نجوم</option>
            <option value="3">3 نجوم</option>
            <option value="2">نجمتان</option>
            <option value="1">نجمة واحدة</option>
        </select>
        <select class="filter-select" wire:model.live="filterProduct">
            <option value="">كل المنتجات</option>
            @foreach($products as $product)
            <option value="{{ $product->id }}">{{ Str::limit($product->name, 30) }}</option>
            @endforeach
        </select>
    </div>
    
    <div class="reviews-container">
        {{-- Reviews List --}}
        <div class="reviews-list">
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title">التقييمات ({{ $reviews->total() }})</h3>
                </div>
                @forelse($reviews as $review)
                <div class="review-item {{ $selectedReviewId === $review->id ? 'active' : '' }}" wire:click="selectReview({{ $review->id }})">
                    <div class="review-header">
                        <div>
                            <p class="review-product">{{ Str::limit($review->product?->name ?? 'منتج محذوف', 30) }}</p>
                            <p class="review-user">{{ $review->user?->name ?? 'مستخدم محذوف' }}</p>
                        </div>
                        <div class="review-rating">
                            @for($i = 1; $i <= 5; $i++)
                            <span class="review-star {{ $i <= $review->rating ? '' : 'empty' }}">★</span>
                            @endfor
                        </div>
                    </div>
                    @if($review->comment)
                    <p class="review-text">{{ Str::limit($review->comment, 100) }}</p>
                    @endif
                    <div class="review-footer">
                        <span class="review-date">{{ $review->created_at->format('Y/m/d') }}</span>
                        <div style="display: flex; gap: 6px;">
                            <span class="status-badge {{ $review->is_approved ? 'approved' : 'pending' }}">
                                {{ $review->is_approved ? 'معتمد' : 'في الانتظار' }}
                            </span>
                            @if($review->is_verified_purchase)
                            <span class="status-badge verified">شراء موثق</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-icon"><svg width="32" height="32" fill="none" stroke="#f59e0b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg></div>
                    <p class="empty-title">لا توجد تقييمات</p>
                    <p class="empty-text">لم يتم إضافة أي تقييمات بعد</p>
                </div>
                @endforelse
                @if($reviews->hasPages())
                <div style="padding: 16px;">{{ $reviews->links() }}</div>
                @endif
            </div>
        </div>
        
        {{-- Review Detail --}}
        <div class="review-detail">
            <div class="panel">
                @if($selectedReview)
                <div class="panel-header">
                    <h3 class="panel-title">تفاصيل التقييم</h3>
                </div>
                
                <div class="detail-section">
                    <h4 class="detail-title">المنتج</h4>
                    <p style="font-weight: 600; color: #1f2937;">{{ $selectedReview->product?->name ?? 'منتج محذوف' }}</p>
                </div>
                
                <div class="detail-section">
                    <h4 class="detail-title">التقييم</h4>
                    <div class="detail-rating">
                        @for($i = 1; $i <= 5; $i++)
                        <span class="detail-star {{ $i <= $selectedReview->rating ? '' : 'empty' }}">★</span>
                        @endfor
                    </div>
                    @if($selectedReview->title)
                    <p style="font-weight: 600; margin-bottom: 8px;">{{ $selectedReview->title }}</p>
                    @endif
                    @if($selectedReview->comment)
                    <div class="detail-comment">{{ $selectedReview->comment }}</div>
                    @endif
                </div>
                
                <div class="detail-section">
                    <h4 class="detail-title">معلومات المُقيّم</h4>
                    <div class="detail-meta">
                        <div class="meta-item"><span class="meta-label">الاسم:</span><span class="meta-value">{{ $selectedReview->user?->name ?? 'غير معروف' }}</span></div>
                        <div class="meta-item"><span class="meta-label">البريد:</span><span class="meta-value">{{ $selectedReview->user?->email ?? '-' }}</span></div>
                        <div class="meta-item"><span class="meta-label">التاريخ:</span><span class="meta-value">{{ $selectedReview->created_at->format('Y/m/d H:i') }}</span></div>
                        <div class="meta-item"><span class="meta-label">شراء موثق:</span><span class="meta-value">{{ $selectedReview->is_verified_purchase ? 'نعم' : 'لا' }}</span></div>
                        <div class="meta-item"><span class="meta-label">الحالة:</span><span class="status-badge {{ $selectedReview->is_approved ? 'approved' : 'pending' }}">{{ $selectedReview->is_approved ? 'معتمد' : 'في الانتظار' }}</span></div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="action-buttons">
                        @if(!$selectedReview->is_approved)
                        <button class="action-btn approve" wire:click="approveReview({{ $selectedReview->id }})">✓ اعتماد</button>
                        @else
                        <button class="action-btn reject" wire:click="rejectReview({{ $selectedReview->id }})">✗ إلغاء الاعتماد</button>
                        @endif
                        <button class="action-btn edit" wire:click="editReview({{ $selectedReview->id }})">تعديل</button>
                        <button class="action-btn delete" wire:click="deleteReview({{ $selectedReview->id }})" wire:confirm="هل أنت متأكد من حذف هذا التقييم؟">حذف</button>
                    </div>
                </div>
                @else
                <div class="empty-state">
                    <div class="empty-icon"><svg width="32" height="32" fill="none" stroke="#f59e0b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg></div>
                    <p class="empty-title">اختر تقييم</p>
                    <p class="empty-text">اختر تقييم من القائمة لعرض التفاصيل</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
