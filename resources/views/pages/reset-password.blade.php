@extends('layouts.site')

@section('content')
<section class="min-h-[70vh] bg-slate-50 py-12 md:py-20">
    <div class="max-w-md mx-auto px-4">
        <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 md:p-8 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-xl bg-[#2c004d]/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-extrabold text-slate-900">تغيير كلمة المرور</h1>
            </div>

            <p class="text-slate-600 mb-6">
                أدخل كلمة المرور الجديدة أدناه.
            </p>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-700 text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('site.auth.reset-password') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="space-y-4">
                    <div>
                        <label for="email_display" class="block text-sm font-bold text-slate-700 mb-1">البريد الإلكتروني</label>
                        <input type="email" id="email_display" value="{{ $email }}" readonly
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-bold text-slate-700 mb-1">كلمة المرور الجديدة</label>
                        <input type="password" name="password" id="password" required minlength="8"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                            placeholder="••••••••"
                            autocomplete="new-password">
                        <p class="text-xs text-slate-500 mt-1">8 أحرف على الأقل، حرف كبير، وأرقام</p>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold text-slate-700 mb-1">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                            placeholder="••••••••"
                            autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="mt-6 w-full py-3 rounded-xl bg-[#2c004d] text-white font-extrabold hover:bg-[#2c004d]/95 transition">
                    تغيير كلمة المرور
                </button>
            </form>

            <a href="{{ route('site.auth') }}" class="inline-block mt-4 text-sm text-[#2c004d] hover:underline font-medium">
                العودة لتسجيل الدخول
            </a>
        </div>
    </div>
</section>
@endsection
