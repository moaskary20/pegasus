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
                <span class="inline-flex items-center gap-1">
                    <span>⭐</span>
                    <span class="font-extrabold">{{ number_format((float) ($product->average_rating ?? 0), 1) }}</span>
                    <span class="text-slate-500">({{ (int) ($product->ratings_count ?? 0) }} تقييم)</span>
                </span>
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
</section>
@endsection
