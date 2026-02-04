@extends('layouts.site')

@section('title', $course->title . ' - ' . config('app.name'))

@push('head')
<meta name="description" content="{{ Str::limit(strip_tags($course->description ?? ''), 160) }}">
<meta property="og:title" content="{{ $course->title }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($course->description ?? ''), 200) }}">
<meta property="og:image" content="{{ $course->cover_image ?? asset('images/og-default.png') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">
<script type="application/ld+json">
@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Course',
        'name' => $course->title,
        'description' => Str::limit(strip_tags($course->description ?? ''), 500),
        'provider' => ['@type' => 'Organization', 'name' => config('app.name')],
        'offers' => ['@type' => 'Offer', 'price' => (float) ($course->offer_price ?? $course->price ?? 0), 'priceCurrency' => 'EGP'],
    ];
    if ($course->instructor) {
        $schema['instructor'] = ['@type' => 'Person', 'name' => $course->instructor->name];
    }
@endphp
{!! json_encode($schema, JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
    @if(session('notice'))
        <div class="max-w-7xl mx-auto px-4 pt-6" style="direction: rtl;">
            <div class="rounded-2xl border px-4 py-3 text-sm font-bold
                {{ (session('notice')['type'] ?? '') === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                {{ session('notice')['message'] ?? '' }}
            </div>
        </div>
    @endif
    @php
        $cartIds = session('cart', []);
        $cartIds = is_array($cartIds) ? array_map('intval', $cartIds) : [];
        $inCart = in_array((int) $course->id, $cartIds, true);

        $wishlistIds = session('course_wishlist', []);
        $wishlistIds = is_array($wishlistIds) ? array_map('intval', $wishlistIds) : [];
        $inWishlist = in_array((int) $course->id, $wishlistIds, true);

        $price = (float) ($course->offer_price ?? $course->price ?? 0);
        $originalPrice = (float) ($course->price ?? 0);
        $hasDiscount = $course->offer_price !== null && $originalPrice > 0 && $price > 0 && $price < $originalPrice;
        $discountPct = $hasDiscount ? (int) round((1 - ($price / $originalPrice)) * 100) : 0;
        
        // Get prices for each subscription type
        $priceOnce = $course->getPriceForSubscriptionType('once');
        $priceMonthly = $course->getPriceForSubscriptionType('monthly');
        $priceDaily = $course->getPriceForSubscriptionType('daily');

        $hours = intdiv($totalMinutes, 60);
        $mins = $totalMinutes % 60;

        $objectivesRaw = (string) ($course->objectives ?? '');
        $objectives = collect(preg_split("/\r\n|\n|\r/", $objectivesRaw))
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->values();

        $description = trim((string) ($course->description ?? ''));
        $level = trim((string) ($course->level ?? ''));
        $lessonProgressMap = $lessonProgressMap ?? [];
        $lastWatchedLesson = $lastWatchedLesson ?? null;
        $recentlyCompletedLessons = $recentlyCompletedLessons ?? [];
        $userCertificate = $userCertificate ?? null;
        $nextSuggestedCourse = $nextSuggestedCourse ?? null;
        $courseUrl = url()->current();
    @endphp

    {{-- عمود الصورة ثابت | تفاصيل الدورة تتمرر أسفله --}}
    <div class="max-w-7xl mx-auto px-4 py-10" style="direction: rtl;">
        <div class="grid lg:grid-cols-12 gap-8 items-start">
            {{-- عمود الصورة والسعر (ثابت عند التمرير) - فوق وأولاً على اليمين --}}
            <aside id="aside-price" class="lg:col-span-4 lg:order-1 order-2 lg:sticky lg:top-4 self-start">
                    <div class="rounded-3xl overflow-hidden border border-white/10 bg-white text-slate-900 shadow-2xl">
                        {{-- صورة الغلاف / فيديو المعاينة (يوتيوب أو ملف أو درس) --}}
                        <div class="relative aspect-video bg-slate-900 overflow-hidden group" x-data="{ showVideo: false }">
                            @if($course->preview_video_url)
                            {{-- فيديو المعاينة: يوتيوب أو ملف --}}
                            <div x-show="showVideo" x-cloak class="absolute inset-0 z-10 bg-black" style="display: none;">
                                @if($course->isPreviewVideoYoutube())
                                @php
                                    $embedUrl = ($course->previewLesson && $course->previewLesson->isYoutubeVideo()) 
                                        ? $course->previewLesson->youtube_embed_url 
                                        : $course->preview_youtube_embed_url;
                                @endphp
                                <iframe class="w-full h-full" src="{{ $embedUrl }}?autoplay=1" title="معاينة الدورة" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen x-ref="previewVideo"></iframe>
                                @else
                                <video class="w-full h-full object-contain" controls autoplay x-ref="previewVideo">
                                    <source src="{{ $course->preview_video_url }}" type="video/mp4">
                                    متصفحك لا يدعم تشغيل الفيديو.
                                </video>
                                @endif
                                <button @click="showVideo = false; $refs.previewVideo?.pause?.(); $refs.previewVideo?.contentWindow?.postMessage?.('{\"event\":\"command\",\"func\":\"pauseVideo\",\"args\":\"\"}', '*')" 
                                        class="absolute top-2 left-2 z-20 w-10 h-10 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            @endif
                            {{-- صورة الغلاف --}}
                            <div x-show="!showVideo" class="absolute inset-0">
                                @if($course->cover_image)
                                    <img src="{{ $course->cover_image }}" alt="{{ $course->title }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition duration-300" loading="lazy">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-[#3d195c] to-slate-800"></div>
                                @endif
                                <div class="absolute inset-0 bg-black/20"></div>
                                @if($course->preview_video_url)
                                    <button @click="showVideo = true" type="button" class="absolute inset-0 flex items-center justify-center cursor-pointer">
                                        <div class="w-16 h-16 rounded-full bg-white/95 flex items-center justify-center shadow-xl group-hover:scale-110 transition duration-300">
                                            <svg class="w-8 h-8 text-[#3d195c] ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                    </button>
                                @else
                                    <a href="#curriculum" class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-white/95 flex items-center justify-center shadow-xl group-hover:scale-110 transition duration-300">
                                            <svg class="w-8 h-8 text-[#3d195c] ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                    </a>
                                @endif
                                <div class="absolute bottom-3 right-3 left-3 text-center">
                                    <span class="text-xs font-bold text-white/90 bg-black/30 px-3 py-1.5 rounded-full">معاينة الدورة</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-5">
                            {{-- عمود السعر (مثل Udemy) --}}
                            <div class="flex flex-wrap items-baseline gap-2">
                                <div class="text-3xl font-extrabold text-slate-900">
                                    {{ $price > 0 ? number_format($price, 2) . ' ج.م' : 'مجاني' }}
                                </div>
                                @if($hasDiscount)
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg text-slate-400 line-through">{{ number_format($originalPrice, 2) }} ج.م</span>
                                        <span class="text-sm font-extrabold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700">خصم {{ $discountPct }}%</span>
                                    </div>
                                @endif
                            </div>

                            @if($isEnrolled && $enrollment)
                                @php
                                    $progress = (float) ($enrollment->progress_percentage ?? 0);
                                    $isCompleted = $enrollment->completed_at !== null;
                                @endphp
                                <div class="mt-5 p-4 rounded-2xl bg-[#3d195c]/5 border border-[#3d195c]/10">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="text-sm font-bold text-slate-700">تقدمك في الدورة</span>
                                        <span class="text-sm font-extrabold text-[#3d195c]">{{ number_format($progress, 0) }}%</span>
                                    </div>
                                    <div class="h-2.5 rounded-full bg-slate-200 overflow-hidden">
                                        <div
                                            class="h-full rounded-full {{ $isCompleted ? 'bg-emerald-500' : 'bg-[#3d195c]' }} transition-all duration-500"
                                            style="width: {{ min(100, $progress) }}%"
                                        ></div>
                                    </div>
                                    @if($isCompleted)
                                        <p class="mt-2 text-xs font-bold text-emerald-600 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            مكتملة
                                        </p>
                                    @endif
                                </div>
                            @endif

                            @if($isEnrolled && $userCertificate)
                                <a href="{{ route('site.course.certificate', $course) }}" class="mt-5 w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl border-2 border-emerald-500 text-emerald-700 font-extrabold hover:bg-emerald-50 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    تحميل الشهادة
                                </a>
                            @endif

                            @if($isEnrolled)
                                <a href="{{ route('site.course.chat', $course) }}" class="mt-3 w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-50 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    محادثة الدورة
                                </a>
                            @endif

                            @if($isEnrolled && ($lastWatchedLesson || count($recentlyCompletedLessons) > 0))
                                <div class="mt-5 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                                    <div class="text-xs font-extrabold text-slate-600 mb-3">روابط سريعة</div>
                                    @if($lastWatchedLesson)
                                        <a href="{{ route('site.course.lesson.show', [$course, $lastWatchedLesson]) }}" class="block text-sm font-bold text-[#3d195c] hover:underline mb-2">
                                            استمر من حيث توقفت: {{ Str::limit($lastWatchedLesson->title, 25) }}
                                        </a>
                                    @endif
                                    @foreach($recentlyCompletedLessons as $rcl)
                                        <a href="{{ route('site.course.lesson.show', [$course, $rcl]) }}" class="block text-xs text-slate-600 hover:text-[#3d195c] mb-1 truncate">
                                            ✓ {{ Str::limit($rcl->title, 30) }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-5 grid gap-2">
                                @if($isEnrolled)
                                    <a href="{{ $firstLesson ? route('site.course.lesson.show', [$course, $firstLesson]) : url('/admin/my-courses') }}" class="w-full inline-flex items-center justify-center px-5 py-3.5 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition text-base">
                                        {{ ($enrollment && $enrollment->completed_at) ? 'مراجعة الدورة' : 'ابدأ التعلّم الآن' }}
                                    </a>
                                @else
                                    @if($inCart)
                                        <a href="{{ route('site.cart') }}" class="w-full inline-flex items-center justify-center px-5 py-3.5 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition text-base">
                                            موجود في السلة — افتح السلة
                                        </a>
                                    @else
                                        {{-- Subscription Options Modal Trigger --}}
                                        <div x-data="{ open: false }">
                                            <button @click="open = true" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition text-base">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                إضافة إلى السلة
                                            </button>
                                            
                                            {{-- Modal --}}
                                            <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                                <div class="flex min-h-screen items-center justify-center p-4">
                                                    <div @click="open = false" class="fixed inset-0 bg-black/50"></div>
                                                    <div class="relative bg-white rounded-3xl shadow-2xl max-w-md w-full p-6" @click.stop>
                                                        <div class="flex items-center justify-between mb-4">
                                                            <h3 class="text-lg font-extrabold text-slate-900">اختر خطة الاشتراك</h3>
                                                            <button @click="open = false" class="text-slate-400 hover:text-slate-600">
                                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                        
                                                        <form action="{{ route('site.course.subscribe.add_to_cart', $course) }}" method="POST">
                                                            @csrf
                                                            
                                                            {{-- Subscription Options --}}
                                                            <div class="space-y-3">
                                                                <label class="block cursor-pointer">
                                                                    <input type="radio" name="subscription_type" value="once" class="hidden peer" checked>
                                                                    <div class="p-4 rounded-2xl border-2 border-slate-200 peer-checked:border-[#3d195c] peer-checked:bg-[#3d195c]/5 transition">
                                                                        <div class="flex items-center justify-between">
                                                                            <div>
                                                                                <div class="font-bold text-slate-900">اشتراك واحد</div>
                                                                                <div class="text-sm text-slate-600">وصول كامل لمدة 120 يوم</div>
                                                                            </div>
                                                                            <div class="font-extrabold text-[#3d195c]">{{ number_format($priceOnce, 2) }} ج.م</div>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                                
                                                                <label class="block cursor-pointer">
                                                                    <input type="radio" name="subscription_type" value="monthly" class="hidden peer">
                                                                    <div class="p-4 rounded-2xl border-2 border-slate-200 peer-checked:border-[#3d195c] peer-checked:bg-[#3d195c]/5 transition">
                                                                        <div class="flex items-center justify-between">
                                                                            <div>
                                                                                <div class="font-bold text-slate-900">اشتراك شهري</div>
                                                                                <div class="text-sm text-slate-600">تجديد تلقائي كل شهر</div>
                                                                            </div>
                                                                            <div class="font-extrabold text-[#3d195c]">{{ number_format($priceMonthly, 2) }} ج.م</div>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                                
                                                                <label class="block cursor-pointer">
                                                                    <input type="radio" name="subscription_type" value="daily" class="hidden peer">
                                                                    <div class="p-4 rounded-2xl border-2 border-slate-200 peer-checked:border-[#3d195c] peer-checked:bg-[#3d195c]/5 transition">
                                                                        <div class="flex items-center justify-between">
                                                                            <div>
                                                                                <div class="font-bold text-slate-900">اشتراك يومي</div>
                                                                                <div class="text-sm text-slate-600">درس واحد يومياً</div>
                                                                            </div>
                                                                            <div class="font-extrabold text-[#3d195c]">{{ number_format($priceDaily, 2) }} ج.م</div>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                            
                                                            <button type="submit" class="w-full mt-4 inline-flex items-center justify-center px-5 py-3.5 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                                                                إضافة إلى السلة
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <a href="{{ route('site.course.subscribe', $course) }}" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl border-2 border-[#3d195c] text-[#3d195c] font-extrabold hover:bg-[#3d195c]/5 transition">
                                        اشتري الآن
                                    </a>
                                @endif
                                <div class="mt-2 flex gap-2">
                                    @if($inWishlist)
                                        <form method="POST" action="{{ route('site.wishlist.courses.remove', $course) }}">
                                            @csrf
                                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-rose-50 text-rose-600 font-extrabold hover:bg-rose-100 transition">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                                إزالة من قائمة الرغبات
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('site.wishlist.courses.add', $course) }}">
                                            @csrf
                                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl border border-slate-200 text-slate-700 font-extrabold hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                                أضف إلى قائمة الرغبات
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-sm font-extrabold text-slate-900">هذا الكورس يشمل</div>
                                <div class="mt-3 space-y-2 text-sm text-slate-700">
                                    <div class="flex items-center justify-between gap-3">
                                        <span>عدد الدروس</span>
                                        <span class="font-extrabold">{{ number_format($lessonsCount) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span>المدة الإجمالية</span>
                                        <span class="font-extrabold">{{ $hours }}س {{ $mins }}د</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span>معاينة مجانية</span>
                                        <span class="font-extrabold">{{ number_format($previewLessonsCount) }} درس</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span>شهادة إتمام</span>
                                        <span class="font-extrabold">متاحة</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </aside>

            {{-- اسم الدورة وتفاصيلها (أسفل عمود الصورة - يتمرر عند السكرول) --}}
            <div class="lg:col-span-8 lg:order-2 order-1">
                {{-- الهيدر الداكن --}}
                <div class="bg-[#2c004d] text-white rounded-3xl p-6 lg:p-8 mb-6">
                    <div class="text-xs text-white/80">
                        <a href="{{ url('/') }}" class="hover:underline">الرئيسية</a>
                        <span class="mx-1">/</span>
                        <a href="{{ route('site.courses') }}" class="hover:underline">الدورات</a>
                        @if($course->category)
                            <span class="mx-1">/</span>
                            <a href="{{ route('site.courses', ['category' => (int) $course->category->id]) }}" class="hover:underline">{{ $course->category->name }}</a>
                        @endif
                    </div>

                    <h1 class="mt-3 text-2xl md:text-3xl font-extrabold leading-snug">
                        {{ $course->title }}
                    </h1>

                    @if($description)
                        <p class="mt-3 text-white/90 leading-relaxed line-clamp-3">
                            {{ $description }}
                        </p>
                    @endif

                    <div class="mt-5 flex flex-wrap items-center gap-3 text-sm text-white/90">
                        <div class="inline-flex items-center gap-2">
                            <span class="font-extrabold text-white">{{ number_format($avgRating, 1) }}</span>
                            <span aria-hidden="true">⭐</span>
                            <span class="text-white/75">({{ number_format((int) $totalReviews) }} تقييم)</span>
                        </div>
                        <span class="text-white/40">•</span>
                        <div class="text-white/85">
                            بواسطة
                            <span class="font-extrabold text-white">{{ $course->instructor?->name }}</span>
                        </div>
                        @if($level)
                            <span class="text-white/40">•</span>
                            <div class="text-white/85">المستوى: <span class="font-extrabold text-white">{{ $level }}</span></div>
                        @endif
                        <span class="text-white/40">•</span>
                        <div class="text-white/85">آخر تحديث: <span class="font-extrabold text-white">{{ optional($course->updated_at)->format('Y-m-d') }}</span></div>
                    </div>
                    <div class="mt-5 flex flex-wrap items-center gap-2" x-data="{ copied: false }">
                        <span class="text-xs text-white/80">مشاركة:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($courseUrl) }}" target="_blank" rel="noopener" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition" aria-label="فيسبوك">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode($courseUrl) }}&text={{ urlencode($course->title) }}" target="_blank" rel="noopener" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition" aria-label="تويتر">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($course->title . ' ' . $courseUrl) }}" target="_blank" rel="noopener" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition" aria-label="واتساب">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        <button @click="navigator.clipboard.writeText('{{ addslashes($courseUrl) }}'); copied=true; setTimeout(()=>copied=false, 2000)" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition" aria-label="نسخ الرابط">
                            <span x-show="!copied" x-cloak><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></span>
                            <span x-show="copied" x-cloak class="text-xs font-bold">تم!</span>
                        </button>
                    </div>
                </div>

                {{-- تفاصيل الدورة (ماذا ستتعلم، المحتوى، إلخ) --}}
                {{-- What you'll learn --}}
                <div class="rounded-3xl border bg-white p-6">
                    <h2 class="text-lg font-extrabold text-slate-900">ماذا ستتعلم؟</h2>
                    @if($objectives->count())
                        <div class="mt-4 grid sm:grid-cols-2 gap-3">
                            @foreach($objectives as $obj)
                                <div class="flex items-start gap-2 text-sm text-slate-700">
                                    <span class="mt-0.5 text-[#3d195c] font-extrabold">✓</span>
                                    <span class="leading-relaxed">{{ $obj }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-sm text-slate-600">سيتم إضافة أهداف هذا الكورس قريبًا.</p>
                    @endif
                </div>

                @if($isEnrolled && trim((string) ($course->announcement ?? '')))
                    <div class="mt-6 rounded-3xl border-2 border-amber-200 bg-amber-50 p-6">
                        <h3 class="text-sm font-extrabold text-amber-800 mb-2">إعلان من المدرس</h3>
                        <p class="text-sm text-amber-900 leading-relaxed whitespace-pre-line">{{ $course->announcement }}</p>
                    </div>
                @endif

                {{-- Curriculum - روابط للدروس (صفحة الدرس تعرض الفيديو والمحتوى) --}}
                <div id="curriculum" class="mt-6 rounded-3xl border bg-white overflow-hidden" x-data="{ open: 0, search: '' }">
                    <div class="px-6 py-5 border-b bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="text-lg font-extrabold text-slate-900">محتوى الكورس</div>
                            <div class="text-xs text-slate-600 mt-1">{{ number_format((int) $course->sections->count()) }} قسم • {{ number_format($lessonsCount) }} درس</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="text" x-model="search" placeholder="ابحث في الدروس..." class="flex-1 min-w-0 px-3 py-2 text-sm rounded-xl border border-slate-200 focus:border-[#3d195c] focus:ring-1 focus:ring-[#3d195c]/20">
                            <a href="#reviews" class="text-xs font-extrabold text-[#3d195c] hover:underline shrink-0">التقييمات</a>
                        </div>
                    </div>

                    <div class="divide-y">
                        @foreach($course->sections as $i => $section)
                            <div>
                                <button
                                    type="button"
                                    class="w-full px-6 py-4 flex items-center justify-between gap-4 text-right hover:bg-slate-50 transition"
                                    @click="open = (open === {{ $i }} ? -1 : {{ $i }})"
                                >
                                    <div class="min-w-0">
                                        <div class="font-extrabold text-slate-900 line-clamp-1">{{ $section->title }}</div>
                                        <div class="text-xs text-slate-600 mt-1">{{ number_format((int) $section->lessons->count()) }} درس</div>
                                    </div>
                                    <div class="shrink-0 text-[#3d195c] font-extrabold">
                                        <span x-show="open !== {{ $i }}">+</span>
                                        <span x-show="open === {{ $i }}" x-cloak>−</span>
                                    </div>
                                </button>

                                <div x-show="open === {{ $i }}" x-cloak x-transition.opacity.duration.150ms class="px-6 pb-5">
                                    <div class="space-y-2">
                                        @foreach($section->lessons as $lesson)
                                            @php 
                                                $isPreview = (bool) ($lesson->is_free ?? false) || (bool) ($lesson->is_free_preview ?? false);
                                                $canWatch = $isEnrolled || $isPreview;
                                                $isCompleted = ($lessonProgressMap[$lesson->id] ?? [])['completed'] ?? false;
                                                $hasVideo = !empty($lesson->video_url);
                                                $hasQuiz = $lesson->quiz !== null;
                                                $hasFiles = $lesson->relationLoaded('files') ? $lesson->files->count() > 0 : false;
                                            @endphp
                                            <div x-show="!search || '{{ addslashes($lesson->title) }}'.toLowerCase().includes(search.toLowerCase())"
                                                 x-transition
                                                 class="{{ $isCompleted ? 'rounded-2xl border border-emerald-200 bg-emerald-50/50' : '' }}">
                                            @if($canWatch)
                                            <a href="{{ route('site.course.lesson.show', [$course, $lesson]) }}"
                                               class="block w-full text-right flex items-center justify-between gap-3 rounded-2xl border px-4 py-3 hover:bg-slate-50 hover:border-[#3d195c]/30 transition {{ $isCompleted ? 'bg-white/80 border-emerald-200' : 'bg-white' }}">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-bold text-slate-800 line-clamp-1">{{ $lesson->title }}</div>
                                                    <div class="text-xs text-slate-500 mt-1">
                                                        @if($lesson->duration_minutes)
                                                            {{ (int) $lesson->duration_minutes }} دقيقة
                                                        @else
                                                            —
                                                        @endif
                                                        @if($isCompleted)
                                                            <span class="text-emerald-600 font-bold"> • مكتمل</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="shrink-0 flex items-center gap-2">
                                                    @if($isCompleted)
                                                        <span class="text-emerald-600">✓</span>
                                                    @endif
                                                    <span class="text-xs font-extrabold {{ $isPreview ? 'text-emerald-700' : 'text-slate-500' }}">
                                                        {{ $isPreview ? 'معاينة' : '' }}
                                                    </span>
                                                    @if($hasVideo)
                                                    <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                    @endif
                                                    @if($hasQuiz)
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-600">اختبار</span>
                                                    @endif
                                                    @if($hasFiles)
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-600">ملفات</span>
                                                    @endif
                                                </div>
                                            </a>
                                            @else
                                            <div class="flex items-center justify-between gap-3 rounded-2xl border bg-slate-50 px-4 py-3 opacity-75">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-bold text-slate-600 line-clamp-1">{{ $lesson->title }}</div>
                                                    <div class="text-xs text-slate-500 mt-1">{{ (int) $lesson->duration_minutes }} دقيقة</div>
                                                </div>
                                                <div class="shrink-0 text-xs font-extrabold text-slate-500">مقفل</div>
                                            </div>
                                            @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Description --}}
                <div class="mt-6 rounded-3xl border bg-white p-6">
                    <h2 class="text-lg font-extrabold text-slate-900">وصف الكورس</h2>
                    @if($description)
                        <div class="mt-3 text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $description }}
                        </div>
                    @else
                        <p class="mt-3 text-sm text-slate-600">لا يوجد وصف مفصل حتى الآن.</p>
                    @endif
                </div>

                {{-- Instructor --}}
                <div class="mt-6 rounded-3xl border bg-white p-6">
                    <h2 class="text-lg font-extrabold text-slate-900">عن المدرّس</h2>
                    <div class="mt-4 flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 overflow-hidden shrink-0">
                            @if($course->instructor?->avatar)
                                <img src="{{ $course->instructor->avatar_url }}" alt="{{ $course->instructor->name }}" class="w-full h-full object-cover" loading="lazy">
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-extrabold text-slate-900">{{ $course->instructor?->name }}</div>
                            <div class="text-xs text-slate-600 mt-1">
                                {{ $course->instructor?->job }}
                                @if($course->instructor?->city)
                                    <span class="mx-1">•</span>{{ $course->instructor?->city }}
                                @endif
                            </div>
                            @if(is_array($course->instructor?->skills) && count($course->instructor->skills))
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach(array_slice($course->instructor->skills, 0, 10) as $sk)
                                        <span class="text-[11px] font-extrabold px-3 py-1 rounded-2xl bg-slate-100 text-slate-700">{{ $sk }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Reviews --}}
                <div id="reviews" class="mt-6 rounded-3xl border bg-white overflow-hidden">
                    <div class="px-6 py-5 border-b bg-slate-50 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-lg font-extrabold text-slate-900">التقييمات</div>
                            <div class="text-xs text-slate-600 mt-1">{{ number_format((int) $totalReviews) }} تقييم • متوسط {{ number_format($avgRating, 1) }}</div>
                        </div>
                    </div>

                    @if($isEnrolled && auth()->check())
                        <div class="p-6 border-b bg-slate-50/50" x-data="{
                            stars: {{ old('stars', $userRating?->stars ?? 0) }},
                            review: {{ json_encode(old('review', $userRating?->review ?? '')) }},
                            submitting: false
                        }">
                            <div class="text-sm font-extrabold text-slate-900 mb-3">
                                {{ $userRating ? 'تعديل تقييمك' : 'قيّم هذه الدورة' }}
                            </div>
                            <form action="{{ route('site.course.rate', $course) }}" method="POST" @submit="submitting = true">
                                @csrf
                                <div class="flex flex-wrap gap-2 mb-3">
                                    @foreach([1,2,3,4,5] as $s)
                                        <button type="button" @click="stars = {{ $s }}" class="p-1 rounded-lg transition"
                                            :class="stars >= {{ $s }} ? 'text-amber-400' : 'text-slate-300 hover:text-amber-300'">
                                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="stars" :value="stars">
                                @error('stars')
                                    <p class="text-sm text-rose-600 mb-2">{{ $message }}</p>
                                @enderror
                                <textarea name="review" rows="3" maxlength="2000" placeholder="اكتب تعليقك (اختياري)..." x-model="review"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-[#3d195c] focus:ring-2 focus:ring-[#3d195c]/20 outline-none transition"></textarea>
                                <div class="mt-3 flex justify-end">
                                    <button type="submit" :disabled="stars < 1 || submitting"
                                        class="px-6 py-2.5 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                        {{ $userRating ? 'تحديث التقييم' : 'إرسال التقييم' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div class="p-6">
                        @php
                            $den = max(1, (int) $totalReviews);
                            $counts = [
                                5 => (int) ($starsCounts[5] ?? 0),
                                4 => (int) ($starsCounts[4] ?? 0),
                                3 => (int) ($starsCounts[3] ?? 0),
                                2 => (int) ($starsCounts[2] ?? 0),
                                1 => (int) ($starsCounts[1] ?? 0),
                            ];
                        @endphp

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-3xl font-extrabold text-slate-900">{{ number_format($avgRating, 1) }}</div>
                                <div class="text-sm text-slate-600 mt-1">متوسط التقييم</div>
                                <div class="mt-4 space-y-2">
                                    @foreach($counts as $stars => $c)
                                        @php $pct = (int) round(($c / $den) * 100); @endphp
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 text-xs font-extrabold text-slate-700">{{ $stars }} ⭐</div>
                                            <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full bg-[#3d195c]" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <div class="w-10 text-xs font-bold text-slate-600 text-left">{{ $pct }}%</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="space-y-3">
                                @forelse($course->ratings->take(6) as $r)
                                    <div class="rounded-3xl border p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-sm font-extrabold text-slate-900">{{ $r->user?->name ?? 'مستخدم' }}</div>
                                                <div class="text-xs text-slate-500 mt-1">{{ optional($r->created_at)->format('Y-m-d') }}</div>
                                            </div>
                                            <div class="shrink-0 text-xs font-extrabold text-[#3d195c]">{{ (int) $r->stars }} ⭐</div>
                                        </div>
                                        @if($r->review)
                                            <div class="mt-3 text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $r->review }}</div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="rounded-3xl border p-6 text-center text-sm text-slate-600">
                                        لا توجد تقييمات بعد.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- الدورة التالية المقترحة + دورات مشابهة --}}
                <div class="mt-6 rounded-3xl border bg-white p-6">
                    @if($nextSuggestedCourse && $nextSuggestedCourse->id !== $course->id)
                        <div class="mb-6 p-4 rounded-2xl bg-[#3d195c]/5 border border-[#3d195c]/20">
                            <div class="text-xs font-bold text-[#3d195c] mb-2">الدورة التالية المقترحة</div>
                            <a href="{{ route('site.course.show', $nextSuggestedCourse) }}" class="flex items-center gap-4 group">
                                <div class="w-24 h-16 rounded-xl bg-slate-100 overflow-hidden shrink-0">
                                    @if($nextSuggestedCourse->cover_image)
                                        <img src="{{ $nextSuggestedCourse->cover_image }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="font-extrabold text-slate-900 line-clamp-2 group-hover:text-[#3d195c]">{{ $nextSuggestedCourse->title }}</div>
                                    <div class="text-xs text-slate-600 mt-1">{{ $nextSuggestedCourse->instructor?->name }}</div>
                                    <div class="text-xs font-bold text-[#3d195c] mt-2">عرض الدورة ←</div>
                                </div>
                            </a>
                        </div>
                    @endif
                    <div class="text-sm font-extrabold text-slate-900">دورات مشابهة</div>
                    <div class="mt-4 space-y-3">
                        @forelse($relatedCourses as $rc)
                            <a href="{{ route('site.course.show', $rc) }}" class="group flex items-center gap-3 rounded-3xl border p-3 hover:bg-slate-50 transition">
                                <div class="w-20 h-14 rounded-2xl bg-slate-100 overflow-hidden shrink-0">
                                    @if($rc->cover_image)
                                        <img src="{{ $rc->cover_image }}" alt="{{ $rc->title }}" class="w-full h-full object-cover" loading="lazy">
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-slate-900 line-clamp-2 group-hover:text-[#3d195c] transition">{{ $rc->title }}</div>
                                    <div class="text-xs text-slate-600 mt-1">{{ $rc->instructor?->name }}</div>
                                    <div class="mt-2 flex items-center justify-between">
                                        <div class="text-xs text-slate-500">⭐ {{ number_format((float) ($rc->rating ?? 0), 1) }}</div>
                                        <div class="text-xs font-extrabold text-slate-900">
                                            @php $rp = (float) ($rc->offer_price ?? $rc->price ?? 0); @endphp
                                            {{ $rp > 0 ? number_format($rp, 2) . ' ج.م' : 'مجاني' }}
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-sm text-slate-600">لا توجد دورات مشابهة حالياً.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sticky CTA للجوال (غير المسجلين) --}}
    @if(!$isEnrolled)
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 p-4 bg-white/95 backdrop-blur border-t shadow-[0_-4px_20px_rgba(0,0,0,0.1)]" style="direction: rtl;">
        <div class="max-w-7xl mx-auto flex items-center gap-4">
            <div class="flex-1 min-w-0">
                <div class="font-extrabold text-slate-900 truncate">{{ $course->title }}</div>
                <div class="text-sm font-bold text-[#3d195c]">
                    {{ $price > 0 ? number_format($price, 2) . ' ج.م' : 'مجاني' }}
                </div>
            </div>
            @if($inCart)
                <a href="{{ route('site.cart') }}" class="shrink-0 px-6 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold">
                    افتح السلة
                </a>
            @else
                <a href="#aside-price" class="shrink-0 px-6 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold">
                    اشترك الآن
                </a>
            @endif
        </div>
    </div>
    <div class="lg:hidden h-20"></div>
    @endif
@endsection

