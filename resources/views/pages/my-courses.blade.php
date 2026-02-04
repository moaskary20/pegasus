@extends('layouts.site')

@section('content')
@php
    $brand = '#2c004d';
@endphp
<style>
    .my-courses-hero {
        background: linear-gradient(135deg, {{ $brand }} 0%, #4a1a6e 50%, #2c004d 100%);
        position: relative;
        overflow: hidden;
    }
    .my-courses-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -20%;
        width: 60%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
        pointer-events: none;
    }
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -8px rgba(44, 0, 77, 0.15);
    }
    .course-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px -8px rgba(44, 0, 77, 0.2);
    }
    .progress-ring {
        transform: rotate(-90deg); /* Start from top */
    }
    .progress-ring-bg { stroke: rgba(255,255,255,0.2); }
    .progress-ring-fill { stroke-linecap: round; transition: stroke-dashoffset 0.6s ease; }
</style>

<section class="my-courses-hero text-white" style="direction: rtl;">
    <div class="max-w-7xl mx-auto px-4 py-12 md:py-16 relative">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-sm font-bold mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    </svg>
                    تعلّمي / دوراتي
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold leading-tight">
                    الدورات التي تم الاشتراك فيها
                </h1>
                <p class="mt-3 text-white/90 text-sm md:text-base max-w-xl">
                    تابع تقدمك، أكمل الدروس، واحصل على شهاداتك من مكان واحد.
                </p>
            </div>
            {{-- Overall progress ring (desktop) --}}
            @if($totalCourses > 0)
            <div class="hidden md:flex flex-col items-center shrink-0">
                <div class="relative w-28 h-28">
                    <svg class="progress-ring w-full h-full" viewBox="0 0 36 36">
                        <circle class="progress-ring-bg" cx="18" cy="18" r="15.9" fill="none" stroke-width="3"/>
                        <circle
                            class="progress-ring-fill"
                            cx="18" cy="18" r="15.9"
                            fill="none"
                            stroke="white"
                            stroke-width="3"
                            stroke-dasharray="{{ 100 * 2 * 3.14159 * 15.9 / 100 }} {{ 2 * 3.14159 * 15.9 }}"
                            stroke-dashoffset="{{ 2 * 3.14159 * 15.9 * (1 - $avgProgress / 100) }}"
                        />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-extrabold">{{ number_format($avgProgress, 0) }}%</span>
                    </div>
                </div>
                <span class="text-xs font-bold text-white/80 mt-2">متوسط التقدم</span>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- Stats cards --}}
<section class="max-w-7xl mx-auto px-4 -mt-6 relative z-10" style="direction: rtl;">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card rounded-2xl border bg-white p-5 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-[#2c004d]/10 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-slate-900">{{ number_format($totalCourses) }}</p>
                    <p class="text-xs font-bold text-slate-500">إجمالي الدورات</p>
                </div>
            </div>
        </div>
        <div class="stat-card rounded-2xl border bg-white p-5 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-amber-500/15 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-slate-900">{{ number_format($inProgressCount) }}</p>
                    <p class="text-xs font-bold text-slate-500">قيد التقدم</p>
                </div>
            </div>
        </div>
        <div class="stat-card rounded-2xl border bg-white p-5 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/15 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-slate-900">{{ number_format($completedCount) }}</p>
                    <p class="text-xs font-bold text-slate-500">مكتملة</p>
                </div>
            </div>
        </div>
        <div class="stat-card rounded-2xl border bg-white p-5 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-violet-500/15 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-slate-900">{{ number_format($totalHours, 1) }}</p>
                    <p class="text-xs font-bold text-slate-500">ساعات إجمالية</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Course list --}}
<section class="max-w-7xl mx-auto px-4 py-10" style="direction: rtl;">
    <div class="flex items-center justify-between gap-4 mb-6">
        <h2 class="text-xl font-extrabold text-slate-900">دوراتي</h2>
        @if($totalCourses > 0)
        <a href="{{ route('site.courses') }}" class="text-sm font-bold text-[#2c004d] hover:underline">
            استكشف دورات جديدة ←
        </a>
        @endif
    </div>

    @if($enrollments->isEmpty())
        <div class="rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50 p-12 text-center">
            <div class="w-20 h-20 mx-auto rounded-full bg-slate-200 flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h3 class="text-lg font-extrabold text-slate-800">لا توجد دورات مسجلة بعد</h3>
            <p class="mt-2 text-sm text-slate-600 max-w-md mx-auto">
                بمجرد الاشتراك في دورة تدريبية، ستظهر هنا مع مؤشر التقدم وروابط متابعة التعلم.
            </p>
            <a href="{{ route('site.courses') }}" class="inline-flex items-center justify-center mt-6 px-6 py-3 rounded-2xl bg-[#2c004d] text-white font-bold hover:bg-[#2c004d]/90 transition">
                تصفّح الدورات
            </a>
        </div>
    @else
        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($enrollments as $enrollment)
                @php
                    $course = $enrollment->course;
                    if (!$course) continue;
                    $progress = (float) ($enrollment->progress_percentage ?? 0);
                    $isCompleted = $enrollment->completed_at !== null;
                    $coverUrl = $course->cover_image ? asset('storage/' . ltrim($course->cover_image, '/')) : null;
                @endphp
                <article class="course-card rounded-2xl border bg-white overflow-hidden shadow-sm">
                    <a href="{{ route('site.course.show', $course) }}" class="block">
                        <div class="aspect-video bg-slate-200 relative overflow-hidden">
                            @if($coverUrl)
                                <img src="{{ $coverUrl }}" alt="{{ $course->title }}" class="w-full h-full object-cover" />
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#2c004d]/20 to-[#2c004d]/5">
                                    <svg class="w-16 h-16 text-[#2c004d]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            @endif
                            @if($isCompleted)
                                <div class="absolute top-3 left-3 right-3 flex justify-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-500 text-white text-xs font-bold">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        مكتملة
                                    </span>
                                </div>
                            @endif
                            <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-slate-200">
                                <div
                                    class="h-full rounded-b {{ $isCompleted ? 'bg-emerald-500' : 'bg-[#2c004d]' }}"
                                    style="width: {{ min(100, $progress) }}%"
                                ></div>
                            </div>
                        </div>
                    </a>
                    <div class="p-4">
                        <a href="{{ route('site.course.show', $course) }}" class="block">
                            <h3 class="font-extrabold text-slate-900 line-clamp-2 hover:text-[#2c004d] transition">{{ $course->title }}</h3>
                        </a>
                        @if($course->instructor)
                            <p class="text-xs text-slate-500 mt-1">{{ $course->instructor->name }}</p>
                        @endif
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-bold text-slate-600">التقدم</span>
                                    <span class="font-extrabold text-slate-900">{{ number_format($progress, 0) }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div
                                        class="h-full rounded-full {{ $isCompleted ? 'bg-emerald-500' : 'bg-[#2c004d]' }} transition-all duration-500"
                                        style="width: {{ min(100, $progress) }}%"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <a
                            href="{{ route('site.course.show', $course) }}"
                            class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold {{ $isCompleted ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-[#2c004d] text-white hover:bg-[#2c004d]/90' }} transition"
                        >
                            @if($isCompleted)
                                مراجعة الدورة
                            @else
                                متابعة التعلم
                            @endif
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>
@endsection
