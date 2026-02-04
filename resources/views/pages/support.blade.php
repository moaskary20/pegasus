@extends('layouts.site')

@section('content')
@php
    $brand = '#2c004d';
    $faqItems = [
        ['q' => 'كيف أبدأ التعلم على المنصة؟', 'a' => 'سجّل حساباً جديداً، تصفّح الدورات المتاحة، واشترك في الدورة المناسبة. بعد الدفع أو تفعيل الاشتراك، ستتمكن من الوصول لكل محتوى الدورة ومتابعة تقدمك.'],
        ['q' => 'كيف أتابع تقدمي في الدورة؟', 'a' => 'من صفحة "دوراتي" يمكنك رؤية نسبة الإكمال لكل دورة. داخل كل درس يتم حفظ تقدمك تلقائياً، ويمكنك العودة من حيث توقفت.'],
        ['q' => 'كيف أتسلم الواجبات؟', 'a' => 'من قائمة "واجباتي" في القائمة المنسدلة أو من داخل الدرس. اختر الواجب، اقرأ التعليمات، وأرفق إجابتك أو ملفاتك ثم اضغط تسليم.'],
        ['q' => 'كيف أتواصل مع الدعم؟', 'a' => 'يمكنك استخدام نظام الرسائل لبدء محادثة جديدة، أو إرسال شكوى/استفسار من هذه الصفحة، أو التواصل عبر البريد الإلكتروني والهاتف المذكورين أدناه.'],
        ['q' => 'هل يمكنني الحصول على شهادة؟', 'a' => 'نعم، بعد إكمال جميع دروس الدورة بنجاح، ستظهر لك إمكانية تحميل الشهادة من صفحة الدورة.'],
        ['q' => 'كيف أضيف دورة إلى قائمة الرغبات؟', 'a' => 'اضغط على أيقونة القلب على كارت الدورة أو المنتج. يمكنك الوصول لقائمة الرغبات من القائمة المنسدلة.'],
    ];
@endphp

<style>
    @keyframes support-fadeInUp {
        from { opacity: 0; transform: translateY(24px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes support-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }
    @keyframes support-shine {
        0% { background-position: -200% center; }
        100% { background-position: 200% center; }
    }
    .support-reveal { animation: support-fadeInUp .6s ease-out both; }
    .support-delay-1 { animation-delay: 80ms; }
    .support-delay-2 { animation-delay: 160ms; }
    .support-delay-3 { animation-delay: 240ms; }
    .support-delay-4 { animation-delay: 320ms; }
    .support-float { animation: support-float 4s ease-in-out infinite; }
    .support-card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .support-card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgba(44, 0, 77, 0.15); }
    .support-accordion-item { transition: all 0.3s ease; }
    .support-accordion-item:hover { background: rgba(44, 0, 77, 0.02); }
    @media (prefers-reduced-motion: reduce) {
        .support-reveal, .support-float { animation: none; opacity: 1; transform: none; }
    }
</style>

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-br from-[#2c004d] via-[#3d195c] to-[#2c004d] text-white">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(rgba(255,255,255,.4) 1px, transparent 1px); background-size: 32px 32px;"></div>
    <div class="absolute top-0 left-0 w-96 h-96 rounded-full bg-white/5 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 rounded-full bg-white/5 translate-x-1/2 translate-y-1/2"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 text-xs font-bold px-4 py-2 rounded-full bg-white/10 border border-white/20 mb-6 support-reveal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>نحن هنا لمساعدتك</span>
            </div>
            <h1 class="text-3xl md:text-5xl font-extrabold leading-tight support-reveal support-delay-1">
                المساعدة والدعم
            </h1>
            <p class="mt-4 text-lg text-white/90 leading-relaxed support-reveal support-delay-2">
                تصفّح الأسئلة الشائعة، تقدّم بشكواك، أو تواصل معنا مباشرة. فريقنا جاهز لمساعدتك على الاستفادة القصوى من المنصة.
            </p>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-12 md:py-16" style="direction: rtl;">
    @if(session('notice'))
        <div class="mb-8 rounded-2xl border px-4 py-3 text-sm font-semibold {{ (session('notice')['type'] ?? '') === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }} support-reveal">
            {{ session('notice')['message'] ?? '' }}
        </div>
    @endif

    {{-- Quick Actions --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-16">
        <a href="#faq" class="support-reveal support-delay-1 support-card-hover rounded-2xl border-2 border-slate-200 bg-white p-6 flex items-center gap-4 group">
            <div class="w-14 h-14 rounded-2xl bg-[#2c004d]/10 flex items-center justify-center group-hover:bg-[#2c004d]/20 transition">
                <svg class="w-7 h-7 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="font-extrabold text-slate-900">الأسئلة الشائعة</div>
                <div class="text-xs text-slate-500 mt-0.5">إجابات سريعة</div>
            </div>
        </a>
        <a href="#complaint" class="support-reveal support-delay-2 support-card-hover rounded-2xl border-2 border-slate-200 bg-white p-6 flex items-center gap-4 group">
            <div class="w-14 h-14 rounded-2xl bg-rose-100 flex items-center justify-center group-hover:bg-rose-200 transition">
                <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <div class="font-extrabold text-slate-900">تقديم شكوى</div>
                <div class="text-xs text-slate-500 mt-0.5">نستمع إليك</div>
            </div>
        </a>
        <a href="#contact" class="support-reveal support-delay-3 support-card-hover rounded-2xl border-2 border-slate-200 bg-white p-6 flex items-center gap-4 group">
            <div class="w-14 h-14 rounded-2xl bg-sky-100 flex items-center justify-center group-hover:bg-sky-200 transition">
                <svg class="w-7 h-7 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="font-extrabold text-slate-900">اتصل بنا</div>
                <div class="text-xs text-slate-500 mt-0.5">تواصل مباشر</div>
            </div>
        </a>
        @auth
        <a href="{{ route('site.messages.new') }}" class="support-reveal support-delay-4 support-card-hover rounded-2xl border-2 border-[#2c004d]/30 bg-[#2c004d]/5 p-6 flex items-center gap-4 group">
            <div class="w-14 h-14 rounded-2xl bg-[#2c004d]/20 flex items-center justify-center group-hover:bg-[#2c004d]/30 transition">
                <svg class="w-7 h-7 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div>
                <div class="font-extrabold text-[#2c004d]">محادثة جديدة</div>
                <div class="text-xs text-slate-500 mt-0.5">تواصل فوري</div>
            </div>
        </a>
        @else
        <a href="{{ route('site.auth') }}?intended={{ urlencode(route('site.support')) }}" class="support-reveal support-delay-4 support-card-hover rounded-2xl border-2 border-slate-200 bg-white p-6 flex items-center gap-4 group">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center group-hover:bg-slate-200 transition">
                <svg class="w-7 h-7 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div>
                <div class="font-extrabold text-slate-900">محادثة جديدة</div>
                <div class="text-xs text-slate-500 mt-0.5">سجّل الدخول أولاً</div>
            </div>
        </a>
        @endauth
    </div>

    {{-- FAQ Accordion --}}
    <div id="faq" class="mb-20 scroll-mt-24">
        <div class="flex items-center justify-between mb-8">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-bold px-3 py-1.5 rounded-full bg-[#2c004d]/10 text-[#2c004d] mb-3">أسئلة شائعة</div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900">الأسئلة الشائعة</h2>
            </div>
        </div>
        <div class="rounded-3xl border-2 border-slate-200 bg-white overflow-hidden shadow-sm" x-data="{ open: 0, toggle(i){ this.open = (this.open === i ? -1 : i) } }">
            @foreach($faqItems as $i => $item)
                <div class="support-accordion-item border-b border-slate-100 last:border-b-0">
                    <button type="button" class="w-full flex items-center justify-between gap-4 px-6 py-5 text-right" @click="toggle({{ $i }})">
                        <span class="text-base font-extrabold text-slate-900">{{ $item['q'] }}</span>
                        <span class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300" :class="open === {{ $i }} ? 'bg-[#2c004d] text-white' : 'bg-slate-100 text-slate-600'">
                            <svg class="w-5 h-5 transition-transform duration-300" :class="open === {{ $i }} ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                        </span>
                    </button>
                    <div x-show="open === {{ $i }}" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="px-6 pb-5">
                        <div class="text-sm text-slate-600 leading-relaxed pr-14">{{ $item['a'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Complaint Form --}}
    <div id="complaint" class="mb-20 scroll-mt-24">
        <div class="flex items-center justify-between mb-8">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-bold px-3 py-1.5 rounded-full bg-rose-100 text-rose-700 mb-3">تقديم شكوى</div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900">تقدّم بشكوى</h2>
                <p class="mt-2 text-slate-600">نستمع لملاحظاتك ونسعى لحل أي مشكلة تواجهك</p>
            </div>
        </div>
        <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 md:p-8 shadow-sm">
            <form action="{{ route('site.support.complaint') }}" method="POST" class="space-y-5">
                @csrf
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">الاسم <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()?->name ?? '') }}" required
                            class="w-full rounded-2xl border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 px-4 py-3 text-slate-800">
                        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">البريد الإلكتروني <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()?->email ?? '') }}" required
                            class="w-full rounded-2xl border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 px-4 py-3 text-slate-800">
                        @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">رقم الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()?->phone ?? '') }}"
                            class="w-full rounded-2xl border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 px-4 py-3 text-slate-800" dir="ltr">
                        @error('phone')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">موضوع الشكوى <span class="text-rose-500">*</span></label>
                        <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="مثال: مشكلة في تشغيل الفيديو"
                            class="w-full rounded-2xl border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 px-4 py-3 text-slate-800">
                        @error('subject')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">تفاصيل الشكوى <span class="text-rose-500">*</span></label>
                    <textarea name="message" rows="5" required placeholder="اشرح مشكلتك بالتفصيل..."
                        class="w-full rounded-2xl border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 px-4 py-3 text-slate-800 resize-none">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-rose-600 text-white font-extrabold hover:bg-rose-700 transition shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    إرسال الشكوى
                </button>
            </form>
        </div>
    </div>

    {{-- Contact Form + Info --}}
    <div id="contact" class="mb-20 scroll-mt-24">
        <div class="flex items-center justify-between mb-8">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-bold px-3 py-1.5 rounded-full bg-sky-100 text-sky-700 mb-3">اتصل بنا</div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900">اتصل بنا</h2>
                <p class="mt-2 text-slate-600">أرسل استفسارك أو تواصل معنا مباشرة</p>
            </div>
        </div>
        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Contact Form --}}
            <div class="lg:col-span-2 rounded-3xl border-2 border-slate-200 bg-white p-6 md:p-8 shadow-sm">
                <form action="{{ route('site.support.contact') }}" method="POST" class="space-y-5">
                    @csrf
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">الاسم <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()?->name ?? '') }}" required
                                class="w-full rounded-2xl border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 px-4 py-3 text-slate-800">
                            @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">البريد الإلكتروني <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()?->email ?? '') }}" required
                                class="w-full rounded-2xl border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 px-4 py-3 text-slate-800">
                            @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">رقم الهاتف</label>
                            <input type="text" name="phone" value="{{ old('phone', auth()->user()?->phone ?? '') }}"
                                class="w-full rounded-2xl border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 px-4 py-3 text-slate-800" dir="ltr">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">الموضوع <span class="text-rose-500">*</span></label>
                            <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="موضوع رسالتك"
                                class="w-full rounded-2xl border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 px-4 py-3 text-slate-800">
                            @error('subject')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">الرسالة <span class="text-rose-500">*</span></label>
                        <textarea name="message" rows="4" required placeholder="اكتب رسالتك هنا..."
                            class="w-full rounded-2xl border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 px-4 py-3 text-slate-800 resize-none">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-[#2c004d] text-white font-extrabold hover:bg-[#2c004d]/95 transition shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        إرسال الرسالة
                    </button>
                </form>
            </div>
            {{-- Contact Info --}}
            <div class="space-y-6">
                <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 shadow-sm support-card-hover">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-sky-100 flex items-center justify-center">
                            <svg class="w-7 h-7 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-500">البريد الإلكتروني</div>
                            <a href="mailto:{{ $supportEmail }}" class="text-base font-extrabold text-[#2c004d] hover:underline break-all">{{ $supportEmail }}</a>
                        </div>
                    </div>
                </div>
                @if($supportPhone || $supportPhone2)
                <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 shadow-sm support-card-hover">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center">
                            <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-500">أرقام التواصل</div>
                            @if($supportPhone)
                                <a href="tel:{{ $supportPhone }}" class="block text-base font-extrabold text-[#2c004d] hover:underline" dir="ltr">{{ $supportPhone }}</a>
                            @endif
                            @if($supportPhone2)
                                <a href="tel:{{ $supportPhone2 }}" class="block text-base font-extrabold text-[#2c004d] hover:underline mt-1" dir="ltr">{{ $supportPhone2 }}</a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                @auth
                <a href="{{ route('site.messages.new') }}" class="block rounded-3xl border-2 border-[#2c004d]/30 bg-[#2c004d]/5 p-6 support-card-hover group">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-[#2c004d]/20 flex items-center justify-center group-hover:bg-[#2c004d]/30 transition">
                            <svg class="w-7 h-7 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <div>
                            <div class="font-extrabold text-[#2c004d]">ابدأ محادثة</div>
                            <div class="text-xs text-slate-500 mt-0.5">تواصل فوري مع الدعم</div>
                        </div>
                    </div>
                </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Platform Guide --}}
    <div id="guide" class="mb-20 scroll-mt-24">
        <div class="mb-8">
            <div class="inline-flex items-center gap-2 text-xs font-bold px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 mb-3">دليل الاستخدام</div>
            <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900">شرح استخدام المنصة</h2>
            <p class="mt-2 text-slate-600">دليلك الشامل للاستفادة من كل مميزات Pegasus Academy</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 support-card-hover">
                <div class="w-12 h-12 rounded-2xl bg-[#2c004d]/10 flex items-center justify-center mb-4">
                    <span class="text-2xl">1</span>
                </div>
                <h3 class="font-extrabold text-slate-900">التسجيل والاشتراك</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">أنشئ حساباً من صفحة تسجيل الدخول، تصفّح الدورات، واختر الدورة المناسبة. أضفها للسلة وأكمل عملية الدفع لتفعيل الاشتراك.</p>
            </div>
            <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 support-card-hover">
                <div class="w-12 h-12 rounded-2xl bg-[#2c004d]/10 flex items-center justify-center mb-4">
                    <span class="text-2xl">2</span>
                </div>
                <h3 class="font-extrabold text-slate-900">متابعة الدروس</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">من "دوراتي" ادخل إلى الدورة وابدأ بمشاهدة الدروس بالترتيب. يتم حفظ تقدمك تلقائياً ويمكنك العودة من حيث توقفت في أي وقت.</p>
            </div>
            <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 support-card-hover">
                <div class="w-12 h-12 rounded-2xl bg-[#2c004d]/10 flex items-center justify-center mb-4">
                    <span class="text-2xl">3</span>
                </div>
                <h3 class="font-extrabold text-slate-900">الواجبات والتقييمات</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">في "واجباتي" ستجد كل الواجبات المطلوبة. اختر الواجب، اقرأ التعليمات، وأرفق إجابتك. بعد التقييم ستظهر لك الدرجة وملاحظات المدرس.</p>
            </div>
            <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 support-card-hover">
                <div class="w-12 h-12 rounded-2xl bg-[#2c004d]/10 flex items-center justify-center mb-4">
                    <span class="text-2xl">4</span>
                </div>
                <h3 class="font-extrabold text-slate-900">الاختبارات</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">بعض الدروس تحتوي على اختبارات. أجب على الأسئلة واضغط إرسال. يمكنك إعادة المحاولة إذا كانت الدورة تسمح بذلك.</p>
            </div>
            <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 support-card-hover">
                <div class="w-12 h-12 rounded-2xl bg-[#2c004d]/10 flex items-center justify-center mb-4">
                    <span class="text-2xl">5</span>
                </div>
                <h3 class="font-extrabold text-slate-900">الشهادات</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">بعد إكمال جميع دروس الدورة، ستظهر لك إمكانية تحميل الشهادة من صفحة الدورة مباشرة.</p>
            </div>
            <div class="rounded-3xl border-2 border-slate-200 bg-white p-6 support-card-hover">
                <div class="w-12 h-12 rounded-2xl bg-[#2c004d]/10 flex items-center justify-center mb-4">
                    <span class="text-2xl">6</span>
                </div>
                <h3 class="font-extrabold text-slate-900">الرسائل والدعم</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">استخدم نظام الرسائل لبدء محادثة مع فريق الدعم أو مدرّس الدورة. يمكنك أيضاً تقديم شكوى أو استفسار من هذه الصفحة.</p>
            </div>
        </div>
    </div>
</section>
@endsection
