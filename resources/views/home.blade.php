@php
    $stats = $stats ?? [];
    $featuredCourses = $featuredCourses ?? collect();
    $newCourses = $newCourses ?? collect();
    $categories = $categories ?? collect();
    $featuredProducts = $featuredProducts ?? collect();
    $homeSlides = $homeSlides ?? [];
    $quickLinksData = $quickLinksData ?? ['cartCount' => 0, 'wishlistCount' => 0, 'unreadMessages' => 0, 'unreadNotifications' => 0];
    $courseWishlistIds = session('course_wishlist', []);
    $courseWishlistIds = is_array($courseWishlistIds) ? array_map('intval', $courseWishlistIds) : [];
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

        .pro-reveal, .reveal-item { opacity: 0; transform: translateY(14px); transition: opacity .7s ease, transform .7s ease; }
        .pro-reveal.is-visible, .reveal-item.is-visible { opacity: 1; transform: translateY(0); }

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

        .hero-parallax { will-change: transform; }

        @media (prefers-reduced-motion: reduce) {
            .pro-reveal, .reveal-item { opacity: 1; transform: none; transition: none; }
            .pro-accent::after { transition: none; transform: scaleX(1); }
        }
    </style>

    @if(session('notice'))
        <div class="max-w-7xl mx-auto px-4 pt-4">
            <div class="rounded-2xl border px-4 py-3 text-sm font-semibold {{ (session('notice')['type'] ?? '') === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}">
                {{ session('notice')['message'] ?? '' }}
            </div>
        </div>
    @endif

    {{-- Hero with Parallax --}}
    <section class="relative overflow-hidden bg-gradient-to-l from-[#2c004d] to-[#2c004d]/85 text-white">
        <div class="hero-parallax absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Ccircle cx=%2250%22 cy=%2250%22 r=%2240%22 fill=%22rgba(255,255,255,0.03)%22/%3E%3C/svg%3E')] opacity-50" data-parallax style="background-size: 60px 60px;"></div>
        <div class="relative max-w-7xl mx-auto px-4 py-12 md:py-16">
            <div class="max-w-2xl">
                <h1 class="text-3xl md:text-4xl font-extrabold leading-snug hero-fade-up" style="animation-delay: 60ms;">
                    <span data-hero-typed data-text="تعلّم بذكاء… وابدأ رحلتك التعليمية الآن">تعلّم بذكاء… وابدأ رحلتك التعليمية الآن</span><span class="hero-cursor" aria-hidden="true">|</span>
                </h1>
                <p class="mt-4 text-white/90 leading-relaxed hero-fade-up" style="animation-delay: 220ms;">
                    دورات احترافية، متابعات واضحة، ورسائل مباشرة مع المدرسين — وكل ما تحتاجه في مكان واحد.
                </p>
                <div class="mt-6 flex flex-col sm:flex-row gap-3 hero-fade-up" style="animation-delay: 360ms;">
                    <a href="{{ route('site.courses') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white text-[#2c004d] font-bold hover:bg-white/95 hover:scale-105 transition-all duration-300">
                        تصفّح الدورات
                    </a>
                    @auth
                        <a href="{{ url('/admin') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white/10 border border-white/20 font-bold hover:bg-white/15 hover:scale-105 transition-all duration-300">
                            لوحة التحكم
                        </a>
                    @else
                        <a href="{{ route('site.auth') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white/10 border border-white/20 font-bold hover:bg-white/15 hover:scale-105 transition-all duration-300">
                            تسجيل الدخول
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Bar with Count-up (desktop only) --}}
    <section class="hidden md:block bg-[#2c004d] py-8 md:py-10">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4" id="stats-section">
                <x-site.stat-counter :value="$stats['courses_count'] ?? 0" label="دورات" icon="academic" />
                <x-site.stat-counter :value="$stats['students_count'] ?? 0" label="طالب" icon="users" />
                <x-site.stat-counter :value="$stats['products_count'] ?? 0" label="منتج" icon="shopping" />
                <x-site.stat-counter :value="$stats['enrollments_count'] ?? 0" label="تسجيل" icon="chart" />
                <x-site.stat-counter :value="$stats['completed_count'] ?? 0" label="مكتمل" icon="check" />
            </div>
        </div>
    </section>

    {{-- Home Slider --}}
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
                <div class="relative rounded-3xl overflow-hidden border bg-slate-900 shadow-xl h-[190px] sm:h-[270px] md:h-[370px]" id="home-slider">
                    @if($firstImage)
                        <img id="home-slider-image" src="{{ $firstImage }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-95 transition-opacity duration-300" />
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-l from-black/55 via-black/25 to-black/10"></div>
                    <div class="relative z-10 h-full flex items-end">
                        <div class="p-5 sm:p-7 md:p-10 max-w-2xl text-white">
                            <div class="inline-flex items-center gap-2 text-[11px] font-bold px-3 py-1 rounded-full bg-white/10 border border-white/15">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span>عروض وتحديثات</span>
                            </div>
                            <div class="mt-3">
                                <div id="home-slider-title" class="text-2xl md:text-3xl font-extrabold leading-snug">{{ $firstTitle ?: 'اكتشف أحدث الدورات والعروض' }}</div>
                                <div id="home-slider-subtitle" class="mt-2 text-sm md:text-base text-white/90 leading-relaxed">{{ $firstSubtitle ?: 'تابع الجديد أولاً واستفد من أفضل المحتويات.' }}</div>
                            </div>
                            <div id="home-slider-buttons" class="mt-5 flex flex-col sm:flex-row gap-3">
                                <a id="home-slider-primary" href="{{ ($firstPrimaryText && $firstPrimaryUrl) ? $firstPrimaryUrl : route('site.courses') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white text-[#2c004d] font-bold hover:bg-white/95 transition">{{ ($firstPrimaryText && $firstPrimaryUrl) ? $firstPrimaryText : 'تصفّح الدورات' }}</a>
                                <a id="home-slider-secondary" href="{{ ($firstSecondaryText && $firstSecondaryUrl) ? $firstSecondaryUrl : (auth()->check() ? url('/admin') : route('site.auth')) }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white/10 border border-white/20 font-bold hover:bg-white/15 transition">{{ ($firstSecondaryText && $firstSecondaryUrl) ? $firstSecondaryText : (auth()->check() ? 'لوحة التحكم' : 'تسجيل الدخول') }}</a>
                            </div>
                        </div>
                    </div>
                    @if(count($homeSlides) > 1)
                        <button type="button" id="home-slider-prev" class="absolute top-1/2 -translate-y-1/2 left-3 sm:left-4 w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/15 text-white flex items-center justify-center transition" aria-label="السابق">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" id="home-slider-next" class="absolute top-1/2 -translate-y-1/2 right-3 sm:right-4 w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/15 text-white flex items-center justify-center transition" aria-label="التالي">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <div class="absolute bottom-3 sm:bottom-4 right-1/2 translate-x-1/2 flex items-center gap-2">
                            @foreach($homeSlides as $i => $s)
                                <button type="button" class="home-slider-dot w-2.5 h-2.5 rounded-full border border-white/40 transition" data-index="{{ $i }}" aria-label="انتقل إلى شريحة {{ $i + 1 }}" style="{{ $i === 0 ? 'background: rgba(255,255,255,.95);' : 'background: rgba(255,255,255,.15);' }}"></button>
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
                slides = (Array.isArray(slides) ? slides : []).filter(s => s && s.image_path && (s.is_active === undefined || !!s.is_active));
                if (slides.length <= 1) return;
                const img = document.getElementById('home-slider-image');
                const titleEl = document.getElementById('home-slider-title');
                const subtitleEl = document.getElementById('home-slider-subtitle');
                const primaryEl = document.getElementById('home-slider-primary');
                const secondaryEl = document.getElementById('home-slider-secondary');
                const prevBtn = document.getElementById('home-slider-prev');
                const nextBtn = document.getElementById('home-slider-next');
                const dots = Array.from(root.querySelectorAll('.home-slider-dot'));
                const toImg = (path) => `${window.location.origin}/storage/${String(path || '').replace(/^\/+/, '')}`;
                let idx = 0;
                let timer = null;
                const intervalMs = prefersReduced ? 900000 : 5200;
                const apply = (i) => {
                    idx = (i + slides.length) % slides.length;
                    const s = slides[idx] || {};
                    if (img) { img.style.opacity = '0'; setTimeout(() => { img.src = toImg(s.image_path); img.style.opacity = '0.95'; }, 140); }
                    if (titleEl) titleEl.textContent = (s.title || '').trim() || 'اكتشف أحدث الدورات والعروض';
                    if (subtitleEl) subtitleEl.textContent = (s.subtitle || '').trim() || 'تابع الجديد أولاً واستفد من أفضل المحتويات.';
                    if (primaryEl) { primaryEl.textContent = (s.primary_text || '').trim() || 'تصفّح الدورات'; primaryEl.href = (s.primary_url || '').trim() || '/courses'; }
                    if (secondaryEl) { secondaryEl.textContent = (s.secondary_text || '').trim() || 'لوحة التحكم'; secondaryEl.href = (s.secondary_url || '').trim() || '/admin'; }
                    dots.forEach((d, di) => { d.style.background = di === idx ? 'rgba(255,255,255,.95)' : 'rgba(255,255,255,.15)'; });
                };
                const start = () => { if (timer) clearInterval(timer); timer = setInterval(() => apply(idx + 1), intervalMs); };
                const stop = () => { if (timer) { clearInterval(timer); timer = null; } };
                if (prevBtn) prevBtn.addEventListener('click', () => { apply(idx - 1); start(); });
                if (nextBtn) nextBtn.addEventListener('click', () => { apply(idx + 1); start(); });
                dots.forEach(d => d.addEventListener('click', () => { apply(Number(d.getAttribute('data-index') || 0)); start(); }));
                root.addEventListener('mouseenter', stop);
                root.addEventListener('mouseleave', start);
                start();
            })();
        </script>
    @endif

    {{-- Categories Slider (5 cards) --}}
    <section class="max-w-7xl mx-auto px-4 py-10">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">التصنيفات</h2>
                <p class="text-sm text-slate-600 mt-1">اختر مجال اهتمامك بسهولة</p>
            </div>
        </div>
        @if($categories->isEmpty())
            <div class="mt-6 text-center text-sm text-slate-500 py-10">لا توجد تصنيفات حالياً.</div>
        @else
            <div class="mt-6 relative px-14" id="categories-slider-wrap">
                <button type="button" id="categories-slider-prev" class="absolute top-1/2 -translate-y-1/2 right-2 z-10 w-12 h-12 rounded-2xl bg-white shadow-lg border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-slate-50 hover:border-[#3d195c]/30 transition disabled:opacity-40 disabled:pointer-events-none" aria-label="السابق">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" id="categories-slider-next" class="absolute top-1/2 -translate-y-1/2 left-2 z-10 w-12 h-12 rounded-2xl bg-white shadow-lg border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-slate-50 hover:border-[#3d195c]/30 transition disabled:opacity-40 disabled:pointer-events-none" aria-label="التالي">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="overflow-hidden" style="direction: ltr;">
                    <div id="categories-slider-track" class="flex gap-4 transition-transform duration-500 ease-out" style="direction: rtl;">
                        @foreach($categories as $i => $cat)
                            <div class="categories-slider-card flex-shrink-0 w-[calc(20%-16px)] min-w-[160px] sm:min-w-[180px] lg:min-w-[200px]">
                                <a href="{{ route('site.courses', ['category' => (int) $cat->id]) }}" class="block rounded-2xl bg-white border p-4 hover:shadow-lg hover:scale-[1.02] transition-all duration-300 h-full">
                                    <div class="font-extrabold text-sm text-slate-900 line-clamp-1">{{ $cat->name }}</div>
                                    <div class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $cat->description ?? '' }}</div>
                                    <div class="mt-3 inline-flex items-center gap-2 text-xs font-extrabold text-[#3d195c]">
                                        <span>استعرض الدورات</span>
                                        <span aria-hidden="true">←</span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <script>
                (function () {
                    const wrap = document.getElementById('categories-slider-wrap');
                    const track = document.getElementById('categories-slider-track');
                    const prevBtn = document.getElementById('categories-slider-prev');
                    const nextBtn = document.getElementById('categories-slider-next');
                    if (!wrap || !track || !prevBtn || !nextBtn) return;

                    const cards = track.querySelectorAll('.categories-slider-card');
                    const total = cards.length;
                    const gap = 16;
                    const visibleCount = 5;

                    let currentIndex = 0;

                    function getCardWidth() {
                        const first = cards[0];
                        if (!first) return 200;
                        return first.offsetWidth + gap;
                    }

                    function getVisibleCount() {
                        const w = wrap.offsetWidth;
                        const cardW = cards[0] ? cards[0].offsetWidth + gap : 216;
                        return Math.min(5, Math.max(1, Math.floor(w / cardW)));
                    }

                    function update() {
                        const visible = getVisibleCount();
                        const maxIndex = Math.max(0, total - visible);
                        currentIndex = Math.min(currentIndex, maxIndex);
                        const offset = currentIndex * getCardWidth();
                        track.style.transform = 'translateX(' + offset + 'px)';
                        prevBtn.disabled = currentIndex <= 0;
                        nextBtn.disabled = currentIndex >= maxIndex;
                    }

                    prevBtn.addEventListener('click', () => {
                        if (currentIndex > 0) {
                            currentIndex--;
                            update();
                        }
                    });

                    nextBtn.addEventListener('click', () => {
                        const visible = getVisibleCount();
                        const maxIndex = Math.max(0, total - visible);
                        if (currentIndex < maxIndex) {
                            currentIndex++;
                            update();
                        }
                    });

                    update();

                    window.addEventListener('resize', () => update());
                })();
            </script>
        @endif
    </section>

    {{-- Featured Courses Grid --}}
    <section class="max-w-7xl mx-auto px-4 py-10">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">الدورات المميزة</h2>
                <p class="text-sm text-slate-600 mt-1">أعلى تقييمات وأفضل محتوى حاليًا</p>
            </div>
            <a href="{{ route('site.courses') }}" class="text-sm font-bold text-[#2c004d] hover:underline">عرض الكل</a>
        </div>
        <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($featuredCourses as $i => $course)
                <x-site.course-card :course="$course" :in-wishlist="in_array((int) $course->id, $courseWishlistIds)" :stagger-index="$i" />
            @empty
                <div class="col-span-full text-center text-sm text-slate-500 py-12">لا توجد دورات لعرضها حالياً.</div>
            @endforelse
        </div>
    </section>

    {{-- Featured Products Slider --}}
    <section class="bg-slate-50 border-y py-10">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">منتجات مميزة</h2>
                    <p class="text-sm text-slate-600 mt-1">أفضل اختيارات المتجر</p>
                </div>
                <a href="{{ route('site.store') }}" class="text-sm font-bold text-[#2c004d] hover:underline">عرض الكل</a>
            </div>
            @if($featuredProducts->isEmpty())
                <div class="mt-6 text-center text-sm text-slate-500 py-12">لا توجد منتجات مميزة حالياً.</div>
            @else
                <div class="mt-6 relative px-14" id="products-slider-wrap">
                    <button type="button" id="products-slider-prev" class="absolute top-1/2 -translate-y-1/2 right-2 z-10 w-12 h-12 rounded-2xl bg-white shadow-lg border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-slate-50 hover:border-[#3d195c]/30 transition disabled:opacity-40 disabled:pointer-events-none" aria-label="السابق">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" id="products-slider-next" class="absolute top-1/2 -translate-y-1/2 left-2 z-10 w-12 h-12 rounded-2xl bg-white shadow-lg border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-slate-50 hover:border-[#3d195c]/30 transition disabled:opacity-40 disabled:pointer-events-none" aria-label="التالي">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <div class="overflow-hidden" style="direction: ltr;">
                        <div id="products-slider-track" class="flex gap-4 transition-transform duration-500 ease-out" style="direction: rtl;">
                            @foreach($featuredProducts as $i => $product)
                                <div class="products-slider-card flex-shrink-0 w-[calc(25%-12px)] min-w-[200px] sm:min-w-[220px] lg:min-w-[260px]">
                                    <x-site.product-card :product="$product" :in-slider="true" :in-wishlist="in_array((int) $product->id, $productWishlistIds ?? [])" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <script>
                    (function () {
                        const wrap = document.getElementById('products-slider-wrap');
                        const track = document.getElementById('products-slider-track');
                        const prevBtn = document.getElementById('products-slider-prev');
                        const nextBtn = document.getElementById('products-slider-next');
                        if (!wrap || !track || !prevBtn || !nextBtn) return;

                        const cards = track.querySelectorAll('.products-slider-card');
                        const total = cards.length;
                        const gap = 16;

                        let currentIndex = 0;

                        function getCardWidth() {
                            const first = cards[0];
                            if (!first) return 280;
                            return first.offsetWidth + gap;
                        }

                        function getVisibleCount() {
                            const w = wrap.offsetWidth;
                            const cardW = cards[0] ? cards[0].offsetWidth + gap : 296;
                            return Math.max(1, Math.floor(w / cardW));
                        }

                        function update() {
                            const visible = getVisibleCount();
                            const maxIndex = Math.max(0, total - visible);
                            currentIndex = Math.min(currentIndex, maxIndex);
                            const offset = currentIndex * getCardWidth();
                            track.style.transform = 'translateX(' + offset + 'px)';
                            prevBtn.disabled = currentIndex <= 0;
                            nextBtn.disabled = currentIndex >= maxIndex;
                        }

                        prevBtn.addEventListener('click', () => {
                            if (currentIndex > 0) {
                                currentIndex--;
                                update();
                            }
                        });

                        nextBtn.addEventListener('click', () => {
                            const visible = getVisibleCount();
                            const maxIndex = Math.max(0, total - visible);
                            if (currentIndex < maxIndex) {
                                currentIndex++;
                                update();
                            }
                        });

                        update();

                        window.addEventListener('resize', () => update());
                    })();
                </script>
            @endif
        </div>
    </section>

    {{-- New Courses Grid --}}
    <section class="max-w-7xl mx-auto px-4 py-10">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">أحدث الدورات</h2>
                <p class="text-sm text-slate-600 mt-1">اكتشف الجديد أولاً</p>
            </div>
            <a href="{{ route('site.courses') }}" class="text-sm font-bold text-[#2c004d] hover:underline">عرض الكل</a>
        </div>
        <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($newCourses as $i => $course)
                <x-site.course-card :course="$course" :in-wishlist="in_array((int) $course->id, $courseWishlistIds)" :stagger-index="$i" />
            @empty
                <div class="col-span-full text-center text-sm text-slate-500 py-12">لا توجد دورات حالياً.</div>
            @endforelse
        </div>
    </section>

    {{-- FAQ + CTA --}}
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-4 py-12 md:py-14">
            <div class="grid lg:grid-cols-2 gap-10 items-center">
                <div class="lg:order-2 relative pro-reveal" data-reveal>
                    <div class="rounded-[22px] overflow-hidden shadow-xl">
                        <img src="{{ asset('assets/site/images/10.png') }}" alt="طالبة عربية تتعلم باستخدام الحاسوب" class="w-full h-[590px] md:h-[650px] object-cover" loading="lazy" />
                    </div>
                </div>
                <div class="lg:order-1 pro-reveal" data-reveal>
                    <div class="text-xs font-extrabold tracking-widest text-slate-500 uppercase">PREMIUM LEARNING</div>
                    <h2 class="mt-3 text-2xl md:text-3xl font-extrabold text-slate-900 leading-snug">
                        تجربة تعليم إلكتروني بمستوى <span class="text-[#3d195c] pro-accent">احترافي</span>
                    </h2>
                    <div class="mt-6 rounded-3xl border bg-white overflow-hidden" x-data="{ open: 0, toggle(i){ this.open = (this.open === i ? -1 : i) } }" style="direction: rtl;">
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
                                    <button type="button" class="w-full flex items-center justify-between gap-4 px-5 py-4 text-right hover:bg-slate-50 transition" @click="toggle({{ $i }})">
                                        <span class="text-sm md:text-base font-extrabold text-slate-900">{{ $item['q'] }}</span>
                                        <span class="shrink-0 w-9 h-9 rounded-2xl border bg-white flex items-center justify-center text-slate-900 group-hover:bg-slate-100 transition" :class="open === {{ $i }} ? 'border-sky-200 bg-sky-50 text-sky-700' : 'border-slate-200'">
                                            <svg class="w-4 h-4 transition-transform" :class="open === {{ $i }} ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                                        </span>
                                    </button>
                                    <div x-show="open === {{ $i }}" x-cloak x-transition.opacity.duration.150ms class="px-5 pb-5 -mt-1">
                                        <div class="text-sm text-slate-600 leading-relaxed">{{ $item['a'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('site.courses') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-[#2c004d] text-white font-bold hover:bg-[#2c004d]/95 hover:scale-105 transition-all duration-300">استكشف الدورات</a>
                        <a href="{{ route('site.support') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-slate-100 text-slate-800 font-bold hover:bg-slate-200 hover:scale-105 transition-all duration-300">تواصل معنا</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- أشهر مدرسينا --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-slate-50 to-white py-14 md:py-20">
        <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 50%, rgba(44,0,77,0.08) 0%, transparent 50%);"></div>
        <div class="relative max-w-7xl mx-auto px-4">
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-2 text-xs font-bold px-4 py-2 rounded-full bg-[#2c004d]/10 text-[#2c004d] mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                    فريقنا التعليمي
                </div>
                <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900">أشهر مدرسينا</h2>
                <p class="mt-3 text-slate-600 max-w-2xl mx-auto">تعلم من أفضل المدرسين ذوي الخبرة والتميز في مجالاتهم</p>
            </div>

            @if($topInstructors->isEmpty())
                <div class="text-center text-slate-500 py-12">لا يوجد مدرسون لعرضهم حالياً.</div>
            @else
                <div class="relative px-2 md:px-14" id="instructors-slider-wrap">
                    <button type="button" id="instructors-slider-prev" class="absolute top-1/2 -translate-y-1/2 right-2 z-10 w-12 h-12 rounded-2xl bg-white shadow-xl border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-[#2c004d] hover:text-white hover:border-[#2c004d] transition disabled:opacity-40 disabled:pointer-events-none" aria-label="السابق">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" id="instructors-slider-next" class="absolute top-1/2 -translate-y-1/2 left-2 z-10 w-12 h-12 rounded-2xl bg-white shadow-xl border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-[#2c004d] hover:text-white hover:border-[#2c004d] transition disabled:opacity-40 disabled:pointer-events-none" aria-label="التالي">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <div class="overflow-hidden" style="direction: ltr;">
                        <div id="instructors-slider-track" class="flex gap-5 transition-transform duration-500 ease-out" style="direction: rtl;">
                            @foreach($topInstructors as $instructor)
                                <div class="instructors-slider-card flex-shrink-0 w-[calc(25%-15px)] min-w-[240px] sm:min-w-[260px] lg:min-w-[280px]">
                                    <a href="{{ route('site.courses', ['instructor' => $instructor['id']]) }}" class="group block rounded-3xl bg-white border-2 border-slate-100 p-6 hover:border-[#2c004d]/20 hover:shadow-2xl hover:shadow-[#2c004d]/10 hover:-translate-y-1 transition-all duration-300 h-full">
                                        <div class="flex flex-col items-center text-center">
                                            <div class="relative mb-4">
                                                <div class="w-20 h-20 md:w-24 md:h-24 rounded-2xl overflow-hidden ring-4 ring-[#2c004d]/5 group-hover:ring-[#2c004d]/20 transition-all duration-300">
                                                    @if(!empty($instructor['avatar']))
                                                        <img src="{{ $instructor['avatar'] }}" alt="{{ $instructor['name'] }}" class="w-full h-full object-cover" loading="lazy" />
                                                    @else
                                                        <div class="w-full h-full bg-gradient-to-br from-[#2c004d] to-[#3d195c] flex items-center justify-center">
                                                            <span class="text-2xl md:text-3xl font-extrabold text-white">{{ mb_substr($instructor['name'], 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-white text-xs font-bold shadow-lg">
                                                    ✓
                                                </div>
                                            </div>
                                            <h3 class="font-extrabold text-slate-900 text-lg group-hover:text-[#2c004d] transition-colors line-clamp-1">{{ $instructor['name'] }}</h3>
                                            <div class="mt-3 flex flex-wrap items-center justify-center gap-3 text-sm">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#2c004d]/5 text-[#2c004d] font-bold">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                    {{ $instructor['courses_count'] }} دورة
                                                </span>
                                                @if($instructor['students_count'] > 0)
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-semibold">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                                        {{ number_format($instructor['students_count']) }} طالب
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="mt-4 inline-flex items-center gap-2 text-[#2c004d] font-bold text-sm group-hover:underline">
                                                عرض الدورات
                                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                            </span>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <script>
                    (function () {
                        const wrap = document.getElementById('instructors-slider-wrap');
                        const track = document.getElementById('instructors-slider-track');
                        const prevBtn = document.getElementById('instructors-slider-prev');
                        const nextBtn = document.getElementById('instructors-slider-next');
                        if (!wrap || !track || !prevBtn || !nextBtn) return;

                        const cards = track.querySelectorAll('.instructors-slider-card');
                        const total = cards.length;
                        const gap = 20;
                        let currentIndex = 0;

                        function getCardWidth() {
                            const first = cards[0];
                            if (!first) return 280;
                            return first.offsetWidth + gap;
                        }

                        function getVisibleCount() {
                            const w = wrap.offsetWidth;
                            const cardW = cards[0] ? cards[0].offsetWidth + gap : 300;
                            return Math.min(4, Math.max(1, Math.floor(w / cardW)));
                        }

                        function update() {
                            const visible = getVisibleCount();
                            const maxIndex = Math.max(0, total - visible);
                            currentIndex = Math.min(currentIndex, maxIndex);
                            const offset = currentIndex * getCardWidth();
                            track.style.transform = 'translateX(' + offset + 'px)';
                            prevBtn.disabled = currentIndex <= 0;
                            nextBtn.disabled = currentIndex >= maxIndex;
                        }

                        prevBtn.addEventListener('click', () => { if (currentIndex > 0) { currentIndex--; update(); } });
                        nextBtn.addEventListener('click', () => {
                            const visible = getVisibleCount();
                            const maxIndex = Math.max(0, total - visible);
                            if (currentIndex < maxIndex) { currentIndex++; update(); }
                        });
                        update();
                        window.addEventListener('resize', () => update());
                    })();
                </script>
            @endif
        </div>
    </section>

    {{-- Scripts: Typing, Scroll Reveal, Count-up, Parallax --}}
    <script>
        (function () {
            const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // Hero typing
            const el = document.querySelector('[data-hero-typed]');
            if (el && !prefersReduced) {
                const full = (el.getAttribute('data-text') || '').trim();
                if (full) {
                    const cursor = el.nextElementSibling;
                    if (cursor && cursor.classList) cursor.classList.add('is-on');
                    el.textContent = '';
                    let i = 0;
                    const tick = () => {
                        i++;
                        el.textContent = full.slice(0, i);
                        if (i < full.length) setTimeout(tick, 26);
                        else setTimeout(() => { if (cursor && cursor.classList) cursor.classList.remove('is-on'); }, 900);
                    };
                    setTimeout(tick, 220);
                }
            }

            // Scroll reveal with stagger
            const revealEls = document.querySelectorAll('[data-reveal]');
            if (revealEls.length && !prefersReduced && 'IntersectionObserver' in window) {
                const obs = new IntersectionObserver((entries) => {
                    entries.forEach((e) => {
                        if (!e.isIntersecting) return;
                        const el = e.target;
                        const stagger = parseInt(el.getAttribute('data-stagger') || '0', 10) * 50;
                        setTimeout(() => el.classList.add('is-visible'), stagger);
                        obs.unobserve(el);
                    });
                }, { threshold: 0.1 });
                revealEls.forEach(el => obs.observe(el));
            } else if (revealEls.length) {
                revealEls.forEach(el => el.classList.add('is-visible'));
            }

            // Count-up for stats
            const statEls = document.querySelectorAll('[data-stat-counter]');
            if (statEls.length && !prefersReduced) {
                const statsSection = document.getElementById('stats-section');
                if (statsSection && 'IntersectionObserver' in window) {
                    const obs = new IntersectionObserver((entries) => {
                        entries.forEach((e) => {
                            if (!e.isIntersecting) return;
                            const els = e.target.querySelectorAll('[data-stat-counter]');
                            els.forEach((box) => {
                                const valEl = box.querySelector('.stat-value');
                                const target = parseInt(box.getAttribute('data-value') || '0', 10);
                                if (!valEl || target <= 0) { valEl && (valEl.textContent = target); return; }
                                const duration = 1500;
                                const start = performance.now();
                                const step = (now) => {
                                    const elapsed = now - start;
                                    const progress = Math.min(elapsed / duration, 1);
                                    const eased = 1 - Math.pow(1 - progress, 3);
                                    valEl.textContent = Math.round(target * eased);
                                    if (progress < 1) requestAnimationFrame(step);
                                    else valEl.textContent = target;
                                };
                                requestAnimationFrame(step);
                            });
                            obs.unobserve(e.target);
                        });
                    }, { threshold: 0.3 });
                    obs.observe(statsSection);
                } else {
                    statEls.forEach((box) => {
                        const valEl = box.querySelector('.stat-value');
                        if (valEl) valEl.textContent = box.getAttribute('data-value') || '0';
                    });
                }
            } else {
                statEls.forEach((box) => {
                    const valEl = box.querySelector('.stat-value');
                    if (valEl) valEl.textContent = box.getAttribute('data-value') || '0';
                });
            }

            // Parallax
            const parallaxEl = document.querySelector('[data-parallax]');
            if (parallaxEl && !prefersReduced) {
                let ticking = false;
                window.addEventListener('scroll', () => {
                    if (ticking) return;
                    ticking = true;
                    requestAnimationFrame(() => {
                        const rect = parallaxEl.closest('section').getBoundingClientRect();
                        const y = rect.top + rect.height / 2;
                        const factor = 0.15;
                        parallaxEl.style.transform = `translateY(${(window.innerHeight / 2 - y) * factor}px)`;
                        ticking = false;
                    });
                }, { passive: true });
            }
        })();
    </script>
@endsection
