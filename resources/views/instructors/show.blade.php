@extends('layouts.site')

@section('title', $instructor->name . ' - الملف الشخصي للمدرب')

@push('head')
<meta name="description" content="{{ Str::limit(strip_tags($instructor->academic_history ?? $instructor->job ?? ''), 160) }}">
<meta property="og:title" content="{{ $instructor->name }}">
<meta property="og:description" content="مدرب فيPegasus Academy - {{ $instructor->coursesCount }} دورة">
<meta property="og:image" content="{{ $instructor->avatar_url ?? asset('images/og-default.png') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="profile">
@endpush

@section('content')
@php
    $cartIds = session('cart', []);
    $cartIds = is_array($cartIds) ? array_map('intval', $cartIds) : [];
    $wishlistIds = session('course_wishlist', []);
    $wishlistIds = is_array($wishlistIds) ? array_map('intval', $wishlistIds) : [];
@endphp

{{-- Hero Section --}}
<section class="relative overflow-hidden bg-gradient-to-br from-[#2c004d] via-[#3d195c] to-[#1a0033] py-16 md:py-24">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.15) 0%, transparent 50%);"></div>
    <div class="relative max-w-7xl mx-auto px-4" style="direction: rtl;">
        <div class="flex flex-col md:flex-row md:items-center gap-8">
            {{-- Avatar --}}
            <div class="flex-shrink-0">
                <div class="w-32 h-32 md:w-40 md:h-40 rounded-3xl overflow-hidden ring-4 ring-white/20 shadow-2xl">
                    @if($instructor->avatar_url)
                        <img src="{{ $instructor->avatar_url }}" alt="{{ $instructor->name }}" class="w-full h-full object-cover" />
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-white/30 to-white/10 flex items-center justify-center">
                            <span class="text-5xl md:text-6xl font-extrabold text-white">{{ mb_substr($instructor->name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>
            </div>
            {{-- Info --}}
            <div class="flex-1">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/15 text-white/90 text-xs font-bold mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    مدرب معتمد
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white">{{ $instructor->name }}</h1>
                @if($instructor->job)
                    <p class="mt-2 text-lg text-white/80">{{ $instructor->job }}</p>
                @endif
                @if($instructor->city)
                    <p class="mt-1 text-sm text-white/60 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $instructor->city }}
                    </p>
                @endif
                <div class="mt-6 flex flex-wrap gap-4">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/15 text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span class="font-bold">{{ $coursesCount }}</span>
                        <span class="text-white/80">دورة</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/15 text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span class="font-bold">{{ number_format($totalStudents) }}</span>
                        <span class="text-white/80">طالب</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Content --}}
<section class="max-w-7xl mx-auto px-4 py-10" style="direction: rtl;">
    <div class="grid lg:grid-cols-12 gap-8">
        {{-- Sidebar: Academic History --}}
        @if($instructor->academic_history)
        <aside class="lg:col-span-4 order-2 lg:order-1">
            <div class="rounded-3xl border bg-white overflow-hidden shadow-sm sticky top-4">
                <div class="px-6 py-4 bg-slate-50 border-b">
                    <h2 class="font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        التاريخ العلمي
                    </h2>
                </div>
                <div class="p-6">
                    <div class="prose prose-slate max-w-none text-slate-700 whitespace-pre-line">{{ $instructor->academic_history }}</div>
                </div>
            </div>
        </aside>
        @endif

        {{-- Main: Courses --}}
        <div class="{{ $instructor->academic_history ? 'lg:col-span-8' : 'lg:col-span-12' }} order-1 lg:order-2">
            <div class="mb-6">
                <h2 class="text-2xl font-extrabold text-slate-900">دورات المدرب</h2>
                <p class="text-slate-600 mt-1">{{ $coursesCount }} دورة متاحة للتعلم</p>
            </div>

            @if($courses->isEmpty())
                <div class="rounded-3xl border bg-white p-12 text-center text-slate-500">
                    <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    لا توجد دورات متاحة حالياً.
                </div>
            @else
                <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($courses as $index => $course)
                        <x-site.course-card
                            :course="$course"
                            :in-wishlist="in_array((int) $course->id, $wishlistIds, true)"
                            :stagger-index="$index"
                        />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
