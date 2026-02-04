@props(['product', 'inWishlist' => false])
@php
    $product = $product ?? null;
    if (!$product) return;
@endphp
<div class="absolute top-2 left-2 z-10" {{ $attributes }}>
    @if($inWishlist)
        <form method="POST" action="{{ route('site.wishlist.products.remove', $product) }}" class="inline-block" onclick="event.stopPropagation();">
            @csrf
            <button type="submit" class="w-9 h-9 rounded-full bg-white/95 shadow-md flex items-center justify-center text-rose-500 hover:bg-rose-50 hover:scale-110 transition" aria-label="إزالة من قائمة الرغبات">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
        </form>
    @else
        <form method="POST" action="{{ route('site.wishlist.products.add', $product) }}" class="inline-block" onclick="event.stopPropagation();">
            @csrf
            <button type="submit" class="w-9 h-9 rounded-full bg-white/95 shadow-md flex items-center justify-center text-slate-500 hover:text-rose-500 hover:bg-rose-50 hover:scale-110 transition" aria-label="إضافة إلى قائمة الرغبات">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
        </form>
    @endif
</div>
