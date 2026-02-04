@props(['course', 'inWishlist' => false, 'staggerIndex' => 0])
@php
    $course = $course ?? null;
    if (!$course) return;
    $price = (float) ($course->offer_price ?? $course->price ?? 0);
@endphp
<div
    {{ $attributes->merge(['class' => 'reveal-item group rounded-2xl border bg-white overflow-hidden hover:shadow-xl hover:scale-[1.02] transition-all duration-300']) }}
    data-reveal
    data-stagger="{{ $staggerIndex }}"
>
    <div class="relative aspect-[16/9] bg-slate-100 overflow-hidden">
        <x-wishlist-heart :course="$course" :in-wishlist="$inWishlist" />
        <a href="{{ route('site.course.show', $course) }}" class="block w-full h-full">
            @if($course->cover_image)
                <img
                    src="{{ $course->cover_image }}"
                    alt="{{ $course->title }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                />
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-200 to-slate-100">
                    <svg class="w-16 h-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            @endif
        </a>
    </div>
    <a href="{{ route('site.course.show', $course) }}" class="block p-4">
        <div class="text-sm font-extrabold line-clamp-2 text-slate-900 group-hover:text-[#3d195c] transition-colors">{{ $course->title }}</div>
        <div class="text-xs text-slate-600 mt-1">{{ $course->instructor?->name }} • {{ $course->category?->name ?? '—' }}</div>
        <div class="mt-3 flex items-center justify-between">
            <div class="text-xs text-slate-500 flex items-center gap-1">
                <span>⭐</span>
                <span>{{ number_format((float) ($course->rating ?? 0), 1) }}</span>
            </div>
            <div class="text-sm font-extrabold text-slate-900">
                {{ $price > 0 ? number_format($price, 2) . ' ج.م' : 'مجاني' }}
            </div>
        </div>
    </a>
</div>
