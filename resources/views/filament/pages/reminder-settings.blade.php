<x-filament-panels::page>
    <style>
        .settings-container { max-width: 800px; margin: 0 auto; }
        
        .settings-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.3);
            position: relative;
            overflow: hidden;
        }
        .settings-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        }
        .header-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
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
        
        .header-actions { display: flex; gap: 12px; }
        .header-btn {
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .header-btn.enable { background: rgba(255,255,255,0.2); color: white; }
        .header-btn.enable:hover { background: rgba(255,255,255,0.3); }
        .header-btn.disable { background: rgba(0,0,0,0.2); color: white; }
        .header-btn.disable:hover { background: rgba(0,0,0,0.3); }
        
        .settings-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .settings-card-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
        }
        .settings-card-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; }
        .settings-card-desc { font-size: 13px; color: #6b7280; margin: 4px 0 0 0; }
        
        .setting-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }
        .setting-row:last-child { border-bottom: none; }
        .setting-row:hover { background: #fafafa; }
        
        .setting-info { display: flex; align-items: center; gap: 16px; }
        .setting-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .setting-icon.purple { background: linear-gradient(135deg, #ede9fe, #ddd6fe); }
        .setting-icon.blue { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }
        .setting-icon.green { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
        .setting-icon.orange { background: linear-gradient(135deg, #ffedd5, #fed7aa); }
        .setting-icon.teal { background: linear-gradient(135deg, #ccfbf1, #99f6e4); }
        .setting-icon.yellow { background: linear-gradient(135deg, #fef9c3, #fef08a); }
        .setting-icon.red { background: linear-gradient(135deg, #fee2e2, #fecaca); }
        .setting-icon.indigo { background: linear-gradient(135deg, #e0e7ff, #c7d2fe); }
        
        .setting-text { flex: 1; }
        .setting-label { font-size: 15px; font-weight: 600; color: #1f2937; margin: 0; }
        .setting-desc { font-size: 12px; color: #6b7280; margin: 4px 0 0 0; }
        
        .setting-controls { display: flex; align-items: center; gap: 24px; }
        .control-group { display: flex; align-items: center; gap: 10px; }
        .control-label { font-size: 12px; color: #6b7280; }
        
        .toggle {
            position: relative;
            width: 50px;
            height: 28px;
            background: #d1d5db;
            border-radius: 14px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .toggle.active { background: #6366f1; }
        .toggle::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 22px;
            height: 22px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }
        .toggle.active::after { transform: translateX(22px); }
        
        .toggle.email { background: #e5e7eb; width: 44px; height: 24px; }
        .toggle.email::after { width: 18px; height: 18px; }
        .toggle.email.active { background: #10b981; }
        .toggle.email.active::after { transform: translateX(20px); }
        
        .info-box {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 16px 20px;
            margin-top: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .info-icon { font-size: 20px; flex-shrink: 0; }
        .info-text { font-size: 13px; color: #1e40af; line-height: 1.6; }
        
        @media (prefers-color-scheme: dark) {
            .settings-card { background: #1f2937; border-color: #374151; }
            .settings-card-header { background: linear-gradient(135deg, #1f2937, #374151); border-color: #374151; }
            .settings-card-title, .setting-label { color: #f9fafb; }
            .setting-row { border-color: #374151; }
            .setting-row:hover { background: #374151; }
        }
    </style>

    @php
        $settings = $this->settings;
    @endphp

    <div class="settings-container">
        {{-- Header --}}
        <div class="settings-header">
            <div class="header-content">
                <div class="header-info">
                    <div class="header-icon">⚙️</div>
                    <div class="header-text">
                        <h1>إعدادات التذكيرات</h1>
                        <p>تحكم في أنواع التذكيرات التي تريد استلامها</p>
                    </div>
                </div>
                
                <div class="header-actions">
                    <button class="header-btn enable" wire:click="enableAll">
                        تفعيل الكل
                    </button>
                    <button class="header-btn disable" wire:click="disableAll">
                        إيقاف الكل
                    </button>
                </div>
            </div>
        </div>
        
        {{-- Settings Card --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <h3 class="settings-card-title">أنواع التذكيرات</h3>
                <p class="settings-card-desc">اختر التذكيرات التي تريد عرضها وإرسالها بالبريد الإلكتروني</p>
            </div>
            
            @foreach($settings as $type => $setting)
                <div class="setting-row">
                    <div class="setting-info">
                        <div class="setting-icon {{ $setting['color'] }}">
                            {{ $setting['icon'] }}
                        </div>
                        <div class="setting-text">
                            <p class="setting-label">{{ $setting['label'] }}</p>
                            <p class="setting-desc">
                                @switch($type)
                                    @case('quiz')
                                        تذكيرات بالاختبارات المعلقة التي لم تجتازها
                                        @break
                                    @case('message')
                                        إشعار عند وجود رسائل غير مقروءة
                                        @break
                                    @case('lesson')
                                        تذكيرات بإكمال الدورات الغير منتهية
                                        @break
                                    @case('coupon')
                                        تنبيهات بالكوبونات التي تقارب على الانتهاء
                                        @break
                                    @case('certificate')
                                        تذكير بتحميل الشهادات للدورات المكتملة
                                        @break
                                    @case('rating')
                                        تذكير بتقييم الدورات التي أكملتها
                                        @break
                                    @case('question')
                                        تنبيه بالأسئلة التي تنتظر إجابتك
                                        @break
                                    @case('new_course')
                                        إشعارات بالدورات الجديدة المناسبة لك
                                        @break
                                @endswitch
                            </p>
                        </div>
                    </div>
                    
                    <div class="setting-controls">
                        <div class="control-group">
                            <span class="control-label">عرض</span>
                            <div class="toggle {{ $setting['enabled'] ? 'active' : '' }}" wire:click="toggleEnabled('{{ $type }}')"></div>
                        </div>
                        <div class="control-group">
                            <span class="control-label">بريد</span>
                            <div class="toggle email {{ $setting['email_enabled'] ? 'active' : '' }}" wire:click="toggleEmail('{{ $type }}')"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="info-box">
            <span class="info-icon">💡</span>
            <p class="info-text">
                يتم فحص التذكيرات تلقائياً وعرضها في أعلى الصفحة. يمكنك أيضاً تفعيل إرسال التذكيرات عبر البريد الإلكتروني لتتلقاها حتى عندما لا تكون متصلاً بالمنصة.
            </p>
        </div>
    </div>
</x-filament-panels::page>
