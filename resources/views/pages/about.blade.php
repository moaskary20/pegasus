@extends('layouts.site')

@section('content')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .about-fade { animation: fadeInUp .6s ease-out both; }
    .about-delay-1 { animation-delay: 80ms; }
    .about-delay-2 { animation-delay: 160ms; }
    .about-delay-3 { animation-delay: 240ms; }
    .about-delay-4 { animation-delay: 320ms; }
    @media (prefers-reduced-motion: reduce) {
        .about-fade { animation: none; opacity: 1; transform: none; }
    }
</style>

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-l from-[#2c004d] via-[#3d195c] to-[#2c004d]/90 text-white">
    <div class="absolute inset-0 opacity-15" style="background-image: radial-gradient(rgba(255,255,255,.3) 1px, transparent 1px); background-size: 24px 24px;"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 text-xs font-bold px-4 py-2 rounded-full bg-white/10 border border-white/20 mb-6 about-fade">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>قصتنا ورؤيتنا</span>
            </div>
            <h1 class="text-3xl md:text-5xl font-extrabold leading-tight about-fade about-delay-1">
                من نحن
            </h1>
            <p class="mt-4 text-lg text-white/90 leading-relaxed about-fade about-delay-2">
                منصة تعليمية عربية احترافية تهدف إلى تمكين المتعلمين من الوصول إلى أفضل المحتوى التعليمي — تعلّم بذكاء وابدأ رحلتك الآن.
            </p>
        </div>
    </div>
</section>

{{-- Mission & Vision --}}
<section class="bg-white">
    <div class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="grid lg:grid-cols-2 gap-12">
            <div class="rounded-3xl border-2 border-slate-100 bg-slate-50/50 p-8 md:p-10">
                <div class="w-14 h-14 rounded-2xl bg-[#2c004d]/10 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">رؤيتنا</h2>
                <p class="mt-4 text-slate-600 leading-relaxed">
                    أن نكون المنصة التعليمية العربية الأولى التي تجمع بين الجودة العالية وسهولة الوصول، وتمكين كل متعلم من تحقيق أهدافه بثقة.
                </p>
            </div>
            <div class="rounded-3xl border-2 border-slate-100 bg-slate-50/50 p-8 md:p-10">
                <div class="w-14 h-14 rounded-2xl bg-[#2c004d]/10 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.498 3.498 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.498 3.498 3.42 3.42 0 00-1.946.806 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.498-3.498z"/>
                    </svg>
                </div>
                <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">رسالتنا</h2>
                <p class="mt-4 text-slate-600 leading-relaxed">
                    تقديم تجربة تعليمية متكاملة تجمع الدورات الاحترافية، متابعة التقدم، التواصل المباشر مع المدرسين، ومتجر المنتجات — كل ما تحتاجه في مكان واحد.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Features --}}
<section class="bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 text-xs font-bold px-3 py-1.5 rounded-full bg-[#2c004d]/10 text-[#2c004d] mb-4">
                لماذا نحن؟
            </div>
            <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900">ما الذي يميزنا</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 hover:border-[#2c004d]/20 hover:shadow-lg transition-all">
                <div class="w-12 h-12 rounded-xl bg-[#2c004d]/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="mt-4 font-extrabold text-slate-900">دورات احترافية</h3>
                <p class="mt-2 text-sm text-slate-600">محتوى تعليمي عالي الجودة يقدمه خبراء في مجالاتهم</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 hover:border-[#2c004d]/20 hover:shadow-lg transition-all">
                <div class="w-12 h-12 rounded-xl bg-[#2c004d]/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="mt-4 font-extrabold text-slate-900">متابعة التقدم</h3>
                <p class="mt-2 text-sm text-slate-600">تتبع إنجازاتك وتطورك بسهولة ووضوح</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 hover:border-[#2c004d]/20 hover:shadow-lg transition-all">
                <div class="w-12 h-12 rounded-xl bg-[#2c004d]/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="mt-4 font-extrabold text-slate-900">تواصل مباشر</h3>
                <p class="mt-2 text-sm text-slate-600">رسائل مباشرة مع المدرسين للإجابة على استفساراتك</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 hover:border-[#2c004d]/20 hover:shadow-lg transition-all">
                <div class="w-12 h-12 rounded-xl bg-[#2c004d]/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <h3 class="mt-4 font-extrabold text-slate-900">متجر متكامل</h3>
                <p class="mt-2 text-sm text-slate-600">منتجات تعليمية وموارد إضافية في مكان واحد</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-white">
    <div class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="rounded-3xl bg-gradient-to-l from-[#2c004d] to-[#3d195c] p-8 md:p-12 text-white text-center">
            <h2 class="text-2xl md:text-3xl font-extrabold">ابدأ رحلتك التعليمية الآن</h2>
            <p class="mt-3 text-white/90 max-w-2xl mx-auto">
                انضم إلى آلاف المتعلمين واستفد من دوراتنا الاحترافية.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ url('/courses') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-white text-[#2c004d] font-extrabold hover:bg-white/95 transition shadow-lg">
                    تصفّح الدورات
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="{{ route('site.support') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-white/10 border border-white/25 font-bold hover:bg-white/20 transition">
                    المساعدة والدعم
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
