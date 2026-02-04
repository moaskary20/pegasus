@props(['product', 'staggerIndex' => 0, 'inSlider' => false, 'inWishlist' => false])
@php
    $product = $product ?? null;
    if (!$product) return;
    $imgUrl = $product->main_image ? asset('storage/' . ltrim($product->main_image, '/')) : ($product->images->first() ? $product->images->first()->url : null);
    $baseClass = 'group rounded-2xl border bg-white overflow-hidden hover:shadow-xl hover:scale-[1.02] transition-all duration-300';
    $revealClass = $inSlider ? '' : 'reveal-item';
@endphp
<div
    {{ $attributes->merge(['class' => $baseClass . ($revealClass ? ' ' . $revealClass : '')]) }}
    @if(!$inSlider) data-reveal data-stagger="{{ $staggerIndex }}" @endif
>
    <div class="relative aspect-[16/10] bg-slate-100 overflow-hidden">
        <x-wishlist-heart-product :product="$product" :in-wishlist="$inWishlist" />
        <a href="{{ route('site.store.product', $product) }}" class="block w-full h-full">
            @if($imgUrl)
                <img
                    src="{{ $imgUrl }}"
                    alt="{{ $product->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                />
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-200 to-slate-100">
                    <svg class="w-16 h-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6 6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
        </a>
        @if($product->discount_percentage ?? null)
            <span class="absolute top-2 right-2 px-2 py-1 rounded-lg bg-rose-500 text-white text-xs font-bold">خصم {{ (int) $product->discount_percentage }}%</span>
        @endif
        @if($product->is_featured ?? false)
            <span class="absolute bottom-2 right-2 px-2 py-1 rounded-lg bg-[#3d195c] text-white text-xs font-bold">مميز</span>
        @endif
    </div>
    <a href="{{ route('site.store.product', $product) }}" class="block p-4">
        <div class="text-sm font-extrabold text-slate-900 line-clamp-2 group-hover:text-[#3d195c] transition-colors">{{ $product->name }}</div>
        <div class="mt-2 text-xs text-slate-500">{{ $product->category?->name ?? '—' }}</div>
        <div class="mt-3 flex items-center justify-between">
            <div class="flex items-center gap-1 text-xs">
                @if(($product->average_rating ?? 0) > 0)
                    <span>⭐</span>
                    <span class="font-extrabold text-slate-900">{{ number_format((float) ($product->average_rating ?? 0), 1) }}</span>
                @endif
            </div>
            <div class="text-sm font-extrabold text-slate-900">
                @if($product->compare_price && (float) $product->compare_price > (float) $product->price)
                    <span class="text-slate-400 line-through text-xs">{{ number_format((float) $product->compare_price, 2) }}</span>
                    <span class="text-rose-600">{{ number_format((float) $product->price, 2) }} ج.م</span>
                @else
                    {{ (float) $product->price > 0 ? number_format((float) $product->price, 2) . ' ج.م' : 'مجاني' }}
                @endif
            </div>
        </div>
    </a>
</div>
