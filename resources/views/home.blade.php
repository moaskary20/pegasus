@php
    $featuredCourses = \App\Models\Course::query()
        ->where('is_published', true)
        ->with(['instructor', 'category'])
        ->orderByDesc('rating')
        ->limit(8)
        ->get();

    $newCourses = \App\Models\Course::query()
        ->where('is_published', true)
        ->with(['instructor', 'category'])
        ->latest()
        ->limit(8)
        ->get();

    $categories = \App\Models\Category::query()
        ->latest()
        ->limit(10)
        ->get();

    $featuredProducts = \App\Models\Product::query()
        ->active()
        ->featured()
        ->latest()
        ->limit(8)
        ->get();

    // Home slider (from Site Settings)
    $rawSlides = \App\Models\PlatformSetting::get('site_home_slider', []);
    if (is_string($rawSlides)) {
        $rawSlides = json_decode($rawSlides, true) ?: [];
    }
    $homeSlides = collect(is_array($rawSlides) ? $rawSlides : [])
        ->filter(fn ($s) => is_array($s) && !empty($s['image_path'] ?? '') && ((bool) ($s['is_active'] ?? true)))
        ->values()
        ->all();
@endphp

@extends('layouts.site')

@section('content')
    <style>
        @keyframes heroFadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .hero-fade-up { animation: heroFadeUp .7s ease-out both; }

        @keyframes heroBlink {
            0%, 45% { opacity: 1; }
            46%, 100% { opacity: 0; }
        }
        .hero-cursor { display: none; }
        .hero-cursor.is-on { display: inline-block; animation: heroBlink .9s steps(1) infinite; }

        /* Split section: professional reveal + frame (3d195c) */
        .pro-reveal { opacity: 0; transform: translateY(14px); }
        .pro-reveal.is-visible { opacity: 1; transform: translateY(0); transition: opacity .7s ease, transform .7s ease; }

        @keyframes proSpin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        .pro-frame { position: relative; border-radius: 28px; padding: 10px; }


        .pro-accent { position: relative; display: inline-block; }
        .pro-accent::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -6px;
            height: 3px;
            border-radius: 999px;
            background: #3d195c;
            transform: scaleX(0);
            transform-origin: right;
            transition: transform .7s ease;
            opacity: .9;
        }
        .pro-reveal.is-visible .pro-accent::after { transform: scaleX(1); }

        @media (prefers-reduced-motion: reduce) {
            .pro-reveal { opacity: 1; transform: none; }
            .pro-reveal.is-visible { transition: none; }
            .pro-frame::before { animation: none; }
            .pro-accent::after { transition: none; transform: scaleX(1); }
        }
    </style>

    {{-- Hero (Slider removed as requested) --}}
    <section class="bg-gradient-to-l from-[#2c004d] to-[#2c004d]/85 text-white">
        <div class="max-w-7xl mx-auto px-4 py-12 md:py-16">
            <div class="grid md:grid-cols-1 gap-10 items-center">
                <div class="max-w-2xl">
                    <h1 class="text-3xl md:text-4xl font-extrabold leading-snug hero-fade-up" style="animation-delay: 60ms;">
                        <span
                            data-hero-typed
                            data-text="تعلّم بذكاء… وابدأ رحلتك التعليمية الآن"
                        >تعلّم بذكاء… وابدأ رحلتك التعليمية الآن</span><span class="hero-cursor" aria-hidden="true">|</span>
                    </h1>
                    <p class="mt-4 text-white/90 leading-relaxed hero-fade-up" style="animation-delay: 220ms;">
                        دورات احترافية، متابعات واضحة، ورسائل مباشرة مع المدرسين — وكل ما تحتاجه في مكان واحد.
                    </p>
                    <div class="mt-6 flex flex-col sm:flex-row gap-3 hero-fade-up" style="animation-delay: 360ms;">
                        <a href="{{ url('/admin/browse-courses') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white text-[#2c004d] font-bold hover:bg-white/95 transition">
                            تصفّح الدورات
                        </a>
                        <a href="{{ url('/admin') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white/10 border border-white/20 font-bold hover:bg-white/15 transition">
                            لوحة التحكم
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function () {
            const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const el = document.querySelector('[data-hero-typed]');
            if (!el || prefersReduced) return;

            const full = (el.getAttribute('data-text') || '').trim();
            if (!full) return;

            const cursor = el.nextElementSibling;
            if (cursor && cursor.classList) cursor.classList.add('is-on');

            // Start typing after initial fade-in
            const startDelayMs = 220;
            const speedMs = 26; // typing speed

            el.textContent = '';
            let i = 0;

            const tick = () => {
                i++;
                el.textContent = full.slice(0, i);
                if (i < full.length) {
                    window.setTimeout(tick, speedMs);
                } else {
                    // Stop cursor after finishing (still subtle)
                    window.setTimeout(() => {
                        if (cursor && cursor.classList) cursor.classList.remove('is-on');
                    }, 900);
                }
            };

            window.setTimeout(tick, startDelayMs);
        })();
    </script>

    {{-- Home Slider (below hero) --}}
    @if(!empty($homeSlides))
        @php
            $first = $homeSlides[0] ?? [];
            $firstImage = !empty($first['image_path'] ?? '') ? asset('storage/' . ltrim((string) $first['image_path'], '/')) : null;
            $firstTitle = (string) ($first['title'] ?? '');
            $firstSubtitle = (string) ($first['subtitle'] ?? '');
            $firstPrimaryText = (string) ($first['primary_text'] ?? '');
            $firstPrimaryUrl = (string) ($first['primary_url'] ?? '');
            $firstSecondaryText = (string) ($first['secondary_text'] ?? '');
            $firstSecondaryUrl = (string) ($first['secondary_url'] ?? '');
        @endphp

        <section class="bg-white">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <div class="relative rounded-3xl overflow-hidden border bg-slate-900 shadow-xl h-[240px] sm:h-[320px] md:h-[420px]" id="home-slider">
                    {{-- Background image --}}
                    @if($firstImage)
                        <img
                            id="home-slider-image"
                            src="{{ $firstImage }}"
                            alt=""
                            class="absolute inset-0 w-full h-full object-cover opacity-95 transition-opacity duration-300"
                        />
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-l from-black/55 via-black/25 to-black/10"></div>

                    {{-- Content --}}
                    <div class="relative z-10 h-full flex items-end">
                        <div class="p-5 sm:p-7 md:p-10 max-w-2xl text-white">
                            <div class="inline-flex items-center gap-2 text-[11px] font-bold px-3 py-1 rounded-full bg-white/10 border border-white/15">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span>عروض وتحديثات</span>
                            </div>

                            <div class="mt-3">
                                <div id="home-slider-title" class="text-2xl md:text-3xl font-extrabold leading-snug">
                                    {{ $firstTitle ?: 'اكتشف أحدث الدورات والعروض' }}
                                </div>
                                <div id="home-slider-subtitle" class="mt-2 text-sm md:text-base text-white/90 leading-relaxed">
                                    {{ $firstSubtitle ?: 'تابع الجديد أولاً واستفد من أفضل المحتويات.' }}
                                </div>
                            </div>

                            <div id="home-slider-buttons" class="mt-5 flex flex-col sm:flex-row gap-3">
                                @if($firstPrimaryText && $firstPrimaryUrl)
                                    <a
                                        id="home-slider-primary"
                                        href="{{ $firstPrimaryUrl }}"
                                        class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white text-[#2c004d] font-bold hover:bg-white/95 transition"
                                    >
                                        {{ $firstPrimaryText }}
                                    </a>
                                @else
                                    <a
                                        id="home-slider-primary"
                                        href="{{ url('/admin/browse-courses') }}"
                                        class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white text-[#2c004d] font-bold hover:bg-white/95 transition"
                                    >
                                        تصفّح الدورات
                                    </a>
                                @endif

                                @if($firstSecondaryText && $firstSecondaryUrl)
                                    <a
                                        id="home-slider-secondary"
                                        href="{{ $firstSecondaryUrl }}"
                                        class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white/10 border border-white/20 font-bold hover:bg-white/15 transition"
                                    >
                                        {{ $firstSecondaryText }}
                                    </a>
                                @else
                                    <a
                                        id="home-slider-secondary"
                                        href="{{ url('/admin') }}"
                                        class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white/10 border border-white/20 font-bold hover:bg-white/15 transition"
                                    >
                                        لوحة التحكم
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Controls --}}
                    @if(count($homeSlides) > 1)
                        <button
                            type="button"
                            id="home-slider-prev"
                            class="absolute top-1/2 -translate-y-1/2 left-3 sm:left-4 w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/15 text-white flex items-center justify-center transition"
                            aria-label="السابق"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button
                            type="button"
                            id="home-slider-next"
                            class="absolute top-1/2 -translate-y-1/2 right-3 sm:right-4 w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/15 text-white flex items-center justify-center transition"
                            aria-label="التالي"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <div class="absolute bottom-3 sm:bottom-4 right-1/2 translate-x-1/2 flex items-center gap-2">
                            @foreach($homeSlides as $i => $s)
                                <button
                                    type="button"
                                    class="home-slider-dot w-2.5 h-2.5 rounded-full border border-white/40 transition"
                                    data-index="{{ $i }}"
                                    aria-label="انتقل إلى شريحة {{ $i + 1 }}"
                                    style="{{ $i === 0 ? 'background: rgba(255,255,255,.95);' : 'background: rgba(255,255,255,.15);' }}"
                                ></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <script type="application/json" id="home-slider-data">@json($homeSlides)</script>
        <script>
            (function () {
                const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const dataEl = document.getElementById('home-slider-data');
                const root = document.getElementById('home-slider');
                if (!dataEl || !root) return;

                let slides = [];
                try { slides = JSON.parse(dataEl.textContent || '[]') || []; } catch (e) { slides = []; }
                slides = Array.isArray(slides) ? slides : [];
                slides = slides.filter(s => s && s.image_path && (s.is_active === undefined || !!s.is_active));
                if (slides.length <= 1) return; // server-rendered first slide is enough

                const img = document.getElementById('home-slider-image');
                const titleEl = document.getElementById('home-slider-title');
                const subtitleEl = document.getElementById('home-slider-subtitle');
                const primaryEl = document.getElementById('home-slider-primary');
                const secondaryEl = document.getElementById('home-slider-secondary');
                const prevBtn = document.getElementById('home-slider-prev');
                const nextBtn = document.getElementById('home-slider-next');
                const dots = Array.from(root.querySelectorAll('.home-slider-dot'));

                const toImg = (path) => {
                    const p = String(path || '').replace(/^\/+/, '');
                    return `${window.location.origin}/storage/${p}`;
                };

                let idx = 0;
                let timer = null;
                const intervalMs = prefersReduced ? 900000 : 5200;

                const apply = (i) => {
                    idx = (i + slides.length) % slides.length;
                    const s = slides[idx] || {};

                    if (img) {
                        img.style.opacity = '0';
                        window.setTimeout(() => {
                            img.src = toImg(s.image_path);
                            img.style.opacity = '0.95';
                        }, 140);
                    }

                    if (titleEl) titleEl.textContent = (s.title || '').trim() || 'اكتشف أحدث الدورات والعروض';
                    if (subtitleEl) subtitleEl.textContent = (s.subtitle || '').trim() || 'تابع الجديد أولاً واستفد من أفضل المحتويات.';

                    if (primaryEl) {
                        const t = (s.primary_text || '').trim() || 'تصفّح الدورات';
                        const u = (s.primary_url || '').trim() || '/admin/browse-courses';
                        primaryEl.textContent = t;
                        primaryEl.setAttribute('href', u);
                    }

                    if (secondaryEl) {
                        const t = (s.secondary_text || '').trim() || 'لوحة التحكم';
                        const u = (s.secondary_url || '').trim() || '/admin';
                        secondaryEl.textContent = t;
                        secondaryEl.setAttribute('href', u);
                    }

                    dots.forEach((d, di) => {
                        d.style.background = di === idx ? 'rgba(255,255,255,.95)' : 'rgba(255,255,255,.15)';
                    });
                };

                const start = () => {
                    stop();
                    timer = window.setInterval(() => apply(idx + 1), intervalMs);
                };
                const stop = () => { if (timer) { window.clearInterval(timer); timer = null; } };

                if (prevBtn) prevBtn.addEventListener('click', () => { apply(idx - 1); start(); });
                if (nextBtn) nextBtn.addEventListener('click', () => { apply(idx + 1); start(); });
                dots.forEach(d => d.addEventListener('click', () => { apply(Number(d.getAttribute('data-index') || 0)); start(); }));

                // pause on hover
                root.addEventListener('mouseenter', stop);
                root.addEventListener('mouseleave', start);

                start();
            })();
        </script>
    @endif

    {{-- Featured Courses --}}
    <section class="max-w-7xl mx-auto px-4 py-10">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">الدورات المميزة</h2>
                <p class="text-sm text-slate-600 mt-1">أعلى تقييمات وأفضل محتوى حاليًا</p>
            </div>
            <a href="{{ url('/admin/browse-courses') }}" class="text-sm font-bold text-[#2c004d] hover:underline">عرض الكل</a>
        </div>

        @php
            $courseWishlistIds = session('course_wishlist', []);
            $courseWishlistIds = is_array($courseWishlistIds) ? array_map('intval', $courseWishlistIds) : [];
        @endphp
        <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($featuredCourses as $course)
                <div class="rounded-2xl border bg-white overflow-hidden hover:shadow-lg transition group">
                    <div class="relative aspect-[16/9] bg-slate-100 overflow-hidden">
                        <x-wishlist-heart :course="$course" :in-wishlist="in_array((int) $course->id, $courseWishlistIds)" />
                        <a href="{{ route('site.course.show', $course) }}" class="block w-full h-full">
                            @if($course->cover_image)
                                <img src="{{ $course->cover_image }}" alt="{{ $course->title }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition duration-300" />
                            @endif
                        </a>
                    </div>
                    <a href="{{ route('site.course.show', $course) }}" class="block p-4">
                        <div class="text-sm font-extrabold line-clamp-2 text-slate-900">{{ $course->title }}</div>
                        <div class="text-xs text-slate-600 mt-1">{{ $course->instructor?->name }} • {{ $course->category?->name }}</div>
                        <div class="mt-3 flex items-center justify-between">
                            <div class="text-xs text-slate-500">
                                ⭐ {{ number_format((float) ($course->rating ?? 0), 1) }}
                            </div>
                            <div class="text-sm font-extrabold text-slate-900">
                                @php $p = (float) ($course->offer_price ?? $course->price ?? 0); @endphp
                                {{ $p > 0 ? number_format($p, 2) . ' ج.م' : 'مجاني' }}
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center text-sm text-slate-500 py-12">
                    لا توجد دورات لعرضها حالياً.
                </div>
            @endforelse
        </div>
    </section>

    {{-- Categories --}}
    <section class="bg-slate-50 border-y">
        <div class="max-w-7xl mx-auto px-4 py-10">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">التصنيفات</h2>
                    <p class="text-sm text-slate-600 mt-1">اختر مجال اهتمامك بسهولة</p>
                </div>
            </div>
            <div class="mt-6 grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                @forelse($categories as $cat)
                    <a
                        href="{{ url('/courses?category=' . (int) $cat->id) }}"
                        class="rounded-2xl bg-white border p-4 hover:shadow-sm transition block"
                    >
                        <div class="font-extrabold text-sm text-slate-900 line-clamp-1">{{ $cat->name }}</div>
                        <div class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $cat->description }}</div>
                        <div class="mt-3 inline-flex items-center gap-2 text-xs font-extrabold text-[#3d195c]">
                            <span>استعرض الدورات</span>
                            <span aria-hidden="true">←</span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center text-sm text-slate-500 py-10">لا توجد تصنيفات حالياً.</div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Split section: Image (right) + FAQ accordion (left) --}}
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-4 py-12 md:py-14">
            <div class="grid lg:grid-cols-2 gap-10 items-center">
                {{-- Right: Image --}}
                <div class="lg:order-2 relative pro-reveal" data-reveal style="transition-delay: 40ms;">
                    <div class="pro-frame">
                        <div class="relative z-[1] rounded-[22px] overflow-hidden shadow-xl">
                            <img
                                src="{{ asset('assets/site/images/10.png') }}"
                                alt="طالبة عربية تتعلم باستخدام الحاسوب"
                                class="w-full h-[360px] md:h-[420px] object-cover"
                                loading="lazy"
                            />
                        </div>
                    </div>
                </div>

                {{-- Left: Accordion --}}
                <div class="lg:order-1 pro-reveal" data-reveal style="transition-delay: 120ms;">
                    <div class="text-xs font-extrabold tracking-widest text-slate-500 uppercase">
                        PREMIUM LEARNING
                    </div>
                    <h2 class="mt-3 text-2xl md:text-3xl font-extrabold text-slate-900 leading-snug">
                        تجربة تعليم إلكتروني بمستوى
                        <span class="text-[#3d195c] pro-accent">احترافي</span>
                    </h2>

                    <div
                        class="mt-6 rounded-3xl border bg-white overflow-hidden"
                        x-data="{ open: 0, toggle(i){ this.open = (this.open === i ? -1 : i) } }"
                        style="direction: rtl;"
                    >
                        @php
                            $faq = [
                                ['q' => 'تعلّم بالسرعة التي تناسبك', 'a' => 'اختر الوقت المناسب لك وتابع تقدمك خطوة بخطوة مع تذكيرات ومتابعات واضحة.'],
                                ['q' => 'تعلّم من أفضل المدرّسين', 'a' => 'محتوى احترافي وتحديثات مستمرة، مع إمكانية التواصل السريع للحصول على الدعم.'],
                                ['q' => 'شارك المعرفة والأفكار', 'a' => 'انضم للمجتمع وشارك أسئلتك وخبراتك، واستفد من خبرات الآخرين.'],
                                ['q' => 'تواصل مع مجتمع إبداعي عالمي', 'a' => 'تجربة تعلم متكاملة تجمع الدورات والاختبارات والمهام والتقارير في مكان واحد.'],
                            ];
                        @endphp

                        <div class="divide-y divide-slate-200">
                            @foreach($faq as $i => $item)
                                <div class="group">
                                    <button
                                        type="button"
                                        class="w-full flex items-center justify-between gap-4 px-5 py-4 text-right hover:bg-slate-50 transition"
                                        @click="toggle({{ $i }})"
                                    >
                                        <span class="text-sm md:text-base font-extrabold text-slate-900">
                                            {{ $item['q'] }}
                                        </span>
                                        <span
                                            class="shrink-0 w-9 h-9 rounded-2xl border bg-white flex items-center justify-center text-slate-900 group-hover:bg-slate-100 transition"
                                            :class="open === {{ $i }} ? 'border-sky-200 bg-sky-50 text-sky-700' : 'border-slate-200'"
                                        >
                                            <svg class="w-4 h-4 transition-transform" :class="open === {{ $i }} ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/>
                                            </svg>
                                        </span>
                                    </button>

                                    <div x-show="open === {{ $i }}" x-cloak x-transition.opacity.duration.150ms class="px-5 pb-5 -mt-1">
                                        <div class="text-sm text-slate-600 leading-relaxed">
                                            {{ $item['a'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <a href="{{ url('/courses') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-[#2c004d] text-white font-bold hover:bg-[#2c004d]/95 transition">
                            استكشف الدورات
                        </a>
                        <a href="{{ route('site.support') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-slate-100 text-slate-800 font-bold hover:bg-slate-200 transition">
                            تواصل معنا
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function () {
            const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReduced) return;

            const els = Array.from(document.querySelectorAll('[data-reveal]'));
            if (!els.length) return;

            if (!('IntersectionObserver' in window)) {
                els.forEach(el => el.classList.add('is-visible'));
                return;
            }

            const obs = new IntersectionObserver((entries) => {
                entries.forEach((e) => {
                    if (!e.isIntersecting) return;
                    e.target.classList.add('is-visible');
                    obs.unobserve(e.target);
                });
            }, { threshold: 0.15 });

            els.forEach(el => obs.observe(el));
        })();
    </script>

    {{-- New Courses + Featured Products --}}
    <section class="max-w-7xl mx-auto px-4 py-10">
        <div class="grid lg:grid-cols-2 gap-8">
            <div>
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">أحدث الدورات</h2>
                        <p class="text-sm text-slate-600 mt-1">اكتشف الجديد أولاً</p>
                    </div>
                    <a href="{{ url('/admin/browse-courses') }}" class="text-sm font-bold text-[#2c004d] hover:underline">عرض الكل</a>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($newCourses->take(6) as $course)
                        <div class="rounded-2xl border bg-white p-4 flex items-center justify-between gap-3 hover:shadow-sm transition">
                            <div class="min-w-0">
                                <div class="font-extrabold text-sm line-clamp-1">{{ $course->title }}</div>
                                <div class="text-xs text-slate-600 mt-1">{{ $course->instructor?->name }}</div>
                            </div>
                            <div class="text-sm font-extrabold text-slate-900 shrink-0">
                                @php $p = (float) ($course->offer_price ?? $course->price ?? 0); @endphp
                                {{ $p > 0 ? number_format($p, 2) . ' ج.م' : 'مجاني' }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-sm text-slate-500 py-10">لا توجد دورات حالياً.</div>
                    @endforelse
                </div>
            </div>

            <div>
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">منتجات مميزة</h2>
                        <p class="text-sm text-slate-600 mt-1">أفضل اختيارات المتجر</p>
                    </div>
                    <a href="{{ url('/admin/store-products') }}" class="text-sm font-bold text-[#2c004d] hover:underline">إدارة المتجر</a>
                </div>

                <div class="mt-6 grid sm:grid-cols-2 gap-4">
                    @forelse($featuredProducts as $p)
                        <div class="rounded-2xl border bg-white overflow-hidden hover:shadow-lg transition">
                            <div class="aspect-[16/10] bg-slate-100 overflow-hidden">
                                @if($p->main_image)
                                    <img src="{{ asset('storage/' . ltrim($p->main_image, '/')) }}" alt="{{ $p->name }}" class="w-full h-full object-cover" />
                                @endif
                            </div>
                            <div class="p-4">
                                <div class="text-sm font-extrabold line-clamp-1">{{ $p->name }}</div>
                                <div class="mt-2 flex items-center justify-between">
                                    <div class="text-sm font-extrabold text-slate-900">{{ number_format((float) $p->price, 2) }} ج.م</div>
                                    @if($p->compare_price && (float) $p->compare_price > (float) $p->price)
                                        <div class="text-xs text-slate-400 line-through">{{ number_format((float) $p->compare_price, 2) }} ج.م</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center text-sm text-slate-500 py-10">
                            لا توجد منتجات مميزة حالياً.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection

