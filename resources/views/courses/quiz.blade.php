@extends('layouts.site')

@push('head_scripts')
@if($timeRemaining !== null && $timeRemaining > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('quiz-form');
    const timerEl = document.getElementById('quiz-timer');
    if (!timerEl || !form) return;

    let remaining = {{ $timeRemaining }};
    const duration = {{ $quiz->duration_minutes * 60 }};

    function updateTimer() {
        remaining--;
        if (remaining <= 0) {
            timerEl.textContent = '00:00';
            timerEl.classList.add('text-red-600');
            form.submit();
            return;
        }
        const m = Math.floor(remaining / 60);
        const s = remaining % 60;
        timerEl.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        if (remaining < 300) timerEl.classList.add('text-red-600');
    }
    updateTimer();
    setInterval(updateTimer, 1000);
});
</script>
@endif
@endpush

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10" style="direction: rtl;">
    {{-- Breadcrumb --}}
    <div class="text-xs text-slate-600 mb-6">
        <a href="{{ url('/') }}" class="hover:underline">الرئيسية</a>
        <span class="mx-1">/</span>
        <a href="{{ route('site.courses') }}" class="hover:underline">الدورات</a>
        <span class="mx-1">/</span>
        <a href="{{ route('site.course.show', $course) }}" class="hover:underline">{{ $course->title }}</a>
        <span class="mx-1">/</span>
        <a href="{{ route('site.course.lesson.show', [$course, $lesson]) }}" class="hover:underline">{{ $lesson->title }}</a>
        <span class="mx-1">/</span>
        <span class="text-slate-900 font-bold">اختبار الدرس</span>
    </div>

    @if(session('notice'))
        <div class="mb-6 p-4 rounded-2xl {{ session('notice')['type'] === 'success' ? 'bg-emerald-50 text-emerald-800' : (session('notice')['type'] === 'error' ? 'bg-red-50 text-red-800' : 'bg-amber-50 text-amber-800') }}">
            {{ session('notice')['message'] }}
        </div>
    @endif

    <div class="rounded-3xl border bg-white overflow-hidden">
        <div class="px-6 py-5 border-b bg-slate-50">
            <h1 class="text-2xl font-extrabold text-slate-900">{{ $quiz->title }}</h1>
            @if($quiz->description)
                <p class="mt-2 text-slate-700">{{ $quiz->description }}</p>
            @endif
            <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
                <span class="text-slate-600">نسبة النجاح: <strong>{{ $quiz->pass_percentage }}%</strong></span>
                @if($quiz->duration_minutes)
                    <span class="text-slate-600">المدة: <strong>{{ $quiz->duration_minutes }} دقيقة</strong></span>
                @endif
                @if($quiz->max_attempts)
                    <span class="text-slate-600">المحاولات: <strong>{{ $quiz->max_attempts }}</strong></span>
                @endif
            </div>
        </div>

        @if($timeRemaining !== null)
            <div class="px-6 py-4 border-b {{ $timeRemaining < 300 ? 'bg-red-50' : 'bg-amber-50' }}">
                <div class="flex items-center justify-between">
                    <span class="font-bold {{ $timeRemaining < 300 ? 'text-red-700' : 'text-amber-700' }}">الوقت المتبقي:</span>
                    <span id="quiz-timer" class="text-xl font-extrabold {{ $timeRemaining < 300 ? 'text-red-600' : 'text-amber-600' }}">
                        {{ gmdate('i:s', $timeRemaining) }}
                    </span>
                </div>
            </div>
        @endif

        <form id="quiz-form" method="POST" action="{{ route('site.course.quiz.submit', [$course, $lesson]) }}" class="p-6">
            @csrf

            <div class="space-y-8">
                @foreach($quiz->questions as $i => $question)
                    <div class="rounded-2xl border p-5 bg-white">
                        <div class="font-bold text-slate-900 mb-3">
                            {{ $i + 1 }}. {{ $question->question_text }}
                        </div>

                        @switch($question->type)
                            @case('mcq')
                                <div class="space-y-2">
                                    @foreach($question->options ?? [] as $key => $label)
                                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer hover:bg-slate-50 hover:border-[#3d195c]/30 transition">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $key }}" class="w-4 h-4 text-[#3d195c]" {{ ($answers[$question->id] ?? '') == $key ? 'checked' : '' }}>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @break

                            @case('true_false')
                                <div class="space-y-2">
                                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer hover:bg-slate-50 hover:border-[#3d195c]/30 transition">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="true" class="w-4 h-4 text-[#3d195c]" {{ ($answers[$question->id] ?? '') == 'true' ? 'checked' : '' }}>
                                        <span>صح</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer hover:bg-slate-50 hover:border-[#3d195c]/30 transition">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="false" class="w-4 h-4 text-[#3d195c]" {{ ($answers[$question->id] ?? '') == 'false' ? 'checked' : '' }}>
                                        <span>خطأ</span>
                                    </label>
                                </div>
                                @break

                            @case('fill_blank')
                            @case('short_answer')
                                <textarea name="answers[{{ $question->id }}]" rows="3" class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-[#3d195c] focus:ring-2 focus:ring-[#3d195c]/20" placeholder="اكتب إجابتك...">{{ $answers[$question->id] ?? '' }}</textarea>
                                @break

                            @case('matching')
                                <textarea name="answers[{{ $question->id }}]" rows="4" class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-[#3d195c] focus:ring-2 focus:ring-[#3d195c]/20" placeholder='أدخل الإجابات بصيغة JSON: {"key1": "value1", "key2": "value2"}'>{{ $answers[$question->id] ?? '' }}</textarea>
                                <p class="mt-2 text-xs text-slate-500">أدخل الإجابات بصيغة JSON</p>
                                @break

                            @default
                                <input type="text" name="answers[{{ $question->id }}]" value="{{ $answers[$question->id] ?? '' }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-[#3d195c] focus:ring-2 focus:ring-[#3d195c]/20">
                        @endswitch
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex flex-wrap gap-4">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    إرسال الإجابات
                </button>
                <a href="{{ route('site.course.lesson.show', [$course, $lesson]) }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl border-2 border-slate-200 text-slate-700 font-extrabold hover:bg-slate-50 transition">
                    إلغاء والعودة للدرس
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
