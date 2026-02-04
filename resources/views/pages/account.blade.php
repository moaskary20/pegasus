@extends('layouts.site')

@section('content')
@php
    $user = auth()->user();
    $brand = '#2c004d';
@endphp

<section class="max-w-5xl mx-auto px-4 py-10" style="direction: rtl;">
    {{-- Header --}}
    <div class="mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#2c004d]/10 text-[#2c004d] text-sm font-bold mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            الإعدادات
        </div>
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">إعدادات الحساب</h1>
        <p class="text-sm text-slate-600 mt-1">إدارة معلومات حسابك الشخصي وإعدادات الخصوصية</p>
    </div>

    {{-- Notifications --}}
    @if(session('notice'))
        <div class="mb-6 rounded-2xl border px-4 py-3 text-sm font-semibold {{ session('notice.type') === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
            {{ session('notice.message') }}
        </div>
    @endif

    {{-- Tabs Navigation --}}
    <div class="flex flex-wrap gap-2 mb-6 border-b border-slate-200 pb-1">
        <button onclick="showTab('profile')" class="tab-btn active px-4 py-2.5 rounded-t-lg font-semibold text-sm transition-colors border-b-2 border-[#2c004d] text-[#2c004d] bg-[#2c004d]/5" data-tab="profile">
            البيانات الشخصية
        </button>
        <button onclick="showTab('security')" class="tab-btn px-4 py-2.5 rounded-t-lg font-semibold text-sm transition-colors border-b-2 border-transparent text-slate-600 hover:text-[#2c004d]" data-tab="security">
            الأمان
        </button>
        <button onclick="showTab('privacy')" class="tab-btn px-4 py-2.5 rounded-t-lg font-semibold text-sm transition-colors border-b-2 border-transparent text-slate-600 hover:text-[#2c004d]" data-tab="privacy">
            الخصوصية
        </button>
        <button onclick="showTab('notifications')" class="tab-btn px-4 py-2.5 rounded-t-lg font-semibold text-sm transition-colors border-b-2 border-transparent text-slate-600 hover:text-[#2c004d]" data-tab="notifications">
            الإشعارات
        </button>
    </div>

    {{-- Tab: Profile --}}
    <div id="tab-profile" class="tab-content">
        <div class="rounded-3xl border bg-white overflow-hidden shadow-sm">
            <div class="px-6 py-4 bg-slate-50 border-b">
                <h2 class="font-bold text-slate-900">البيانات الشخصية</h2>
                <p class="text-xs text-slate-600 mt-1">قم بتحديث معلوماتك الشخصية والصورة الشخصية</p>
            </div>
            
            <form method="POST" action="{{ route('site.account.update') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')
                
                {{-- Avatar --}}
                <div class="flex items-center gap-6 mb-8">
                    <div class="relative">
                        <div class="w-24 h-24 rounded-full bg-slate-100 overflow-hidden flex items-center justify-center border-2 border-slate-200">
                            @if($user && $user->avatar)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
                            @else
                                <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            @endif
                        </div>
                        <label class="absolute -bottom-1 -left-1 bg-[#2c004d] text-white p-1.5 rounded-full cursor-pointer hover:bg-[#2c004d]/90 transition shadow-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <input type="file" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">
                        </label>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900">الصورة الشخصية</h3>
                        <p class="text-xs text-slate-500 mt-1">JPG, GIF أو PNG. الحجم الأقصى 2MB</p>
                    </div>
                </div>

                {{-- Form Fields --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">الاسم الكامل</label>
                        <input type="text" name="name" value="{{ old('name', $user?->name) }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                            placeholder="أدخل اسمك الكامل">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email', $user?->email) }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                            placeholder="أدخل بريدك الإلكتروني">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">رقم الهاتف</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user?->phone) }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                            placeholder="أدخل رقم هاتفك">
                        @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- City --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">المدينة</label>
                        <input type="text" name="city" value="{{ old('city', $user?->city) }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                            placeholder="أدخل مدينتك">
                        @error('city')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Job --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">المهنة</label>
                        <input type="text" name="job" value="{{ old('job', $user?->job) }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                            placeholder="أدخل مهنتك">
                        @error('job')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Skills --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">المهارات (مفصلة بفواصل)</label>
                        <input type="text" name="skills" value="{{ old('skills', $user && is_array($user->skills) ? implode(', ', $user->skills) : '') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                            placeholder="مثال: PHP, Laravel, MySQL">
                        @error('skills')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Academic History --}}
                <div class="mt-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">التاريخ العلمي</label>
                    <textarea name="academic_history" rows="5"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                        placeholder="المؤهلات والشهادات والخبرات التعليمية">{{ old('academic_history', $user?->academic_history) }}</textarea>
                    <p class="text-xs text-slate-500 mt-1">يظهر في صفحة ملفك الشخصي كمدرب (اختياري)</p>
                    @error('academic_history')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Interests --}}
                <div class="mt-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">اهتماماتك</label>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $allInterests = ['البرمجة', 'التصميم', 'التسويق', 'الأعمال', 'الصحة', 'الرياضة', 'اللغات', 'الطبخ', 'الموسيقى', 'القراءة', 'السفر', 'الألعاب'];
                            $userInterests = $user && is_array($user->interests) ? $user->interests : [];
                        @endphp
                        @foreach($allInterests as $interest)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="interests[]" value="{{ $interest }}" class="hidden peer"
                                    {{ in_array($interest, old('interests', $userInterests)) ? 'checked' : '' }}>
                                <span class="inline-block px-4 py-2 rounded-full border border-slate-200 text-sm text-slate-600 peer-checked:bg-[#2c004d] peer-checked:text-white peer-checked:border-[#2c004d] transition">
                                    {{ $interest }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Submit --}}
                <div class="mt-8 flex items-center justify-end gap-4">
                    <button type="submit" class="px-6 py-3 rounded-xl bg-[#2c004d] text-white font-bold hover:bg-[#2c004d]/90 transition shadow-lg shadow-[#2c004d]/20">
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tab: Security --}}
    <div id="tab-security" class="tab-content hidden">
        <div class="rounded-3xl border bg-white overflow-hidden shadow-sm">
            <div class="px-6 py-4 bg-slate-50 border-b">
                <h2 class="font-bold text-slate-900">الأمان</h2>
                <p class="text-xs text-slate-600 mt-1">إدارة كلمة المرور وإعدادات الأمان</p>
            </div>
            
            <form method="POST" action="{{ route('site.account.password') }}" class="p-6">
                @csrf
                @method('PUT')
                
                {{-- Current Password --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">كلمة المرور الحالية</label>
                    <input type="password" name="current_password" required
                        class="w-full md:w-96 px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                        placeholder="أدخل كلمة المرور الحالية">
                    @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- New Password --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">كلمة المرور الجديدة</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full md:w-96 px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                        placeholder="أدخل كلمة المرور الجديدة (الحد الأدنى 8 أحرف)">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Confirm Password --}}
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full md:w-96 px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                        placeholder="أعد إدخال كلمة المرور الجديدة">
                </div>

                {{-- Submit --}}
                <div class="flex items-center justify-end gap-4">
                    <button type="submit" class="px-6 py-3 rounded-xl bg-[#2c004d] text-white font-bold hover:bg-[#2c004d]/90 transition shadow-lg shadow-[#2c004d]/20">
                        تحديث كلمة المرور
                    </button>
                </div>
            </form>
        </div>

        {{-- Two Factor Authentication --}}
        <div class="rounded-3xl border bg-white overflow-hidden shadow-sm mt-6">
            <div class="px-6 py-4 bg-slate-50 border-b">
                <h2 class="font-bold text-slate-900">المصادقة الثنائية (2FA)</h2>
                <p class="text-xs text-slate-600 mt-1">أضف طبقة أمان إضافية لحسابك</p>
            </div>
            
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-900">حالة المصادقة الثنائية</h3>
                        <p class="text-sm text-slate-500 mt-1">لم يتم تفعيل المصادقة الثنائية بعد</p>
                    </div>
                    <button type="button" class="px-4 py-2 rounded-xl border border-[#2c004d] text-[#2c004d] font-semibold hover:bg-[#2c004d]/5 transition">
                        تفعيل الآن
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab: Privacy --}}
    <div id="tab-privacy" class="tab-content hidden">
        <div class="rounded-3xl border bg-white overflow-hidden shadow-sm">
            <div class="px-6 py-4 bg-slate-50 border-b">
                <h2 class="font-bold text-slate-900">الخصوصية</h2>
                <p class="text-xs text-slate-600 mt-1">تحكم في رؤية بياناتك ومعلوماتك</p>
            </div>
            
            <div class="p-6 space-y-6">
                {{-- Profile Visibility --}}
                <div class="flex items-center justify-between py-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-semibold text-slate-900">ظهور الملف الشخصي</h3>
                        <p class="text-sm text-slate-500 mt-1">تحديد من يمكنه رؤية ملفك الشخصي</p>
                    </div>
                    <select class="px-4 py-2 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none bg-white">
                        <option value="public">الجميع</option>
                        <option value="students">الطلاب فقط</option>
                        <option value="private">لا أحد</option>
                    </select>
                </div>

                {{-- Show Email --}}
                <div class="flex items-center justify-between py-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-semibold text-slate-900">عرض البريد الإلكتروني</h3>
                        <p class="text-sm text-slate-500 mt-1">السماح للآخرين برؤية بريدك الإلكتروني</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                    </label>
                </div>

                {{-- Show Progress --}}
                <div class="flex items-center justify-between py-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-semibold text-slate-900">عرض تقدم الدورات</h3>
                        <p class="text-sm text-slate-500 mt-1">السماح للآخرين برؤية دوراتك والتقدم فيها</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                    </label>
                </div>

                {{-- Show Certificates --}}
                <div class="flex items-center justify-between py-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-semibold text-slate-900">عرض الشهادات</h3>
                        <p class="text-sm text-slate-500 mt-1">السماح للآخرين برؤية شهاداتك</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                    </label>
                </div>

                {{-- Search Engine Indexing --}}
                <div class="flex items-center justify-between py-4">
                    <div>
                        <h3 class="font-semibold text-slate-900">الفهرسة في محركات البحث</h3>
                        <p class="text-sm text-slate-500 mt-1">السماح لمحركات البحث بعرض ملفك الشخصي</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Delete Account --}}
        <div class="rounded-3xl border border-red-200 bg-red-50 overflow-hidden shadow-sm mt-6">
            <div class="px-6 py-4 bg-red-100 border-b border-red-200">
                <h2 class="font-bold text-red-800">حذف الحساب</h2>
                <p class="text-xs text-red-600 mt-1">هذه العملية لا يمكن التراجع عنها</p>
            </div>
            
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-red-900">حذف حسابك نهائياً</h3>
                        <p class="text-sm text-red-700 mt-1">ستفقد جميع بياناتك والدورات المسجل فيها والشهادات</p>
                    </div>
                    <button type="button" class="px-4 py-2 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition">
                        حذف الحساب
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab: Notifications --}}
    <div id="tab-notifications" class="tab-content hidden">
        <div class="rounded-3xl border bg-white overflow-hidden shadow-sm">
            <div class="px-6 py-4 bg-slate-50 border-b">
                <h2 class="font-bold text-slate-900">إعدادات الإشعارات</h2>
                <p class="text-xs text-slate-600 mt-1">تحكم في الإشعارات التي تستلمها</p>
            </div>
            
            <div class="p-6 space-y-6">
                {{-- Email Notifications --}}
                <div class="py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-900 mb-4">إشعارات البريد الإلكتروني</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-slate-700">دورات جديدة</h4>
                                <p class="text-xs text-slate-500">إشعار عند إضافة دورات جديدة في اهتماماتك</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-slate-700">عروض وخصومات</h4>
                                <p class="text-xs text-slate-500">إشعار عند توفر عروض خاصة على الدورات</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-slate-700">نشاط الدورات</h4>
                                <p class="text-xs text-slate-500">إشعار عند إضافة دروس جديدة في دوراتك</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-slate-700">شهادات وجوائز</h4>
                                <p class="text-xs text-slate-500">إشعار عند الحصول على شهادة أو جائزة</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-slate-700">النشرة الأسبوعية</h4>
                                <p class="text-xs text-slate-500">تلخيص أسبوعي لأحدث الدورات والمقالات</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Push Notifications --}}
                <div class="py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-900 mb-4">الإشعارات الفورية (Push)</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-slate-700">رسائل جديدة</h4>
                                <p class="text-xs text-slate-500">إشعار عند وصول رسالة جديدة</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-slate-700">إشعارات المنصة</h4>
                                <p class="text-xs text-slate-500">إشعارات مهمة من إدارة المنصة</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-slate-700">تذكيرات التعلم</h4>
                                <p class="text-xs text-slate-500">تذكير بمواصلة الدورات المتروكة</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- SMS Notifications --}}
                <div class="py-4">
                    <h3 class="font-semibold text-slate-900 mb-4">إشعارات الرسائل القصيرة (SMS)</h3>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-slate-700">رمز التحقق</h4>
                            <p class="text-xs text-slate-500">استلام رموز التحقق عبر الرسائل القصيرة</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-[#2c004d]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2c004d]"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Tab Script --}}
<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'border-[#2c004d]', 'text-[#2c004d]', 'bg-[#2c004d]/5');
        btn.classList.add('border-transparent', 'text-slate-600');
    });
    
    // Show selected tab content
    document.getElementById('tab-' + tabName).classList.remove('hidden');
    
    // Add active class to selected button
    const activeBtn = document.querySelector('[data-tab="' + tabName + '"]');
    activeBtn.classList.add('active', 'border-[#2c004d]', 'text-[#2c004d]', 'bg-[#2c004d]/5');
    activeBtn.classList.remove('border-transparent', 'text-slate-600');
}
</script>

<style>
.tab-btn.active {
    border-bottom-color: #2c004d;
    background-color: rgba(44, 0, 77, 0.05);
}
</style>
@endsection
