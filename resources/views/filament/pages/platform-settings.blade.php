<x-filament-panels::page>
    <style>
        .settings-container {
            display: flex;
            gap: 1.5rem;
        }
        .settings-sidebar {
            width: 280px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            padding: 1rem;
            height: fit-content;
            position: sticky;
            top: 1rem;
        }
        .sidebar-title {
            color: #94a3b8;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.5rem 1rem;
            margin-bottom: 0.5rem;
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #cbd5e1;
            margin-bottom: 0.25rem;
        }
        .sidebar-item:hover {
            background: rgba(99, 102, 241, 0.1);
            color: #a5b4fc;
        }
        .sidebar-item.active {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }
        .sidebar-item svg {
            width: 20px;
            height: 20px;
        }
        .sidebar-item span {
            font-size: 0.875rem;
            font-weight: 500;
        }
        .settings-content {
            flex: 1;
            min-width: 0;
        }
        .content-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .content-header h2 {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .content-header h2 svg {
            width: 28px;
            height: 28px;
            color: #a5b4fc;
        }
        .content-header p {
            color: #94a3b8;
            font-size: 0.875rem;
            margin: 0.5rem 0 0 0;
        }
        .settings-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .card-title {
            color: #e2e8f0;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .card-title svg {
            width: 18px;
            height: 18px;
            color: #6366f1;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .form-grid.single {
            grid-template-columns: 1fr;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .form-group.full-width {
            grid-column: span 2;
        }
        .form-group label {
            color: #94a3b8;
            font-size: 0.8125rem;
            font-weight: 500;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select,
        .form-group textarea {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            color: white;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-group .hint {
            color: #64748b;
            font-size: 0.75rem;
        }
        .toggle-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: rgba(15, 23, 42, 0.4);
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        .toggle-group .toggle-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .toggle-group .toggle-label {
            color: #e2e8f0;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .toggle-group .toggle-hint {
            color: #64748b;
            font-size: 0.75rem;
        }
        .toggle-switch {
            position: relative;
            width: 48px;
            height: 26px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #334155;
            border-radius: 26px;
            transition: 0.3s;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.3s;
        }
        .toggle-switch input:checked + .toggle-slider {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }
        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(22px);
        }
        .save-btn {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }
        .save-btn svg {
            width: 18px;
            height: 18px;
        }
        .action-btn {
            background: rgba(99, 102, 241, 0.1);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.3);
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-size: 0.8125rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .action-btn:hover {
            background: rgba(99, 102, 241, 0.2);
        }
        .action-btn svg {
            width: 16px;
            height: 16px;
        }
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }
        .alert svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .social-card {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }
        .social-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .social-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .social-logo img {
            width: 32px;
            height: 32px;
        }
        .social-logo .logo-circle {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .social-logo .logo-circle.google {
            background: linear-gradient(135deg, #ea4335 0%, #fbbc05 50%, #34a853 100%);
        }
        .social-logo .logo-circle.facebook {
            background: #1877f2;
        }
        .social-logo .logo-circle.twitter {
            background: #000;
        }
        .social-logo .logo-circle svg {
            width: 20px;
            height: 20px;
            color: white;
        }
        .social-logo span {
            color: #e2e8f0;
            font-size: 1rem;
            font-weight: 600;
        }
        .analytics-card {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }
        .analytics-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .analytics-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .analytics-logo .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .analytics-logo .logo-icon.ga { background: linear-gradient(135deg, #f9ab00 0%, #e37400 100%); }
        .analytics-logo .logo-icon.fb { background: #1877f2; }
        .analytics-logo .logo-icon.gtm { background: #4285f4; }
        .analytics-logo .logo-icon.hotjar { background: #ff3c00; }
        .analytics-logo .logo-icon.clarity { background: #0078d4; }
        .analytics-logo span {
            color: #e2e8f0;
            font-size: 0.9375rem;
            font-weight: 600;
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(148, 163, 184, 0.1);
        }
        @media (max-width: 1024px) {
            .settings-container {
                flex-direction: column;
            }
            .settings-sidebar {
                width: 100%;
                position: static;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
        }
    </style>

    @if(session('success'))
        <div class="alert alert-success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="settings-container">
        <!-- Sidebar -->
        <div class="settings-sidebar">
            <div class="sidebar-title">الأقسام</div>
            @foreach($this->tabs as $key => $tab)
                <div class="sidebar-item {{ $activeTab === $key ? 'active' : '' }}" wire:click="setActiveTab('{{ $key }}')">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                    </svg>
                    <span>{{ $tab['label'] }}</span>
                </div>
            @endforeach
        </div>

        <!-- Content -->
        <div class="settings-content">
            <!-- Lessons Settings -->
            @if($activeTab === 'lessons')
                <div class="content-header">
                    <div>
                        <h2>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            إعدادات الدروس والفيديوهات
                        </h2>
                        <p>التحكم في طريقة عرض الدروس وحماية المحتوى</p>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        إعدادات الأجهزة والمشاهدات
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>الحد الأقصى للأجهزة لكل حساب</label>
                            <input type="number" wire:model="settings.max_devices_per_account" min="1" max="10">
                            <span class="hint">عدد الأجهزة المسموح بتسجيل الدخول منها في نفس الوقت</span>
                        </div>
                        <div class="form-group">
                            <label>الحد الأقصى لمشاهدات كل درس</label>
                            <input type="number" wire:model="settings.max_views_per_lesson" min="1" max="100">
                            <span class="hint">0 = غير محدود</span>
                        </div>
                        <div class="form-group">
                            <label>نسبة المشاهدة المطلوبة للإكمال (%)</label>
                            <input type="number" wire:model="settings.require_lesson_completion" min="50" max="100">
                            <span class="hint">النسبة المئوية المطلوبة لاعتبار الدرس مكتملاً</span>
                        </div>
                        <div class="form-group">
                            <label>جودة الفيديو الافتراضية</label>
                            <select wire:model="settings.default_video_quality">
                                <option value="auto">تلقائي</option>
                                <option value="1080p">1080p</option>
                                <option value="720p">720p</option>
                                <option value="480p">480p</option>
                                <option value="360p">360p</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        حماية المحتوى
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">منع الانتقال للدرس التالي قبل الإكمال</span>
                            <span class="toggle-hint">يجب على الطالب إكمال الدرس الحالي للانتقال للتالي</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enforce_lesson_order">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">تفعيل العلامة المائية</span>
                            <span class="toggle-hint">عرض بيانات المستخدم على الفيديو</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_video_watermark">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    @if($settings['enable_video_watermark'] ?? false)
                        <div class="form-grid" style="margin-top: 1rem;">
                            <div class="form-group full-width">
                                <label>نص العلامة المائية</label>
                                <input type="text" wire:model="settings.watermark_text" placeholder="{user_email} أو {user_name}">
                                <span class="hint">المتغيرات المتاحة: {user_email}, {user_name}, {user_id}</span>
                            </div>
                        </div>
                    @endif
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">منع تحميل الفيديوهات</span>
                            <span class="toggle-hint">إخفاء زر التحميل وحماية الروابط</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.prevent_video_download">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">السماح بتغيير سرعة التشغيل</span>
                            <span class="toggle-hint">تمكين المستخدم من تسريع أو إبطاء الفيديو</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_playback_speed">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">استئناف الفيديو من آخر نقطة</span>
                            <span class="toggle-hint">حفظ موضع المشاهدة والاستئناف تلقائياً</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_video_resume">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="save-btn" wire:click="saveLessonsSettings">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        حفظ إعدادات الدروس
                    </button>
                </div>
            @endif

            <!-- Security Settings -->
            @if($activeTab === 'security')
                <div class="content-header">
                    <div>
                        <h2>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            إعدادات الأمان
                        </h2>
                        <p>حماية الحسابات وتأمين تسجيل الدخول</p>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        حماية تسجيل الدخول
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>الحد الأقصى لمحاولات الدخول الفاشلة</label>
                            <input type="number" wire:model="settings.max_failed_login_attempts" min="3" max="10">
                        </div>
                        <div class="form-group">
                            <label>مدة قفل الحساب (بالدقائق)</label>
                            <input type="number" wire:model="settings.lockout_duration_minutes" min="5" max="1440">
                        </div>
                        <div class="form-group">
                            <label>مدة الجلسة (بالدقائق)</label>
                            <input type="number" wire:model="settings.session_lifetime_minutes" min="30" max="1440">
                        </div>
                        <div class="form-group">
                            <label>إجبار تغيير كلمة المرور كل (أيام)</label>
                            <input type="number" wire:model="settings.force_password_change_days" min="0" max="365">
                            <span class="hint">0 = تعطيل هذه الميزة</span>
                        </div>
                    </div>
                    
                    <div class="toggle-group" style="margin-top: 1rem;">
                        <div class="toggle-info">
                            <span class="toggle-label">تفعيل المصادقة الثنائية</span>
                            <span class="toggle-hint">طلب رمز تحقق إضافي عند تسجيل الدخول</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_two_factor_auth">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        متطلبات كلمة المرور
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>الحد الأدنى لطول كلمة المرور</label>
                            <input type="number" wire:model="settings.min_password_length" min="6" max="32">
                        </div>
                    </div>
                    
                    <div class="toggle-group" style="margin-top: 1rem;">
                        <div class="toggle-info">
                            <span class="toggle-label">يتطلب حرف كبير</span>
                            <span class="toggle-hint">يجب أن تحتوي كلمة المرور على حرف كبير واحد على الأقل</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.require_password_uppercase">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">يتطلب رقم</span>
                            <span class="toggle-hint">يجب أن تحتوي كلمة المرور على رقم واحد على الأقل</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.require_password_number">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        reCAPTCHA
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">تفعيل CAPTCHA</span>
                            <span class="toggle-hint">حماية النماذج من الروبوتات</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_captcha">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    @if($settings['enable_captcha'] ?? false)
                        <div class="form-grid" style="margin-top: 1rem;">
                            <div class="form-group">
                                <label>مفتاح الموقع (Site Key)</label>
                                <input type="text" wire:model="settings.captcha_site_key">
                            </div>
                            <div class="form-group">
                                <label>المفتاح السري (Secret Key)</label>
                                <input type="password" wire:model="settings.captcha_secret_key">
                            </div>
                        </div>
                    @endif
                </div>

                <div class="form-actions">
                    <button class="save-btn" wire:click="saveSecuritySettings">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        حفظ إعدادات الأمان
                    </button>
                </div>
            @endif

            <!-- Social Login Settings -->
            @if($activeTab === 'social')
                <div class="content-header">
                    <div>
                        <h2>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            تسجيل الدخول الاجتماعي
                        </h2>
                        <p>ربط حسابات التواصل الاجتماعي لتسهيل تسجيل الدخول</p>
                    </div>
                </div>

                <!-- Google -->
                <div class="social-card">
                    <div class="social-header">
                        <div class="social-logo">
                            <div class="logo-circle google">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                </svg>
                            </div>
                            <span>Google</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_google_login">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    @if($settings['enable_google_login'] ?? false)
                        <div class="form-grid">
                            <div class="form-group">
                                <label>معرف العميل (Client ID)</label>
                                <input type="text" wire:model="settings.google_client_id">
                            </div>
                            <div class="form-group">
                                <label>المفتاح السري (Client Secret)</label>
                                <input type="password" wire:model="settings.google_client_secret">
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Facebook -->
                <div class="social-card">
                    <div class="social-header">
                        <div class="social-logo">
                            <div class="logo-circle facebook">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </div>
                            <span>Facebook</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_facebook_login">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    @if($settings['enable_facebook_login'] ?? false)
                        <div class="form-grid">
                            <div class="form-group">
                                <label>معرف التطبيق (App ID)</label>
                                <input type="text" wire:model="settings.facebook_app_id">
                            </div>
                            <div class="form-group">
                                <label>المفتاح السري (App Secret)</label>
                                <input type="password" wire:model="settings.facebook_app_secret">
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Twitter -->
                <div class="social-card">
                    <div class="social-header">
                        <div class="social-logo">
                            <div class="logo-circle twitter">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </div>
                            <span>Twitter / X</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_twitter_login">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    @if($settings['enable_twitter_login'] ?? false)
                        <div class="form-grid">
                            <div class="form-group">
                                <label>معرف العميل (Client ID)</label>
                                <input type="text" wire:model="settings.twitter_client_id">
                            </div>
                            <div class="form-group">
                                <label>المفتاح السري (Client Secret)</label>
                                <input type="password" wire:model="settings.twitter_client_secret">
                            </div>
                        </div>
                    @endif
                </div>

                <div class="form-actions">
                    <button class="save-btn" wire:click="saveSocialSettings">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        حفظ إعدادات تسجيل الدخول
                    </button>
                </div>
            @endif

            <!-- Analytics Settings -->
            @if($activeTab === 'analytics')
                <div class="content-header">
                    <div>
                        <h2>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            التحليلات والتتبع
                        </h2>
                        <p>ربط أدوات التحليل لمتابعة أداء الموقع</p>
                    </div>
                </div>

                <!-- Google Analytics (GA4) -->
                <div class="settings-card">
                    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f9ab00 0%, #e37400 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                    <path d="M22.84 2.998c-.644-.644-1.696-.644-2.34 0l-3.712 3.712c-.644.644-.644 1.696 0 2.34l.806.806-5.468 5.468-.806-.806c-.644-.644-1.696-.644-2.34 0l-3.712 3.712c-.644.644-.644 1.696 0 2.34.644.644 1.696.644 2.34 0l3.712-3.712c.644-.644.644-1.696 0-2.34l-.806-.806 5.468-5.468.806.806c.644.644 1.696.644 2.34 0l3.712-3.712c.644-.644.644-1.696 0-2.34z"/>
                                    <circle cx="6" cy="18" r="3"/>
                                    <circle cx="18" cy="6" r="3"/>
                                </svg>
                            </div>
                            <span>Google Analytics (GA4)</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model.live="settings.enable_google_analytics">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    @if($settings['enable_google_analytics'] ?? false)
                        <div class="form-grid">
                            <div class="form-group">
                                <label>معرف القياس (Measurement ID)</label>
                                <input type="text" wire:model="settings.google_analytics_id" placeholder="G-XXXXXXXXXX">
                                <span class="hint">يمكنك الحصول عليه من Google Analytics > Admin > Data Streams</span>
                            </div>
                            <div class="form-group">
                                <label>معرف الموقع (Property ID)</label>
                                <input type="text" wire:model="settings.ga_property_id" placeholder="123456789">
                            </div>
                        </div>
                        
                        <div style="margin-top: 1rem; padding: 1rem; background: rgba(249, 171, 0, 0.1); border-radius: 8px; border-right: 3px solid #f9ab00;">
                            <div style="color: #f9ab00; font-weight: 600; margin-bottom: 0.5rem;">خيارات التتبع</div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">تتبع التجارة الإلكترونية المحسّن</span>
                                    <span class="toggle-hint">تتبع عمليات الشراء والمنتجات</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.ga_enhanced_ecommerce">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">تتبع مشاهدات الصفحات</span>
                                    <span class="toggle-hint">تتبع تلقائي لجميع الصفحات</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.ga_track_pageviews">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">وضع الاختبار (Debug Mode)</span>
                                    <span class="toggle-hint">عرض الأحداث في DebugView</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.ga_debug_mode">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Facebook Pixel -->
                <div class="settings-card">
                    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 40px; height: 40px; background: #1877f2; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </div>
                            <span>Facebook Pixel</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model.live="settings.enable_facebook_pixel">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    @if($settings['enable_facebook_pixel'] ?? false)
                        <div class="form-grid">
                            <div class="form-group">
                                <label>معرف البكسل (Pixel ID)</label>
                                <input type="text" wire:model="settings.facebook_pixel_id" placeholder="123456789012345">
                                <span class="hint">يمكنك الحصول عليه من Events Manager في Facebook Business</span>
                            </div>
                            <div class="form-group">
                                <label>رمز الاختبار (Test Event Code)</label>
                                <input type="text" wire:model="settings.fb_test_event_code" placeholder="TEST12345">
                                <span class="hint">اختياري - للاختبار في Events Manager</span>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1rem; padding: 1rem; background: rgba(24, 119, 242, 0.1); border-radius: 8px; border-right: 3px solid #1877f2;">
                            <div style="color: #1877f2; font-weight: 600; margin-bottom: 0.5rem;">Conversions API (اختياري)</div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Access Token</label>
                                    <input type="password" wire:model="settings.fb_access_token" placeholder="EAAxxxxxx...">
                                    <span class="hint">للإرسال من الخادم (Server-Side)</span>
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1rem; padding: 1rem; background: rgba(24, 119, 242, 0.05); border-radius: 8px;">
                            <div style="color: #94a3b8; font-weight: 600; margin-bottom: 0.5rem;">الأحداث المتتبعة</div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">PageView</span>
                                    <span class="toggle-hint">تتبع مشاهدات الصفحات</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.fb_track_pageview">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">Purchase</span>
                                    <span class="toggle-hint">تتبع عمليات الشراء</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.fb_track_purchase">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">AddToCart</span>
                                    <span class="toggle-hint">تتبع الإضافة للسلة</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.fb_track_add_to_cart">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">CompleteRegistration</span>
                                    <span class="toggle-hint">تتبع التسجيل الجديد</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.fb_track_registration">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">Lead</span>
                                    <span class="toggle-hint">تتبع التسجيل في الدورات</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.fb_track_lead">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Google Tag Manager -->
                <div class="settings-card">
                    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 40px; height: 40px; background: #4285f4; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                            </div>
                            <span>Google Tag Manager</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model.live="settings.enable_google_tag_manager">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    @if($settings['enable_google_tag_manager'] ?? false)
                        <div class="form-grid">
                            <div class="form-group">
                                <label>معرف الحاوية (Container ID)</label>
                                <input type="text" wire:model="settings.google_tag_manager_id" placeholder="GTM-XXXXXXX">
                                <span class="hint">يبدأ بـ GTM-</span>
                            </div>
                            <div class="form-group">
                                <label>البيئة (Environment)</label>
                                <select wire:model="settings.gtm_environment">
                                    <option value="live">Live (الإنتاج)</option>
                                    <option value="preview">Preview (المعاينة)</option>
                                    <option value="dev">Development (التطوير)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1rem; padding: 1rem; background: rgba(66, 133, 244, 0.1); border-radius: 8px; border-right: 3px solid #4285f4;">
                            <div style="color: #4285f4; font-weight: 600; margin-bottom: 0.5rem;">خيارات متقدمة</div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">تفعيل Data Layer</span>
                                    <span class="toggle-hint">إرسال البيانات للـ Data Layer</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.gtm_enable_datalayer">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">تتبع أحداث التجارة الإلكترونية</span>
                                    <span class="toggle-hint">إرسال أحداث الشراء والمنتجات</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.gtm_ecommerce_events">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        @if($settings['gtm_environment'] ?? 'live' !== 'live')
                            <div class="form-grid" style="margin-top: 1rem;">
                                <div class="form-group">
                                    <label>Auth Token</label>
                                    <input type="text" wire:model="settings.gtm_auth_token" placeholder="xxxxxxxxxxxxx">
                                </div>
                                <div class="form-group">
                                    <label>Preview ID</label>
                                    <input type="text" wire:model="settings.gtm_preview_id" placeholder="env-xx">
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Hotjar -->
                <div class="settings-card">
                    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff3c00 0%, #fd3a00 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                    <path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2zm0 4a1 1 0 00-1 1v4.586l-2.707 2.707a1 1 0 001.414 1.414l3-3A1 1 0 0013 12V7a1 1 0 00-1-1z"/>
                                </svg>
                            </div>
                            <span>Hotjar</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model.live="settings.enable_hotjar">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    @if($settings['enable_hotjar'] ?? false)
                        <div class="form-grid">
                            <div class="form-group">
                                <label>معرف الموقع (Site ID)</label>
                                <input type="text" wire:model="settings.hotjar_site_id" placeholder="1234567">
                                <span class="hint">يمكنك الحصول عليه من Hotjar Dashboard</span>
                            </div>
                            <div class="form-group">
                                <label>إصدار Hotjar</label>
                                <select wire:model="settings.hotjar_version">
                                    <option value="6">Version 6 (الأحدث)</option>
                                    <option value="5">Version 5</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1rem; padding: 1rem; background: rgba(255, 60, 0, 0.1); border-radius: 8px; border-right: 3px solid #ff3c00;">
                            <div style="color: #ff3c00; font-weight: 600; margin-bottom: 0.5rem;">ميزات Hotjar</div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">تسجيل الجلسات (Recordings)</span>
                                    <span class="toggle-hint">تسجيل حركة المستخدمين</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.hotjar_recordings">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">خرائط الحرارة (Heatmaps)</span>
                                    <span class="toggle-hint">عرض أماكن النقر والتمرير</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.hotjar_heatmaps">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">الاستطلاعات (Surveys)</span>
                                    <span class="toggle-hint">عرض استطلاعات للمستخدمين</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.hotjar_surveys">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="toggle-group" style="margin-bottom: 0;">
                                <div class="toggle-info">
                                    <span class="toggle-label">التغذية الراجعة (Feedback)</span>
                                    <span class="toggle-hint">زر التغذية الراجعة</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="settings.hotjar_feedback">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Microsoft Clarity -->
                <div class="analytics-card">
                    <div class="analytics-header">
                        <div class="analytics-logo">
                            <div class="logo-icon clarity">🔍</div>
                            <span>Microsoft Clarity</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_clarity">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    @if($settings['enable_clarity'] ?? false)
                        <div class="form-group">
                            <label>معرف المشروع (Project ID)</label>
                            <input type="text" wire:model="settings.clarity_project_id">
                        </div>
                    @endif
                </div>

                <div class="form-actions">
                    <button class="save-btn" wire:click="saveAnalyticsSettings">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        حفظ إعدادات التحليلات
                    </button>
                </div>
            @endif

            <!-- Email Settings -->
            @if($activeTab === 'email')
                <div class="content-header">
                    <div>
                        <h2>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            إعدادات البريد الإلكتروني
                        </h2>
                        <p>تكوين خدمات إرسال البريد والإشعارات</p>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        مزود خدمة البريد
                    </div>
                    <div class="form-group">
                        <label>اختر مزود الخدمة</label>
                        <select wire:model.live="settings.email_provider">
                            <option value="smtp">SMTP</option>
                            <option value="brevo">Brevo (Sendinblue)</option>
                            <option value="mailgun">Mailgun</option>
                            <option value="ses">Amazon SES</option>
                        </select>
                    </div>
                </div>

                @if(($settings['email_provider'] ?? 'smtp') === 'brevo')
                    <div class="settings-card">
                        <div class="card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            إعدادات Brevo
                        </div>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label>مفتاح API</label>
                                <input type="password" wire:model="settings.brevo_api_key">
                            </div>
                            <div class="form-group">
                                <label>اسم المرسل</label>
                                <input type="text" wire:model="settings.brevo_sender_name">
                            </div>
                            <div class="form-group">
                                <label>بريد المرسل</label>
                                <input type="email" wire:model="settings.brevo_sender_email">
                            </div>
                        </div>
                    </div>
                @else
                    <div class="settings-card">
                        <div class="card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                            </svg>
                            إعدادات SMTP
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>خادم SMTP</label>
                                <input type="text" wire:model="settings.smtp_host" placeholder="smtp.example.com">
                            </div>
                            <div class="form-group">
                                <label>المنفذ</label>
                                <input type="number" wire:model="settings.smtp_port">
                            </div>
                            <div class="form-group">
                                <label>اسم المستخدم</label>
                                <input type="text" wire:model="settings.smtp_username">
                            </div>
                            <div class="form-group">
                                <label>كلمة المرور</label>
                                <input type="password" wire:model="settings.smtp_password">
                            </div>
                            <div class="form-group">
                                <label>التشفير</label>
                                <select wire:model="settings.smtp_encryption">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="">بدون تشفير</option>
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        معلومات المرسل
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>البريد المرسل منه</label>
                            <input type="email" wire:model="settings.email_from_address" placeholder="noreply@example.com">
                        </div>
                        <div class="form-group">
                            <label>اسم المرسل</label>
                            <input type="text" wire:model="settings.email_from_name" placeholder="Pegasus Academy">
                        </div>
                    </div>
                    
                    <div class="toggle-group" style="margin-top: 1rem;">
                        <div class="toggle-info">
                            <span class="toggle-label">تفعيل إشعارات البريد</span>
                            <span class="toggle-hint">إرسال إشعارات بالبريد للمستخدمين</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_email_notifications">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">تفعيل تذكيرات البريد</span>
                            <span class="toggle-hint">إرسال تذكيرات تلقائية بالبريد</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_email_reminders">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        اختبار الإرسال
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>بريد الاختبار</label>
                            <input type="email" wire:model="testEmailAddress" placeholder="test@example.com">
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button class="action-btn" wire:click="sendTestEmail">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                إرسال بريد اختباري
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="save-btn" wire:click="saveEmailSettings">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        حفظ إعدادات البريد
                    </button>
                </div>
            @endif

            <!-- SEO Settings -->
            @if($activeTab === 'seo')
                <div class="content-header">
                    <div>
                        <h2>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            تحسين محركات البحث (SEO)
                        </h2>
                        <p>تحسين ظهور الموقع في نتائج البحث</p>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        البيانات الأساسية
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>عنوان الموقع</label>
                            <input type="text" wire:model="settings.site_title">
                        </div>
                        <div class="form-group">
                            <label>الرابط الأساسي (Canonical URL)</label>
                            <input type="text" wire:model="settings.canonical_url" placeholder="https://example.com">
                        </div>
                        <div class="form-group full-width">
                            <label>وصف الموقع</label>
                            <textarea wire:model="settings.site_description" placeholder="وصف مختصر للموقع يظهر في نتائج البحث"></textarea>
                        </div>
                        <div class="form-group full-width">
                            <label>الكلمات المفتاحية</label>
                            <input type="text" wire:model="settings.site_keywords" placeholder="تعليم, دورات, أونلاين">
                            <span class="hint">افصل بين الكلمات بفواصل</span>
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Sitemap & Robots.txt
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">تفعيل Sitemap.xml</span>
                            <span class="toggle-hint">إنشاء خريطة الموقع تلقائياً</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_sitemap">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    @if($settings['enable_sitemap'] ?? false)
                        <div class="form-grid" style="margin-top: 1rem;">
                            <div class="form-group">
                                <label>تكرار التحديث</label>
                                <select wire:model="settings.sitemap_frequency">
                                    <option value="always">دائماً</option>
                                    <option value="hourly">كل ساعة</option>
                                    <option value="daily">يومياً</option>
                                    <option value="weekly">أسبوعياً</option>
                                    <option value="monthly">شهرياً</option>
                                </select>
                            </div>
                            <div class="form-group" style="display: flex; align-items: flex-end;">
                                <button class="action-btn" wire:click="generateSitemap">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    إنشاء Sitemap الآن
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="form-group full-width" style="margin-top: 1rem;">
                        <label>محتوى Robots.txt</label>
                        <textarea wire:model="settings.robots_txt" style="font-family: monospace; min-height: 150px;"></textarea>
                        <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                            <button class="action-btn" wire:click="updateRobotsTxt">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                تحديث Robots.txt
                            </button>
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                        البيانات المنظمة (Structured Data)
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">تفعيل البيانات المنظمة</span>
                            <span class="toggle-hint">إضافة Schema.org للصفحات</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_structured_data">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    @if($settings['enable_structured_data'] ?? false)
                        <div class="form-group" style="margin-top: 1rem;">
                            <label>نوع المؤسسة</label>
                            <select wire:model="settings.organization_type">
                                <option value="EducationalOrganization">مؤسسة تعليمية</option>
                                <option value="School">مدرسة</option>
                                <option value="CollegeOrUniversity">جامعة أو كلية</option>
                                <option value="Organization">مؤسسة عامة</option>
                            </select>
                        </div>
                    @endif
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        إعدادات المشاركة الاجتماعية
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>نوع بطاقة Twitter</label>
                            <select wire:model="settings.twitter_card_type">
                                <option value="summary">ملخص</option>
                                <option value="summary_large_image">ملخص مع صورة كبيرة</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>حساب Twitter للموقع</label>
                            <input type="text" wire:model="settings.twitter_site" placeholder="@username">
                        </div>
                        <div class="form-group full-width">
                            <label>صورة Open Graph الافتراضية</label>
                            <input type="text" wire:model="settings.og_image" placeholder="https://example.com/image.jpg">
                            <span class="hint">الصورة التي تظهر عند المشاركة (1200x630 px)</span>
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        تحسين الأداء
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">التحميل الكسول للصور</span>
                            <span class="toggle-hint">تأخير تحميل الصور حتى تظهر في الشاشة</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_lazy_loading">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">ضغط CSS/JS</span>
                            <span class="toggle-hint">تصغير حجم ملفات الأنماط والسكريبتات</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_minification">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">التخزين المؤقت للمتصفح</span>
                            <span class="toggle-hint">تسريع التحميل للزوار العائدين</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_browser_caching">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    @if($settings['enable_browser_caching'] ?? false)
                        <div class="form-group" style="margin-top: 1rem;">
                            <label>مدة التخزين المؤقت (أيام)</label>
                            <input type="number" wire:model="settings.cache_duration_days" min="1" max="365">
                        </div>
                    @endif
                    
                    <div class="toggle-group" style="margin-top: 1rem;">
                        <div class="toggle-info">
                            <span class="toggle-label">تحسين الجوال</span>
                            <span class="toggle-hint">تحسين تجربة المستخدم على الهواتف</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.enable_mobile_optimization">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="save-btn" wire:click="saveSeoSettings">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        حفظ إعدادات SEO
                    </button>
                </div>
            @endif

            <!-- Zoom Settings -->
            @if($activeTab === 'zoom')
                <div class="content-header">
                    <div>
                        <h2>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            إعدادات Zoom
                        </h2>
                        <p>إدارة وتكوين اتصالات Zoom API الخاصة بك</p>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5.36 5.36l-.707.707M5.979 18.364l.707-.707M12 17a5 5 0 100-10 5 5 0 000 10z"/>
                        </svg>
                        تفعيل Zoom
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">تفعيل خدمة Zoom</span>
                            <span class="toggle-hint">تمكين دعم Zoom في الدروس والفيديوهات</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.zoom_enabled">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                @if($settings['zoom_enabled'] ?? false)
                    <div class="settings-card">
                        <div class="card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            بيانات OAuth2
                        </div>
                        <p style="color: #cbd5e1; font-size: 0.875rem; margin-bottom: 1rem;">الحصول على بيانات الاعتماد من <a href="https://marketplace.zoom.us" target="_blank" style="color: #a5b4fc;">Zoom App Marketplace</a></p>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label>معرّف عميل OAuth2 (Client ID)</label>
                                <input type="text" wire:model="settings.zoom_client_id" placeholder="أدخل Client ID">
                                <span class="hint">معرف التطبيق من Zoom</span>
                            </div>
                            <div class="form-group full-width">
                                <label>سر عميل OAuth2 (Client Secret)</label>
                                <input type="password" wire:model="settings.zoom_client_secret" placeholder="أدخل Client Secret">
                                <span class="hint">سر التطبيق - احفظه بأمان</span>
                            </div>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            بيانات API الخادم
                        </div>
                        <p style="color: #cbd5e1; font-size: 0.875rem; margin-bottom: 1rem;">لإنشاء الاجتماعات تلقائياً دون تدخل المستخدم</p>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label>معرف حساب Zoom (Account ID)</label>
                                <input type="text" wire:model="settings.zoom_account_id" placeholder="أدخل Account ID">
                                <span class="hint">معرف الحساب من إعدادات Zoom</span>
                            </div>
                            <div class="form-group full-width">
                                <label>مفتاح API (API Key)</label>
                                <input type="text" wire:model="settings.zoom_api_key" placeholder="أدخل API Key">
                                <span class="hint">مفتاح التطبيق من Zoom</span>
                            </div>
                            <div class="form-group full-width">
                                <label>سر API (API Secret)</label>
                                <input type="password" wire:model="settings.zoom_api_secret" placeholder="أدخل API Secret">
                                <span class="hint">سر التطبيق - احفظه بأمان</span>
                            </div>
                            <div class="form-group full-width">
                                <label>معرف مستخدم Zoom (User ID)</label>
                                <input type="text" wire:model="settings.zoom_user_id" placeholder="أدخل User ID">
                                <span class="hint">معرف المستخدم الذي سيستضيف الاجتماعات</span>
                            </div>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            إعدادات الاجتماع الافتراضية
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>مدة الاجتماع الافتراضية (بالدقائق)</label>
                                <input type="number" wire:model="settings.zoom_meeting_duration" min="15" max="480" step="15">
                                <span class="hint">مدة الاجتماع بالدقائق (الحد الأدنى 15)</span>
                            </div>
                            <div class="form-group">
                                <label>نوع الصوت</label>
                                <select wire:model="settings.zoom_audio_type">
                                    <option value="both">Both (VOIP + Telephony)</option>
                                    <option value="voip">VoIP فقط</option>
                                    <option value="telephony">Telephony فقط</option>
                                </select>
                            </div>
                        </div>

                        <div class="toggle-group" style="margin-top: 1rem;">
                            <div class="toggle-info">
                                <span class="toggle-label">تسجيل الاجتماع تلقائياً</span>
                                <span class="toggle-hint">تسجيل الاجتماع تلقائياً عند البدء</span>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" wire:model="settings.zoom_enable_auto_recording">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="toggle-group">
                            <div class="toggle-info">
                                <span class="toggle-label">مطلوب كلمة مرور</span>
                                <span class="toggle-hint">تطلب كلمة مرور للدخول للاجتماع</span>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" wire:model="settings.zoom_require_password">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="toggle-group">
                            <div class="toggle-info">
                                <span class="toggle-label">تفعيل انتظار الاستقبال</span>
                                <span class="toggle-hint">الحضور ينتظرون موافقة المضيف للدخول</span>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" wire:model="settings.zoom_waiting_room_enabled">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            إعدادات الفيديو
                        </div>

                        <div class="toggle-group">
                            <div class="toggle-info">
                                <span class="toggle-label">فيديو المضيف مفعل</span>
                                <span class="toggle-hint">المضيف يدخل الاجتماع مع تشغيل الفيديو</span>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" wire:model="settings.zoom_host_video">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="toggle-group">
                            <div class="toggle-info">
                                <span class="toggle-label">فيديو الحضور مفعل</span>
                                <span class="toggle-hint">الحضور يستطيعون تشغيل الفيديو</span>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" wire:model="settings.zoom_participant_video">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                @endif

                <div class="form-actions">
                    <button class="save-btn" wire:click="saveZoomSettings">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        حفظ إعدادات Zoom
                    </button>
                </div>
            @endif

            <!-- General Settings -->
            @if($activeTab === 'general')
                <div class="content-header">
                    <div>
                        <h2>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            الإعدادات العامة
                        </h2>
                        <p>إعدادات الموقع الأساسية والتسجيل</p>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2v-1"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 15l2 2 4-4"/>
                        </svg>
                        لوجو لوحة التحكم (Admin Panel)
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>رفع لوجو لوحة التحكم (PNG/JPG)</label>
                            <input type="file" wire:model="adminLogoFile" accept="image/*">
                            <span class="hint">يفضل PNG بخلفية شفافة. الحد الأقصى 2MB.</span>
                            @error('adminLogoFile')
                                <span class="hint" style="color:#f87171;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>النص البديل للوجو (ALT)</label>
                            <input type="text" wire:model="settings.admin_logo_alt" placeholder="Pegasus Academy">
                            <span class="hint">يظهر عند عدم تحميل الصورة + مفيد لإتاحة الوصول</span>
                        </div>
                    </div>

                    <div style="margin-top: 1rem; display:flex; gap: 1rem; align-items: center; justify-content: space-between;">
                        <div style="display:flex; gap: 1rem; align-items: center;">
                            @if(!empty($settings['admin_logo_path'] ?? ''))
                                <div style="background: rgba(15,23,42,.4); border:1px solid rgba(148,163,184,.15); border-radius: 12px; padding: .75rem 1rem; display:flex; align-items:center; gap:.75rem;">
                                    <img
                                        src="{{ asset('storage/' . ltrim($settings['admin_logo_path'], '/')) }}"
                                        alt="{{ $settings['admin_logo_alt'] ?? 'Logo' }}"
                                        style="height: 40px; width:auto; max-width: 200px; object-fit: contain;"
                                    />
                                    <div>
                                        <div style="color:#e2e8f0; font-weight:600; font-size:.875rem;">اللوجو الحالي</div>
                                        <div style="color:#64748b; font-size:.75rem;">يظهر في هيدر لوحة التحكم فقط</div>
                                    </div>
                                </div>
                            @else
                                <div style="color:#94a3b8; font-size:.875rem;">لا يوجد لوجو مرفوع حالياً.</div>
                            @endif
                        </div>

                        @if(!empty($settings['admin_logo_path'] ?? ''))
                            <button class="action-btn" wire:click="removeAdminLogo" type="button">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0V5a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                </svg>
                                حذف اللوجو
                            </button>
                        @endif
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        وضع الموقع
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">وضع الصيانة</span>
                            <span class="toggle-hint">إغلاق الموقع مؤقتاً للصيانة</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.maintenance_mode">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    @if($settings['maintenance_mode'] ?? false)
                        <div class="form-group" style="margin-top: 1rem;">
                            <label>رسالة الصيانة</label>
                            <textarea wire:model="settings.maintenance_message"></textarea>
                        </div>
                    @endif
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        إعدادات التسجيل
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">السماح بالتسجيل</span>
                            <span class="toggle-hint">تمكين المستخدمين الجدد من إنشاء حسابات</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.allow_registration">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-info">
                            <span class="toggle-label">تأكيد البريد الإلكتروني</span>
                            <span class="toggle-hint">يجب تأكيد البريد قبل تفعيل الحساب</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" wire:model="settings.require_email_verification">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="form-group" style="margin-top: 1rem;">
                        <label>الدور الافتراضي للمستخدم الجديد</label>
                        <select wire:model="settings.default_user_role">
                            <option value="student">طالب</option>
                            <option value="instructor">مدرس</option>
                        </select>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        التاريخ والوقت والعملة
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>المنطقة الزمنية</label>
                            <select wire:model="settings.timezone">
                                <option value="Africa/Cairo">القاهرة (GMT+2)</option>
                                <option value="Asia/Riyadh">الرياض (GMT+3)</option>
                                <option value="Asia/Dubai">دبي (GMT+4)</option>
                                <option value="Asia/Kuwait">الكويت (GMT+3)</option>
                                <option value="UTC">UTC</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>تنسيق التاريخ</label>
                            <select wire:model="settings.date_format">
                                <option value="d/m/Y">25/01/2026</option>
                                <option value="Y-m-d">2026-01-25</option>
                                <option value="d-m-Y">25-01-2026</option>
                                <option value="M d, Y">Jan 25, 2026</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>تنسيق الوقت</label>
                            <select wire:model="settings.time_format">
                                <option value="H:i">24 ساعة (14:30)</option>
                                <option value="h:i A">12 ساعة (02:30 PM)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>العملة</label>
                            <select wire:model="settings.currency">
                                <option value="EGP">جنيه مصري (EGP)</option>
                                <option value="SAR">ريال سعودي (SAR)</option>
                                <option value="AED">درهم إماراتي (AED)</option>
                                <option value="KWD">دينار كويتي (KWD)</option>
                                <option value="USD">دولار أمريكي (USD)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>رمز العملة</label>
                            <input type="text" wire:model="settings.currency_symbol">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="save-btn" wire:click="saveGeneralSettings">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        حفظ الإعدادات العامة
                    </button>
                </div>
            @endif

            <!-- Site Settings -->
            @if($activeTab === 'site')
                <div class="content-header">
                    <div>
                        <h2>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20zM2 12h20M12 2c2.5 2.7 4 6.2 4 10s-1.5 7.3-4 10c-2.5-2.7-4-6.2-4-10S9.5 4.7 12 2z"/>
                            </svg>
                            إعدادات الموقع
                        </h2>
                        <p>تحكم في لوجو الموقع العام + سلايدر صور الصفحة الرئيسية</p>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2v-1"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 15l2 2 4-4"/>
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
                            <span class="hint">مفيد لإتاحة الوصول + يظهر عند عدم تحميل الصورة.</span>
                        </div>
                    </div>

                    <div style="margin-top: 1rem; display:flex; gap: 1rem; align-items: center; justify-content: space-between; flex-wrap:wrap;">
                        <div style="display:flex; gap: 1rem; align-items: center;">
                            @if(!empty($settings['site_logo_path'] ?? ''))
                                <div style="background: rgba(15,23,42,.4); border:1px solid rgba(148,163,184,.15); border-radius: 12px; padding: .75rem 1rem; display:flex; align-items:center; gap:.75rem;">
                                    <img
                                        src="{{ asset('storage/' . ltrim($settings['site_logo_path'], '/')) }}"
                                        alt="{{ $settings['site_logo_alt'] ?? 'Logo' }}"
                                        style="height: 40px; width:auto; max-width: 200px; object-fit: contain;"
                                    />
                                    <div>
                                        <div style="color:#e2e8f0; font-weight:600; font-size:.875rem;">اللوجو الحالي</div>
                                        <div style="color:#64748b; font-size:.75rem;">يظهر في هيدر الموقع العام</div>
                                    </div>
                                </div>
                            @else
                                <div style="color:#94a3b8; font-size:.875rem;">لا يوجد لوجو موقع مرفوع حالياً.</div>
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
                                حفظ النصوص والروابط
                            </button>
                            @if(!empty($settings['site_logo_path'] ?? ''))
                                <button class="action-btn" wire:click="removeSiteLogo" type="button" style="border-color: rgba(239, 68, 68, 0.3); color:#fca5a5; background: rgba(239, 68, 68, 0.08);">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0V5a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                    </svg>
                                    حذف اللوجو
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        لوجو فوتر الموقع
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>رفع لوجو الفوتر (PNG/JPG)</label>
                            <input type="file" wire:model="siteFooterLogoFile" accept="image/*">
                            <span class="hint">يفضل PNG بخلفية شفافة. الحد الأقصى 4MB.</span>
                            @error('siteFooterLogoFile')
                                <span class="hint" style="color:#f87171;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>ALT (النص البديل) للفوتر</label>
                            <input type="text" wire:model="settings.site_footer_logo_alt" placeholder="Pegasus Academy">
                            <span class="hint">يظهر عند عدم تحميل الصورة + مفيد لإتاحة الوصول.</span>
                        </div>
                    </div>

                    <div style="margin-top: 1rem; display:flex; gap: 1rem; align-items: center; justify-content: space-between; flex-wrap:wrap;">
                        <div style="display:flex; gap: 1rem; align-items: center;">
                            @if(!empty($settings['site_footer_logo_path'] ?? ''))
                                <div style="background: rgba(15,23,42,.4); border:1px solid rgba(148,163,184,.15); border-radius: 12px; padding: .75rem 1rem; display:flex; align-items:center; gap:.75rem;">
                                    <img
                                        src="{{ asset('storage/' . ltrim($settings['site_footer_logo_path'], '/')) }}"
                                        alt="{{ $settings['site_footer_logo_alt'] ?? ($settings['site_logo_alt'] ?? 'Logo') }}"
                                        style="height: 40px; width:auto; max-width: 200px; object-fit: contain;"
                                    />
                                    <div>
                                        <div style="color:#e2e8f0; font-weight:600; font-size:.875rem;">لوجو الفوتر الحالي</div>
                                        <div style="color:#64748b; font-size:.75rem;">يظهر في فوتر الموقع العام</div>
                                    </div>
                                </div>
                            @else
                                <div style="color:#94a3b8; font-size:.875rem;">لا يوجد لوجو فوتر مرفوع حالياً (سيتم استخدام لوجو الهيدر تلقائياً).</div>
                            @endif
                        </div>

                        <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                            <button class="save-btn" wire:click="saveSiteFooterLogo" type="button">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                حفظ لوجو الفوتر
                            </button>
                            @if(!empty($settings['site_footer_logo_path'] ?? ''))
                                <button class="action-btn" wire:click="removeSiteFooterLogo" type="button" style="border-color: rgba(239, 68, 68, 0.3); color:#fca5a5; background: rgba(239, 68, 68, 0.08);">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0V5a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                    </svg>
                                    حذف لوجو الفوتر
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        روابط تحميل التطبيق
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Google Play URL</label>
                            <input type="text" wire:model="settings.site_app_google_play_url" placeholder="https://play.google.com/store/apps/details?id=...">
                            <span class="hint">سيظهر زر Google Play في فوتر الموقع.</span>
                        </div>
                        <div class="form-group">
                            <label>App Store URL</label>
                            <input type="text" wire:model="settings.site_app_apple_store_url" placeholder="https://apps.apple.com/app/id...">
                            <span class="hint">سيظهر زر App Store في فوتر الموقع.</span>
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        إضافة / تعديل شريحة السلايدر
                    </div>

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

                    <div class="form-actions">
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        الشرائح الحالية
                    </div>

                    <div style="background: rgba(15,23,42,.4); border:1px solid rgba(148,163,184,.12); border-radius: 12px; overflow:hidden;">
                        <table style="width:100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="text-align:right; padding:.75rem; color:#94a3b8; font-size:.75rem; border-bottom:1px solid rgba(148,163,184,.12); width:110px;">الصورة</th>
                                    <th style="text-align:right; padding:.75rem; color:#94a3b8; font-size:.75rem; border-bottom:1px solid rgba(148,163,184,.12);">النص</th>
                                    <th style="text-align:right; padding:.75rem; color:#94a3b8; font-size:.75rem; border-bottom:1px solid rgba(148,163,184,.12); width:120px;">الحالة</th>
                                    <th style="text-align:right; padding:.75rem; color:#94a3b8; font-size:.75rem; border-bottom:1px solid rgba(148,163,184,.12); width:280px;">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $slides = $settings['site_home_slider'] ?? []; @endphp
                                @forelse(($slides ?: []) as $idx => $s)
                                    <tr>
                                        <td style="padding:.85rem .75rem; border-bottom:1px solid rgba(148,163,184,.10);">
                                            <div style="width:92px; height:56px; border-radius:12px; overflow:hidden; background: rgba(15,23,42,.6); border:1px solid rgba(148,163,184,.18);">
                                                @if(!empty($s['image_path'] ?? ''))
                                                    <img src="{{ asset('storage/' . ltrim($s['image_path'], '/')) }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                                                @endif
                                            </div>
                                        </td>
                                        <td style="padding:.85rem .75rem; border-bottom:1px solid rgba(148,163,184,.10); color:#e2e8f0;">
                                            <div style="font-weight:800; line-height:1.2">{{ $s['title'] ?? 'بدون عنوان' }}</div>
                                            <div style="color:#64748b; font-size:.75rem; margin-top:.25rem; line-height:1.2">{{ $s['subtitle'] ?? '' }}</div>
                                        </td>
                                        <td style="padding:.85rem .75rem; border-bottom:1px solid rgba(148,163,184,.10);">
                                            @if(($s['is_active'] ?? true))
                                                <span style="display:inline-flex; padding:.25rem .6rem; border-radius:999px; font-size:.75rem; font-weight:700; background: rgba(16,185,129,.10); border:1px solid rgba(16,185,129,.25); color:#34d399;">مفعل</span>
                                            @else
                                                <span style="display:inline-flex; padding:.25rem .6rem; border-radius:999px; font-size:.75rem; font-weight:700; background: rgba(148,163,184,.08); border:1px solid rgba(148,163,184,.12); color:#cbd5e1;">غير مفعل</span>
                                            @endif
                                        </td>
                                        <td style="padding:.85rem .75rem; border-bottom:1px solid rgba(148,163,184,.10);">
                                            <div style="display:flex; gap:.35rem; justify-content:flex-end; flex-wrap:wrap;">
                                                <button class="action-btn" wire:click="moveSlideUp({{ $idx }})" type="button">⬆</button>
                                                <button class="action-btn" wire:click="moveSlideDown({{ $idx }})" type="button">⬇</button>
                                                <button class="action-btn" wire:click="editSlide({{ $idx }})" type="button">تعديل</button>
                                                <button class="action-btn" wire:click="deleteSlide({{ $idx }})" type="button" style="border-color: rgba(239, 68, 68, 0.3); color:#fca5a5; background: rgba(239, 68, 68, 0.08);">حذف</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="padding: 1rem; text-align:center; color:#94a3b8; font-size:.875rem;">
                                            لا توجد شرائح حتى الآن. قم بإضافة أول شريحة من الأعلى.
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
