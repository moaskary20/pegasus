@extends('layouts.site')

@section('content')
@php
    $imgUrl = $product->main_image ? asset('storage/' . ltrim($product->main_image, '/')) : ($product->images->first()?->url ?? null);
@endphp
<section class="max-w-7xl mx-auto px-4 py-10" style="direction: rtl;">
    <div class="mb-6 text-xs text-slate-500">
        <a href="{{ route('site.store') }}" class="hover:text-[#2c004d]">المتجر</a>
        @if($product->category)
            <span class="mx-1">/</span>
            <a href="{{ route('site.store', ['category' => $product->category->parent_id ?? $product->category->id]) }}" class="hover:text-[#2c004d]">{{ $product->category->parent?->name ?? $product->category->name }}</a>
        @endif
    </div>

    @if(session('notice'))
        <div class="mb-6 rounded-2xl border px-4 py-3 text-sm font-semibold {{ (session('notice')['type'] ?? '') === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}">
            {{ session('notice')['message'] ?? '' }}
        </div>
    @endif

    <div class="grid lg:grid-cols-12 gap-8">
        {{-- Image --}}
        <div class="lg:col-span-5">
            <div class="rounded-3xl border bg-white overflow-hidden aspect-square">
                @if($imgUrl)
                    <img src="{{ $imgUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover" loading="lazy">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-slate-100">
                        <svg class="w-24 h-24 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6 6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>

        {{-- Info --}}
        <div class="lg:col-span-7">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">{{ $product->name }}</h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                <a href="#reviews" class="inline-flex items-center gap-1 hover:underline">
                    <span>⭐</span>
                    <span class="font-extrabold">{{ number_format((float) ($product->average_rating ?? 0), 1) }}</span>
                    <span class="text-slate-500">({{ (int) ($product->ratings_count ?? 0) }} تقييم)</span>
                </a>
                @if($product->category)
                    <span class="text-slate-500">•</span>
                    <span class="text-slate-600">{{ $product->category->name }}</span>
                @endif
            </div>

            <div class="mt-6 flex items-baseline gap-3">
                <span class="text-2xl font-extrabold text-slate-900">
                    {{ (float) $product->price > 0 ? number_format((float) $product->price, 2) . ' ج.م' : 'مجاني' }}
                </span>
                @if($product->compare_price && $product->compare_price > $product->price)
                    <span class="text-lg text-slate-400 line-through">{{ number_format((float) $product->compare_price, 2) }} ج.م</span>
                    <span class="px-2 py-0.5 rounded-lg bg-rose-100 text-rose-700 text-sm font-bold">خصم {{ (int) ($product->discount_percentage ?? 0) }}%</span>
                @endif
            </div>

            @if($product->short_description)
                <p class="mt-4 text-slate-600 leading-relaxed">{{ $product->short_description }}</p>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                @if($product->isInStock())
                    <form action="{{ route('site.store.add-to-cart', $product) }}" method="POST" class="inline-flex">
                        @csrf
                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->track_quantity ? $product->quantity : 999 }}" class="w-16 rounded-xl border border-slate-200 px-2 py-2 text-center text-sm" style="direction: ltr;">
                        <button type="submit" class="mr-2 inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                            إضافة إلى السلة
                        </button>
                    </form>
                @else
                    <span class="px-6 py-3 rounded-2xl bg-slate-200 text-slate-600 font-extrabold">غير متوفر</span>
                @endif

                @auth
                    @if($inWishlist)
                        <form method="POST" action="{{ route('site.wishlist.products.remove', $product) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-rose-50 text-rose-600 font-extrabold hover:bg-rose-100 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                إزالة من المفضلة
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('site.wishlist.products.add', $product) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl border border-slate-200 text-slate-700 font-extrabold hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                أضف إلى المفضلة
                            </button>
                        </form>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    @if($product->description)
        <div class="mt-12 rounded-3xl border bg-white p-6">
            <h2 class="text-lg font-extrabold text-slate-900">الوصف</h2>
            <div class="mt-4 text-slate-600 leading-relaxed prose prose-slate max-w-none">
                {!! nl2br(e($product->description)) !!}
            </div>
        </div>
    @endif

    {{-- التقييمات --}}
    <div id="reviews" class="mt-12 rounded-3xl border bg-white overflow-hidden">
        <div class="px-6 py-5 border-b bg-slate-50 flex items-center justify-between gap-3">
            <div>
                <div class="text-lg font-extrabold text-slate-900">التقييمات</div>
                <div class="text-xs text-slate-600 mt-1">{{ number_format($totalReviews ?? 0) }} تقييم • متوسط {{ number_format($avgRating ?? 0, 1) }}</div>
            </div>
        </div>

        @auth
            <div class="p-6 border-b bg-slate-50/50" x-data="{
                stars: {{ old('rating', $userReview?->rating ?? 0) }},
                comment: {{ json_encode(old('comment', $userReview?->comment ?? '')) }},
                submitting: false
            }">
                <div class="text-sm font-extrabold text-slate-900 mb-3">
                    {{ $userReview ? 'تعديل تقييمك' : 'قيّم هذا المنتج' }}
                </div>
                <form action="{{ route('site.store.product.rate', $product) }}" method="POST" @submit="submitting = true">
                    @csrf
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach([1,2,3,4,5] as $s)
                            <button type="button" @click="stars = {{ $s }}" class="p-1 rounded-lg transition"
                                :class="stars >= {{ $s }} ? 'text-amber-400' : 'text-slate-300 hover:text-amber-300'">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="rating" :value="stars">
                    @error('rating')
                        <p class="text-sm text-rose-600 mb-2">{{ $message }}</p>
                    @enderror
                    <textarea name="comment" rows="3" maxlength="2000" placeholder="اكتب تعليقك (اختياري)..." x-model="comment"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-[#3d195c] focus:ring-2 focus:ring-[#3d195c]/20 outline-none transition"></textarea>
                    <div class="mt-3 flex justify-end">
                        <button type="submit" :disabled="stars < 1 || submitting"
                            class="px-6 py-2.5 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            {{ $userReview ? 'تحديث التقييم' : 'إرسال التقييم' }}
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="p-6 border-b bg-slate-50/50">
                <p class="text-sm text-slate-600">
                    <a href="{{ route('site.auth') }}?intended={{ urlencode(route('site.store.product', $product)) }}" class="font-bold text-[#3d195c] hover:underline">سجّل الدخول</a>
                    لتقييم هذا المنتج.
                </p>
            </div>
        @endauth

        <div class="p-6">
            @php
                $den = max(1, (int) ($totalReviews ?? 0));
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
                    <div class="text-3xl font-extrabold text-slate-900">{{ number_format($avgRating ?? 0, 1) }}</div>
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
                    @forelse($product->approvedReviews->take(6) as $r)
                        <div class="rounded-3xl border p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="text-sm font-extrabold text-slate-900">{{ $r->user?->name ?? 'مستخدم' }}</div>
                                        @if($r->is_verified_purchase)
                                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-bold">شراء مؤكد</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1">{{ optional($r->created_at)->format('Y-m-d') }}</div>
                                </div>
                                <div class="shrink-0 text-xs font-extrabold text-[#3d195c]">{{ (int) $r->rating }} ⭐</div>
                            </div>
                            @if($r->comment)
                                <div class="mt-3 text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $r->comment }}</div>
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
</section>
@endsection
