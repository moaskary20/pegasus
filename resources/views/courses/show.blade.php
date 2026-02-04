@extends('layouts.site')

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
    @endphp

    {{-- عمود الصورة ثابت | تفاصيل الدورة تتمرر أسفله --}}
    <div class="max-w-7xl mx-auto px-4 py-10" style="direction: rtl;">
        <div class="grid lg:grid-cols-12 gap-8 items-start">
            {{-- عمود الصورة والسعر (ثابت عند التمرير) - فوق وأولاً على اليمين --}}
            <aside class="lg:col-span-4 lg:order-1 order-2 lg:sticky lg:top-4 self-start">
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

                            <div class="mt-5 grid gap-2">
                                @if($isEnrolled)
                                    <a href="{{ $firstLesson ? route('site.course.lesson.show', [$course, $firstLesson]) : url('/admin/my-courses') }}" class="w-full inline-flex items-center justify-center px-5 py-3.5 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition text-base">
                                        ابدأ التعلّم الآن
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

                {{-- Curriculum - روابط للدروس (صفحة الدرس تعرض الفيديو والمحتوى) --}}
                <div id="curriculum" class="mt-6 rounded-3xl border bg-white overflow-hidden" x-data="{ open: 0 }">
                    <div class="px-6 py-5 border-b bg-slate-50 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-lg font-extrabold text-slate-900">محتوى الكورس</div>
                            <div class="text-xs text-slate-600 mt-1">{{ number_format((int) $course->sections->count()) }} قسم • {{ number_format($lessonsCount) }} درس</div>
                        </div>
                        <a href="#reviews" class="text-xs font-extrabold text-[#3d195c] hover:underline">التقييمات</a>
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
                                            @endphp
                                            @if($canWatch)
                                            <a href="{{ route('site.course.lesson.show', [$course, $lesson]) }}"
                                               class="block w-full text-right flex items-center justify-between gap-3 rounded-2xl border bg-white px-4 py-3 hover:bg-slate-50 hover:border-[#3d195c]/30 transition">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-bold text-slate-800 line-clamp-1">{{ $lesson->title }}</div>
                                                    <div class="text-xs text-slate-500 mt-1">
                                                        @if($lesson->duration_minutes)
                                                            {{ (int) $lesson->duration_minutes }} دقيقة
                                                        @else
                                                            —
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="shrink-0 flex items-center gap-2">
                                                    <span class="text-xs font-extrabold {{ $isPreview ? 'text-emerald-700' : 'text-slate-500' }}">
                                                        {{ $isPreview ? 'معاينة' : '' }}
                                                    </span>
                                                    @if($lesson->video_url)
                                                    <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
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
                                <img src="{{ asset('storage/' . ltrim((string) $course->instructor->avatar, '/')) }}" alt="{{ $course->instructor->name }}" class="w-full h-full object-cover" loading="lazy">
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

                {{-- دورات مشابهة --}}
                <div class="mt-6 rounded-3xl border bg-white p-6">
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
@endsection

