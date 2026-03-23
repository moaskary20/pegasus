@extends('layouts.site')

@section('content')
<section class="min-h-[70vh] bg-slate-50 py-12 md:py-20">
    <div class="max-w-md mx-auto px-4">
        <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 md:p-8 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-xl bg-[#2c004d]/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-extrabold text-slate-900">استرجاع كلمة المرور</h1>
            </div>

            <p class="text-slate-600 mb-6">
                أدخل بريدك الإلكتروني وسنرسل لك رابطاً لتغيير كلمة المرور.
            </p>

            @if (session('status'))
                <div class="mb-4 p-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-700 text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('site.auth.forgot-password.send') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-bold text-slate-700 mb-1">البريد الإلكتروني</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none transition"
                            placeholder="example@email.com">
                    </div>
                </div>
                <button type="submit" class="mt-6 w-full py-3 rounded-xl bg-[#2c004d] text-white font-extrabold hover:bg-[#2c004d]/95 transition">
                    إرسال رابط الاسترجاع
                </button>
            </form>

            <a href="{{ route('site.auth') }}" class="inline-block mt-4 text-sm text-[#2c004d] hover:underline font-medium">
                العودة لتسجيل الدخول
            </a>
        </div>
    </div>
</section>
@endsection
