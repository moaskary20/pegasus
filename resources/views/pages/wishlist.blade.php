@extends('layouts.site')

@section('content')
@php
    $coursesCount = $courseWishlist->count();
@endphp
<section class="max-w-7xl mx-auto px-4 py-10" style="direction: rtl;">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/10 text-rose-600 text-sm font-bold mb-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                قائمة الرغبات
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">قائمة الرغبات</h1>
            <p class="text-sm text-slate-600 mt-1">الدورات والمنتجات التي حفظتها للمراجعة لاحقاً.</p>
        </div>
        @if($coursesCount > 0)
        <a href="{{ route('site.courses') }}" class="text-sm font-bold text-[#2c004d] hover:underline">
            استكشف دورات جديدة ←
        </a>
        @endif
    </div>

    @if(session('notice'))
        <div class="mb-6 rounded-2xl border px-4 py-3 text-sm font-semibold {{ session('notice.type') === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
            {{ session('notice.message') }}
        </div>
    @endif

    {{-- Courses wishlist --}}
    <div class="rounded-3xl border bg-white overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b bg-slate-50 flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-slate-900">دورات قائمة الرغبات</h2>
            <span class="text-sm font-bold text-slate-500">{{ $coursesCount }} دورة</span>
        </div>

        @if($coursesCount === 0 && $storeWishlist->isEmpty())
            <div class="p-12 text-center">
                <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-extrabold text-slate-800">قائمة الرغبات فارغة</h3>
                <p class="mt-2 text-sm text-slate-600 max-w-md mx-auto">
                    عند الضغط على أيقونة القلب في أي دورة تدريبية، ستُضاف هنا.
                </p>
                <a href="{{ route('site.courses') }}" class="inline-flex items-center justify-center mt-6 px-6 py-3 rounded-2xl bg-[#2c004d] text-white font-bold hover:bg-[#2c004d]/90 transition">
                    تصفّح الدورات
                </a>
            </div>
        @elseif($coursesCount > 0)
            <div class="divide-y divide-slate-100">
                @foreach($courseWishlist as $course)
                    @php
                        $coverUrl = $course->cover_image ? asset('storage/' . ltrim($course->cover_image, '/')) : null;
                        $price = (float) ($course->offer_price ?? $course->price ?? 0);
                    @endphp
                    <div class="flex flex-col sm:flex-row gap-4 p-5 hover:bg-slate-50/50 transition">
                        <a href="{{ route('site.course.show', $course) }}" class="sm:w-48 shrink-0 rounded-2xl overflow-hidden bg-slate-100 aspect-video block">
                            @if($coverUrl)
                                <img src="{{ $coverUrl }}" alt="{{ $course->title }}" class="w-full h-full object-cover" loading="lazy" />
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#2c004d]/10 to-slate-200">
                                    <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            @endif
                        </a>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('site.course.show', $course) }}" class="block">
                                <h3 class="font-extrabold text-slate-900 line-clamp-2 hover:text-[#2c004d] transition">{{ $course->title }}</h3>
                            </a>
                            <p class="text-sm text-slate-500 mt-1">{{ $course->instructor?->name }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <span class="text-xs text-slate-500">⭐ {{ number_format((float) ($course->rating ?? 0), 1) }}</span>
                                <span class="text-sm font-extrabold text-slate-900">{{ $price > 0 ? number_format($price, 2) . ' ج.م' : 'مجاني' }}</span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('site.course.show', $course) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#2c004d] text-white text-sm font-bold hover:bg-[#2c004d]/90 transition">
                                    عرض الدورة
                                </a>
                                <form method="POST" action="{{ route('site.wishlist.courses.remove', $course) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold hover:bg-rose-50 hover:text-rose-600 transition">
                                        إزالة من القائمة
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center">
                <p class="text-sm text-slate-600">لا توجد دورات في قائمة الرغبات.</p>
                <a href="{{ route('site.courses') }}" class="inline-flex items-center justify-center mt-3 px-4 py-2 rounded-xl bg-[#2c004d] text-white text-sm font-bold hover:bg-[#2c004d]/90 transition">تصفّح الدورات</a>
            </div>
        @endif
    </div>

    @if($storeWishlist->isNotEmpty())
        <div class="mt-10 rounded-3xl border bg-white overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b bg-slate-50">
                <h2 class="text-lg font-extrabold text-slate-900">منتجات قائمة الرغبات</h2>
            </div>
            <div class="p-5">
                <p class="text-sm text-slate-600">منتجات المتجر المحفوظة تظهر هنا عند إضافتها.</p>
                <div class="mt-4 space-y-3">
                    @foreach($storeWishlist->take(10) as $w)
                        <div class="flex items-center justify-between gap-3 py-3 border-b border-slate-100 last:border-0">
                            <span class="font-semibold text-slate-900">{{ $w->product?->name ?? 'منتج' }}</span>
                            <a href="{{ route('site.store') }}" class="text-sm font-bold text-[#2c004d] hover:underline">عرض المتجر</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</section>
@endsection
