@extends('layouts.site')

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
        <span class="text-slate-900 font-bold">نتيجة الاختبار</span>
    </div>

    @if(session('notice'))
        <div class="mb-6 p-4 rounded-2xl {{ session('notice')['type'] === 'success' ? 'bg-emerald-50 text-emerald-800' : (session('notice')['type'] === 'error' ? 'bg-red-50 text-red-800' : 'bg-amber-50 text-amber-800') }}">
            {{ session('notice')['message'] }}
        </div>
    @endif

    <div class="rounded-3xl border bg-white overflow-hidden">
        <div class="px-6 py-5 border-b bg-slate-50">
            <h1 class="text-2xl font-extrabold text-slate-900">{{ $quiz->title }}</h1>
        </div>

        @if($maxReached)
            <div class="p-6">
                <div class="rounded-2xl bg-amber-50 border border-amber-200 p-6 text-center">
                    <p class="text-amber-800 font-bold">لقد استنفدت عدد المحاولات المسموح به ({{ $quiz->max_attempts }})</p>
                    <p class="mt-2 text-sm text-amber-700">آخر نتيجة أدناه</p>
                </div>
            </div>
        @endif

        @if($attempt)
            <div class="p-6">
                <div class="rounded-2xl p-6 mb-6 {{ $attempt->passed ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200' }}">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full {{ $attempt->passed ? 'bg-emerald-100' : 'bg-amber-100' }} flex items-center justify-center">
                            @if($attempt->passed)
                                <svg class="w-8 h-8 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="w-8 h-8 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-xl font-extrabold {{ $attempt->passed ? 'text-emerald-900' : 'text-amber-900' }}">
                                {{ $attempt->passed ? 'تهانينا! نجحت في الاختبار' : 'لم تنجح في الاختبار' }}
                            </h2>
                            <p class="text-slate-700 mt-1">
                                نقاطك: <strong>{{ number_format((float) $attempt->score, 1) }}%</strong>
                                (المطلوب: {{ $quiz->pass_percentage }}%)
                            </p>
                            <p class="text-sm text-slate-600 mt-1">المحاولة رقم {{ $attempt->attempt_number }}</p>
                        </div>
                    </div>
                </div>

                <h3 class="text-lg font-extrabold text-slate-900 mb-4">تفاصيل الإجابات</h3>
                <div class="space-y-4">
                    @foreach($quiz->questions as $i => $question)
                        @php
                            $userAnswer = $attempt->answers[$question->id] ?? null;
                            $correctAnswer = $question->correct_answer;
                            $isCorrect = false;

                            switch($question->type) {
                                case 'mcq':
                                case 'true_false':
                                    $isCorrect = is_array($correctAnswer) && in_array($userAnswer, $correctAnswer);
                                    break;
                                case 'fill_blank':
                                case 'short_answer':
                                    if (is_array($correctAnswer)) {
                                        $userAnswerLower = strtolower(trim((string) ($userAnswer ?? '')));
                                        foreach ($correctAnswer as $correct) {
                                            if (strtolower(trim((string) $correct)) === $userAnswerLower) {
                                                $isCorrect = true;
                                                break;
                                            }
                                        }
                                    }
                                    break;
                                case 'matching':
                                    if (is_array($correctAnswer) && is_string($userAnswer)) {
                                        $userArr = json_decode($userAnswer, true);
                                        if (is_array($userArr)) {
                                            $matches = 0;
                                            foreach ($correctAnswer as $k => $v) {
                                                if (isset($userArr[$k]) && (string) $userArr[$k] === (string) $v) $matches++;
                                            }
                                            $isCorrect = $matches === count($correctAnswer);
                                        }
                                    }
                                    break;
                            }
                        @endphp
                        <div class="rounded-2xl border p-4 {{ $isCorrect ? 'border-emerald-200 bg-emerald-50/50' : 'border-amber-200 bg-amber-50/50' }}">
                            <div class="font-bold text-slate-900">{{ $i + 1 }}. {{ $question->question_text }}</div>
                            <div class="mt-3 space-y-2 text-sm">
                                <p>
                                    <strong>إجابتك:</strong>
                                    <span class="{{ $isCorrect ? 'text-emerald-700' : 'text-amber-700' }}">
                                        {{ is_array($userAnswer) ? json_encode($userAnswer) : ($userAnswer ?? 'لم تجب') }}
                                    </span>
                                </p>
                                @if(!$isCorrect && $correctAnswer !== null)
                                    <p class="text-slate-600">
                                        <strong>الإجابة الصحيحة:</strong>
                                        {{ is_array($correctAnswer) ? implode(' أو ', $correctAnswer) : $correctAnswer }}
                                    </p>
                                @endif
                                @if($question->explanation)
                                    <div class="mt-2 p-3 rounded-xl bg-slate-100">
                                        <strong>التفسير:</strong> {{ $question->explanation }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="px-6 py-5 border-t bg-slate-50 flex flex-wrap gap-4">
            <a href="{{ route('site.course.lesson.show', [$course, $lesson]) }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                العودة للدرس
            </a>
            @if($quiz->allow_retake && !$maxReached)
                @php
                    $attemptsCount = \App\Models\QuizAttempt::where('user_id', auth()->id())->where('quiz_id', $quiz->id)->count();
                    $canRetake = !$quiz->max_attempts || $attemptsCount < $quiz->max_attempts;
                @endphp
                @if($canRetake)
                    <form method="POST" action="{{ route('site.course.quiz.retake', [$course, $lesson]) }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl border-2 border-[#3d195c] text-[#3d195c] font-extrabold hover:bg-[#3d195c]/5 transition">
                            إعادة المحاولة
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
