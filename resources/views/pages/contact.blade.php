@extends('layouts.site')

@section('content')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .contact-fade { animation: fadeInUp .6s ease-out both; }
    .contact-delay-1 { animation-delay: 80ms; }
    .contact-delay-2 { animation-delay: 160ms; }
    .contact-delay-3 { animation-delay: 240ms; }
    @media (prefers-reduced-motion: reduce) {
        .contact-fade { animation: none; opacity: 1; transform: none; }
    }
</style>

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-l from-[#2c004d] via-[#3d195c] to-[#2c004d]/90 text-white">
    <div class="absolute inset-0 opacity-15" style="background-image: radial-gradient(rgba(255,255,255,.3) 1px, transparent 1px); background-size: 24px 24px;"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 text-xs font-bold px-4 py-2 rounded-full bg-white/10 border border-white/20 mb-6 contact-fade">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>نحن هنا لمساعدتك</span>
            </div>
            <h1 class="text-3xl md:text-5xl font-extrabold leading-tight contact-fade contact-delay-1">
                الاتصال بنا
            </h1>
            <p class="mt-4 text-lg text-white/90 leading-relaxed contact-fade contact-delay-2">
                للاستفسارات والدعم الفني، نرحب بتواصلك معنا عبر أي من القنوات التالية. فريقنا جاهز لمساعدتك.
            </p>
        </div>
    </div>
</section>

{{-- Contact Cards --}}
<section class="bg-white">
    <div class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 text-xs font-bold px-3 py-1.5 rounded-full bg-[#2c004d]/10 text-[#2c004d] mb-4">
                قنوات التواصل
            </div>
            <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900">تواصل معنا</h2>
            <p class="mt-3 text-slate-600 max-w-2xl mx-auto">
                اختر الطريقة الأنسب لك للتواصل مع فريقنا.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <a href="{{ route('site.support') }}" class="group rounded-2xl border-2 border-slate-200 bg-white p-8 hover:border-[#2c004d]/30 hover:shadow-xl transition-all text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-[#2c004d]/10 flex items-center justify-center group-hover:bg-[#2c004d]/20 transition mb-5">
                    <svg class="w-8 h-8 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900">المساعدة والدعم</h3>
                <p class="mt-3 text-slate-600">الأسئلة الشائعة والدعم الفني والاستفسارات العامة</p>
                <span class="mt-4 inline-flex items-center gap-2 text-[#2c004d] font-bold group-hover:underline">
                    اذهب للدعم
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </a>

            <a href="{{ route('site.messages') }}" class="group rounded-2xl border-2 border-slate-200 bg-white p-8 hover:border-[#2c004d]/30 hover:shadow-xl transition-all text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-[#2c004d]/10 flex items-center justify-center group-hover:bg-[#2c004d]/20 transition mb-5">
                    <svg class="w-8 h-8 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900">الرسائل</h3>
                <p class="mt-3 text-slate-600">تواصل مباشر مع فريق الدعم عبر نظام الرسائل</p>
                <span class="mt-4 inline-flex items-center gap-2 text-[#2c004d] font-bold group-hover:underline">
                    فتح الرسائل
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </a>

            <div class="rounded-2xl border-2 border-slate-200 bg-white p-8 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-[#2c004d]/10 flex items-center justify-center mb-5">
                    <svg class="w-8 h-8 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900">البريد الإلكتروني</h3>
                <p class="mt-3 text-slate-600">للتواصل عبر البريد الإلكتروني</p>
                @php $contactDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com'; @endphp
                <a href="mailto:support@{{ $contactDomain }}" class="mt-4 inline-block text-[#2c004d] font-bold hover:underline break-all">
                    support@{{ $contactDomain }}
                </a>
            </div>
        </div>

        <div class="mt-16 text-center">
            <a href="{{ route('site.support') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-[#2c004d] text-white font-extrabold hover:bg-[#2c004d]/95 transition shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                المساعدة والدعم
            </a>
        </div>

        {{-- Google Map --}}
        <div class="mt-16">
            <div class="flex items-center justify-between gap-3 mb-6">
                <div>
                    <div class="inline-flex items-center gap-2 text-xs font-bold px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 mb-3">موقعنا</div>
                    <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">الموقع على الخريطة</h2>
                    <p class="text-sm text-slate-600 mt-1">اكتشف موقعنا وتواصل معنا بسهولة</p>
                </div>
                <a href="https://maps.app.goo.gl/DrD8gM9hZDXVPx697?g_st=ac" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-50 hover:border-[#2c004d]/30 transition shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    فتح في خرائط جوجل
                </a>
            </div>
            <a href="https://maps.app.goo.gl/DrD8gM9hZDXVPx697?g_st=ac" target="_blank" rel="noopener noreferrer" class="block rounded-3xl overflow-hidden border-2 border-slate-200 shadow-lg hover:border-[#2c004d]/40 hover:shadow-xl transition-all group" style="direction: ltr;">
                <div class="relative h-[350px] md:h-[400px] bg-slate-100">
                    {{-- خريطة OpenStreetMap - مجانية ولا تظهر أخطاء. لتحديث الموقع: غيّر bbox و marker بالإحداثيات من خرائط جوجل --}}
                    <iframe
                        src="https://www.openstreetmap.org/export/embed.html?bbox=31.18%2C30.02%2C31.28%2C30.08&layer=mapnik&marker=30.05%2C31.23"
                        width="100%"
                        height="100%"
                        style="border:0; min-height: 350px;"
                        loading="lazy"
                        title="موقعنا على الخريطة"
                        class="w-full h-full"
                    ></iframe>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent flex items-end justify-center pb-8 pointer-events-none">
                        <span class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white/95 text-slate-800 font-bold shadow-lg group-hover:bg-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            اضغط لفتح الموقع على خرائط جوجل
                        </span>
                    </div>
                </div>
            </a>
            <p class="mt-3 text-center text-sm text-slate-500">
                <a href="https://maps.app.goo.gl/DrD8gM9hZDXVPx697?g_st=ac" target="_blank" rel="noopener noreferrer" class="text-[#2c004d] font-bold hover:underline">عرض الموقع الكامل على خرائط جوجل</a>
            </p>
        </div>
    </div>
</section>

{{-- Info Section --}}
<section class="bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-bold px-3 py-1.5 rounded-full bg-[#2c004d]/10 text-[#2c004d] mb-4">
                    أوقات الاستجابة
                </div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900">نحن هنا لمساعدتك</h2>
                <p class="mt-4 text-slate-600 leading-relaxed">
                    نسعى للرد على استفساراتك في أسرع وقت ممكن. يمكنك أيضاً تصفح قسم المساعدة والدعم للعثور على إجابات للأسئلة الشائعة.
                </p>
                <div class="mt-6 flex flex-wrap gap-4">
                    <a href="{{ route('site.about') }}" class="inline-flex items-center gap-2 text-[#2c004d] font-bold hover:underline">
                        من نحن
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ url('/courses') }}" class="inline-flex items-center gap-2 text-[#2c004d] font-bold hover:underline">
                        تصفّح الدورات
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="rounded-3xl border-2 border-slate-200 bg-white p-8 md:p-10">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-[#2c004d]/10 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900">نصيحة سريعة</h3>
                        <p class="mt-2 text-slate-600 text-sm leading-relaxed">
                            إذا كنت مسجلاً في المنصة، يمكنك استخدام نظام الرسائل للتواصل المباشر مع فريق الدعم والحصول على رد أسرع.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
