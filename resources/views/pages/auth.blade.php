@extends('layouts.site')

@section('content')
@php
    $allowRegistration = \App\Models\PlatformSetting::get('allow_registration', true);
    $userTypes = [
        'student' => ['name' => 'طالب', 'desc' => 'لتعلّم الدورات والاستفادة من المحتوى التعليمي'],
        'instructor' => ['name' => 'مدرب', 'desc' => 'لإنشاء دوراتك وتدريس الطلاب وكسب الدخل'],
    ];
@endphp
<style>
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .auth-fade { animation: fadeIn .4s ease-out both; }
</style>

<section class="min-h-[70vh] bg-slate-50 py-12 md:py-20">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-10">
            <h1 class="text-2xl md:text-4xl font-extrabold text-slate-900">تسجيل الدخول أو الاشتراك</h1>
            <p class="mt-2 text-slate-600">اختر الطريقة المناسبة لك</p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            {{-- Login --}}
            <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 md:p-8 shadow-sm auth-fade">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-[#2c004d]/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900">تسجيل الدخول</h2>
                </div>

                @if(session('notice'))
                    <div class="mb-4 p-3 rounded-xl {{ session('notice')['type'] === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} text-sm">{{ session('notice')['message'] ?? '' }}</div>
                @endif

                @if($errors->has('email') && !$errors->has('register'))
                    <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-700 text-sm">{{ $errors->first('email') }}</div>
                @endif

                <form method="POST" action="{{ route('site.auth.login') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="login_email" class="block text-sm font-bold text-slate-700 mb-1">البريد الإلكتروني</label>
                            <input type="email" name="email" id="login_email" value="{{ old('email') }}" required autofocus
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                                placeholder="example@email.com">
                        </div>
                        <div>
                            <label for="login_password" class="block text-sm font-bold text-slate-700 mb-1">كلمة المرور</label>
                            <div class="relative">
                                <input type="password" name="password" id="login_password" required autocomplete="current-password"
                                    class="w-full ps-4 pe-12 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                                    placeholder="••••••••">
                                <button type="button" class="absolute end-2 top-1/2 -translate-y-1/2 p-2 rounded-lg text-slate-500 hover:text-[#2c004d] hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#2c004d]/30"
                                    data-password-toggle="login_password" aria-label="إظهار كلمة المرور">
                                    <svg class="icon-eye w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg class="icon-eye-off w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            <a href="{{ route('site.auth.forgot-password') }}" class="inline-block mt-2 text-sm text-[#2c004d] hover:underline font-medium">
                                نسيت كلمة المرور؟
                            </a>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="remember" id="remember" value="1"
                                class="rounded border-slate-300 text-[#2c004d] focus:ring-[#2c004d]">
                            <label for="remember" class="text-sm text-slate-600">تذكرني</label>
                        </div>
                    </div>
                    <button type="submit" class="mt-6 w-full py-3 rounded-xl bg-[#2c004d] text-white font-extrabold hover:bg-[#2c004d]/95 transition">
                        تسجيل الدخول
                    </button>
                </form>
            </div>

            {{-- Register --}}
            <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 md:p-8 shadow-sm auth-fade">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-[#2c004d]/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900">إنشاء حساب جديد</h2>
                </div>

                @if(!$allowRegistration)
                    <div class="p-4 rounded-xl bg-amber-50 text-amber-800 text-sm">التسجيل معطل حالياً. تواصل معنا للمزيد من المعلومات.</div>
                @else
                    @if($errors->has('register'))
                        <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-700 text-sm">{{ $errors->first('register') }}</div>
                    @endif

                    <form method="POST" action="{{ route('site.auth.register') }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="reg_name" class="block text-sm font-bold text-slate-700 mb-1">الاسم</label>
                                <input type="text" name="name" id="reg_name" value="{{ old('name') }}" required
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                                    placeholder="الاسم الكامل">
                                @error('name')<span class="text-rose-600 text-xs mt-1">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="reg_email" class="block text-sm font-bold text-slate-700 mb-1">البريد الإلكتروني</label>
                                <input type="email" name="email" id="reg_email" value="{{ old('email') }}" required
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                                    placeholder="example@email.com">
                                @error('email')<span class="text-rose-600 text-xs mt-1">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="reg_phone" class="block text-sm font-bold text-slate-700 mb-1">رقم الهاتف</label>
                                <input type="tel" name="phone" id="reg_phone" value="{{ old('phone') }}" required
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                                    placeholder="01012345678"
                                    inputmode="numeric" autocomplete="tel">
                                <p class="text-xs text-slate-500 mt-1">11 رقماً، يبدأ بـ 010 أو 011 أو 012 أو 015</p>
                                @error('phone')<span class="text-rose-600 text-xs mt-1">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="reg_password" class="block text-sm font-bold text-slate-700 mb-1">كلمة المرور</label>
                                <div class="relative">
                                    <input type="password" name="password" id="reg_password" required minlength="8" autocomplete="new-password"
                                        class="w-full ps-4 pe-12 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                                        placeholder="••••••••">
                                    <button type="button" class="absolute end-2 top-1/2 -translate-y-1/2 p-2 rounded-lg text-slate-500 hover:text-[#2c004d] hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#2c004d]/30"
                                        data-password-toggle="reg_password" aria-label="إظهار كلمة المرور">
                                        <svg class="icon-eye w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg class="icon-eye-off w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">8 أحرف على الأقل، حرف كبير، وأرقام</p>
                                @error('password')<span class="text-rose-600 text-xs mt-1">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="reg_password_confirmation" class="block text-sm font-bold text-slate-700 mb-1">تأكيد كلمة المرور</label>
                                <div class="relative">
                                    <input type="password" name="password_confirmation" id="reg_password_confirmation" required autocomplete="new-password"
                                        class="w-full ps-4 pe-12 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                                        placeholder="••••••••">
                                    <button type="button" class="absolute end-2 top-1/2 -translate-y-1/2 p-2 rounded-lg text-slate-500 hover:text-[#2c004d] hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#2c004d]/30"
                                        data-password-toggle="reg_password_confirmation" aria-label="إظهار كلمة المرور">
                                        <svg class="icon-eye w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg class="icon-eye-off w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- User types table --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-3">نوع الحساب</label>
                                <div class="rounded-xl border border-slate-200 overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="bg-slate-50 border-b border-slate-200">
                                                <th class="text-right py-3 px-4 font-extrabold text-slate-700">النوع</th>
                                                <th class="text-right py-3 px-4 font-extrabold text-slate-700">الوصف</th>
                                                <th class="text-center py-3 px-4 font-extrabold text-slate-700 w-20">اختر</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($userTypes as $key => $type)
                                                <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/50">
                                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $type['name'] }}</td>
                                                    <td class="py-3 px-4 text-slate-600">{{ $type['desc'] }}</td>
                                                    <td class="py-3 px-4 text-center">
                                                        <input type="radio" name="user_type" value="{{ $key }}" id="type_{{ $key }}"
                                                            {{ old('user_type', 'student') === $key ? 'checked' : '' }}
                                                            class="rounded-full border-slate-300 text-[#2c004d] focus:ring-[#2c004d]">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @error('user_type')<span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <button type="submit" class="mt-6 w-full py-3 rounded-xl bg-[#2c004d] text-white font-extrabold hover:bg-[#2c004d]/95 transition">
                            إنشاء الحساب
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-password-toggle');
            var input = id ? document.getElementById(id) : null;
            if (!input) return;
            var eye = btn.querySelector('.icon-eye');
            var eyeOff = btn.querySelector('.icon-eye-off');
            if (input.type === 'password') {
                input.type = 'text';
                if (eye) eye.classList.add('hidden');
                if (eyeOff) eyeOff.classList.remove('hidden');
                btn.setAttribute('aria-label', 'إخفاء كلمة المرور');
            } else {
                input.type = 'password';
                if (eye) eye.classList.remove('hidden');
                if (eyeOff) eyeOff.classList.add('hidden');
                btn.setAttribute('aria-label', 'إظهار كلمة المرور');
            }
        });
    });
})();
</script>
@endsection
