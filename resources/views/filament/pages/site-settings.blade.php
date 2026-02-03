<x-filament-panels::page>
    <style>
        .site-settings-container { direction: rtl; }
        .site-tabs { display:flex; gap:.5rem; flex-wrap:wrap; margin-top:1rem; }
        .site-tab {
            background: rgba(15, 23, 42, 0.4);
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, 0.15);
            padding: 0.625rem 1rem;
            border-radius: 999px;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s ease;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }
        .site-tab:hover { background: rgba(99, 102, 241, 0.12); border-color: rgba(99,102,241,.35); color:#e2e8f0; }
        .site-tab.active { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-color: transparent; color: white; box-shadow: 0 6px 22px rgba(99,102,241,.35); }

        .site-settings-grid { display:grid; grid-template-columns: 1fr; gap: 1rem; }
        .table-wrap { background: rgba(15, 23, 42, 0.35); border: 1px solid rgba(148, 163, 184, 0.12); border-radius: 12px; overflow: hidden; }
        table.site-table { width:100%; border-collapse: collapse; }
        .site-table th {
            text-align: right;
            font-size: .75rem;
            color: #94a3b8;
            padding: .75rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
            background: rgba(15, 23, 42, 0.35);
        }
        .site-table td {
            padding: .85rem .75rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.10);
            vertical-align: middle;
            color: #e2e8f0;
        }
        .thumb {
            width: 92px;
            height: 56px;
            border-radius: 12px;
            overflow: hidden;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.18);
        }
        .thumb img { width:100%; height:100%; object-fit: cover; }
        .badge {
            display:inline-flex;
            align-items:center;
            gap:.35rem;
            padding:.25rem .6rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            border: 1px solid rgba(148, 163, 184, 0.12);
        }
        .badge.on { background: rgba(16, 185, 129, 0.10); border-color: rgba(16, 185, 129, 0.25); color: #34d399; }
        .badge.off { background: rgba(148, 163, 184, 0.08); color: #cbd5e1; }
        .mini-actions { display:flex; gap:.35rem; justify-content:flex-end; flex-wrap:wrap; }
        .mini-actions .action-btn { padding: .45rem .75rem; font-size: .75rem; border-radius: 10px; }

        .preview {
            background: rgba(15, 23, 42, 0.4);
            border: 1px dashed rgba(148, 163, 184, 0.22);
            border-radius: 12px;
            padding: .85rem 1rem;
            display:flex;
            gap: .9rem;
            align-items: center;
        }
        .preview img { height: 40px; width:auto; max-width: 220px; object-fit: contain; }
    </style>

    <div class="site-settings-container">
        @if(session()->has('success'))
            <div class="alert alert-success" style="margin-bottom: 1rem;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session()->has('error'))
            <div class="alert alert-error" style="margin-bottom: 1rem;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="content-header">
            <div>
                <h2>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    إعدادات الموقع
                </h2>
                <p>تحكم في سلايدر الصفحة الرئيسية + لوجو الهيدر للموقع العام</p>

                <div class="site-tabs">
                    <button type="button" class="site-tab {{ $activeTab === 'slider' ? 'active' : '' }}" wire:click="setActiveTab('slider')">
                        🖼️ سلايدر الصفحة الرئيسية
                    </button>
                    <button type="button" class="site-tab {{ $activeTab === 'branding' ? 'active' : '' }}" wire:click="setActiveTab('branding')">
                        🧩 هوية الموقع (اللوجو)
                    </button>
                </div>
            </div>
        </div>

        <div class="site-settings-grid">
            @if($activeTab === 'branding')
                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        لوجو الموقع العام (الهيدر)
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>رفع لوجو الموقع (PNG/JPG)</label>
                            <input type="file" wire:model="siteLogoFile" accept="image/*">
                            <span class="hint">يفضل PNG بخلفية شفافة. الحد الأقصى 4MB.</span>
                            @error('siteLogoFile')
                                <span class="hint" style="color:#f87171;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>ALT (النص البديل)</label>
                            <input type="text" wire:model="settings.site_logo_alt" placeholder="Pegasus Academy">
                            <span class="hint">مفيد لإتاحة الوصول + عند عدم تحميل الصورة.</span>
                        </div>
                    </div>

                    <div style="margin-top: 1rem; display:flex; gap: 1rem; align-items:center; justify-content: space-between; flex-wrap:wrap;">
                        <div>
                            @if(!empty($settings['site_logo_path'] ?? ''))
                                <div class="preview">
                                    <img src="{{ asset('storage/' . ltrim($settings['site_logo_path'], '/')) }}" alt="{{ $settings['site_logo_alt'] ?? 'Logo' }}">
                                    <div>
                                        <div style="color:#e2e8f0; font-weight:800; font-size:.9rem;">اللوجو الحالي للموقع</div>
                                        <div class="hint">يظهر في هيدر الموقع بجانب البحث.</div>
                                    </div>
                                </div>
                            @else
                                <div class="hint">لا يوجد لوجو موقع مرفوع حالياً.</div>
                            @endif
                        </div>

                        <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                            <button class="save-btn" wire:click="saveSiteLogo" type="button">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                حفظ اللوجو
                            </button>
                            <button class="action-btn" wire:click="saveSiteTextSettings" type="button">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                حفظ ALT
                            </button>
                            @if(!empty($settings['site_logo_path'] ?? ''))
                                <button class="action-btn" wire:click="removeSiteLogo" type="button" style="border-color: rgba(239, 68, 68, 0.35); color:#fca5a5; background: rgba(239,68,68,.08);">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0V5a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                    </svg>
                                    حذف اللوجو
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if($activeTab === 'slider')
                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 18h16M4 12h16"/>
                        </svg>
                        سلايدر الصفحة الرئيسية (صور)
                    </div>

                    <div class="hint" style="margin-bottom: 1rem;">يظهر في أعلى الصفحة الرئيسية مكان القسم المشار إليه.</div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>صورة الشريحة</label>
                            <input type="file" wire:model="slideImage" accept="image/*">
                            <span class="hint">مطلوبة عند الإضافة — اختيارية عند التعديل (الحد الأقصى 6MB).</span>
                            @error('slideImage')
                                <span class="hint" style="color:#f87171;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>تفعيل الشريحة</label>
                            <div class="toggle-group" style="margin-bottom:0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">الحالة</span>
                                    <span class="toggle-hint">إظهار/إخفاء الشريحة من السلايدر</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="slideForm.is_active">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid" style="margin-top: 1rem;">
                        <div class="form-group">
                            <label>العنوان</label>
                            <input type="text" wire:model="slideForm.title" placeholder="مثال: تعلّم بذكاء…">
                        </div>
                        <div class="form-group">
                            <label>الوصف</label>
                            <input type="text" wire:model="slideForm.subtitle" placeholder="مثال: دورات احترافية…">
                        </div>
                    </div>

                    <div class="form-grid" style="margin-top: 1rem;">
                        <div class="form-group">
                            <label>زر أساسي (نص)</label>
                            <input type="text" wire:model="slideForm.primary_text" placeholder="تصفح الدورات">
                        </div>
                        <div class="form-group">
                            <label>زر أساسي (رابط)</label>
                            <input type="text" wire:model="slideForm.primary_url" placeholder="/admin/browse-courses">
                        </div>
                        <div class="form-group">
                            <label>زر ثانوي (نص)</label>
                            <input type="text" wire:model="slideForm.secondary_text" placeholder="لوحة التحكم">
                        </div>
                        <div class="form-group">
                            <label>زر ثانوي (رابط)</label>
                            <input type="text" wire:model="slideForm.secondary_url" placeholder="/admin">
                        </div>
                    </div>

                    <div style="margin-top: 1rem; display:flex; gap:.75rem; justify-content:flex-end; flex-wrap:wrap;">
                        <button class="action-btn" wire:click="startAddSlide" type="button">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            تفريغ
                        </button>
                        <button class="save-btn" wire:click="saveSlide" type="button">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $editingSlideIndex === null ? 'إضافة شريحة' : 'حفظ التعديل' }}
                        </button>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 18h16M4 12h16"/>
                        </svg>
                        الشرائح الحالية
                    </div>

                    <div class="table-wrap">
                        <table class="site-table">
                            <thead>
                                <tr>
                                    <th style="width:110px;">الصورة</th>
                                    <th>النص</th>
                                    <th style="width:120px;">الحالة</th>
                                    <th style="width:280px;">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $slides = $settings['site_home_slider'] ?? []; @endphp
                                @forelse(($slides ?: []) as $idx => $s)
                                    <tr>
                                        <td>
                                            <div class="thumb">
                                                @if(!empty($s['image_path'] ?? ''))
                                                    <img src="{{ asset('storage/' . ltrim($s['image_path'], '/')) }}" alt="">
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight:800; color:#e2e8f0; line-height:1.2">{{ $s['title'] ?? 'بدون عنوان' }}</div>
                                            <div class="hint" style="margin-top:.25rem; line-height:1.2">{{ $s['subtitle'] ?? '' }}</div>
                                        </td>
                                        <td>
                                            @if(($s['is_active'] ?? true))
                                                <span class="badge on">مفعل</span>
                                            @else
                                                <span class="badge off">غير مفعل</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="mini-actions">
                                                <button class="action-btn" wire:click="moveSlideUp({{ $idx }})" type="button">⬆</button>
                                                <button class="action-btn" wire:click="moveSlideDown({{ $idx }})" type="button">⬇</button>
                                                <button class="action-btn" wire:click="editSlide({{ $idx }})" type="button">تعديل</button>
                                                <button class="action-btn" wire:click="deleteSlide({{ $idx }})" type="button" style="border-color: rgba(239, 68, 68, 0.35); color:#fca5a5; background: rgba(239,68,68,.08);">حذف</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="padding: 1rem;">
                                            <div class="hint" style="text-align:center;">لا توجد شرائح حتى الآن. قم بإضافة أول شريحة من الأعلى.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>

