@extends('layouts.site')

@push('head_scripts')
@if($canAccess && !$lesson->isYoutubeVideo() && $lesson->video_url && $progress)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('lesson-video-player');
    if (!video) return;
    const progressId = {{ $progress->id }};
    const fallbackDuration = {{ (int) ($lesson->video?->duration_seconds ?? 0) }};
    @if($progress->last_position_seconds > 0)
    video.currentTime = {{ $progress->last_position_seconds }};
    @endif
    function getDuration() { return video.duration && !isNaN(video.duration) ? Math.floor(video.duration) : fallbackDuration; }
    let saveTimeout;
    video.addEventListener('timeupdate', function() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(function() {
            if (progressId) {
                var d = getDuration();
                if (d > 0) {
                    fetch('{{ route("site.course.lesson.save-progress", [$course, $lesson]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ position: Math.floor(video.currentTime), duration: d }),
                    });
                }
            }
        }, 5000);
    });
    video.addEventListener('ended', function() {
        if (progressId) {
            var d = getDuration();
            if (d > 0) {
                fetch('{{ route("site.course.lesson.save-progress", [$course, $lesson]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ position: d, duration: d }),
                });
            }
        }
    });
});
</script>
@endif
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10" style="direction: rtl;">
    {{-- Breadcrumb --}}
    <div class="text-xs text-slate-600 mb-6">
        <a href="{{ url('/') }}" class="hover:underline">الرئيسية</a>
        <span class="mx-1">/</span>
        <a href="{{ route('site.courses') }}" class="hover:underline">الدورات</a>
        <span class="mx-1">/</span>
        <a href="{{ route('site.course.show', $course) }}" class="hover:underline">{{ $course->title }}</a>
        <span class="mx-1">/</span>
        <span class="text-slate-900 font-bold">{{ $lesson->title }}</span>
    </div>

    @if(!$canAccess)
        {{-- مقفل --}}
        <div class="rounded-3xl border-2 border-amber-200 bg-amber-50 p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mb-2">{{ $lesson->title }}</h1>
            <p class="text-slate-700 mb-6">{{ $accessMessage }}</p>
            <a href="{{ route('site.course.subscribe', $course) }}" class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                اشترك في الدورة لمشاهدة الدرس
            </a>
        </div>
    @else
        {{-- محتوى الدرس --}}
        <div class="space-y-6">
            <div class="rounded-3xl border bg-white overflow-hidden">
                <h1 class="px-6 py-5 text-2xl font-extrabold text-slate-900 border-b">{{ $lesson->title }}</h1>

                @if($lesson->description)
                    <div class="px-6 py-4 border-b bg-slate-50">
                        <p class="text-slate-700 leading-relaxed">{{ $lesson->description }}</p>
                    </div>
                @endif

                {{-- الفيديو: يوتيوب أو ملف --}}
                @if($lesson->isYoutubeVideo())
                    <div class="aspect-video bg-slate-900">
                        <iframe id="lesson-video-player" class="w-full h-full" src="{{ $lesson->youtube_embed_url }}?autoplay=1" title="{{ $lesson->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                @elseif($lesson->video_url)
                    <div class="aspect-video bg-slate-900 relative" x-data="{ speed: 1, open: false }">
                        <video id="lesson-video-player" class="w-full h-full object-contain" controls autoplay x-ref="video">
                            @if($lesson->video && $lesson->video->hls_path)
                                <source src="{{ $lesson->video->hls_path }}" type="application/x-mpegURL">
                            @else
                                <source src="{{ $lesson->video_url }}" type="video/mp4">
                            @endif
                            متصفحك لا يدعم تشغيل الفيديو.
                        </video>
                        <div class="absolute bottom-14 left-2 right-2 flex justify-end gap-1 opacity-90">
                            <button type="button" @click="open = !open" class="px-3 py-1.5 rounded-lg bg-black/70 text-white text-sm font-bold hover:bg-black/90">
                                سرعة: <span x-text="speed + 'x'"></span>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false" class="absolute bottom-full right-0 mb-1 py-2 rounded-lg bg-black/90 shadow-xl min-w-[100px]">
                                <template x-for="s in [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2]" :key="s">
                                    <button type="button" @click="speed = s; $refs.video.playbackRate = s; open = false" class="block w-full px-4 py-2 text-right text-sm text-white hover:bg-white/20" :class="speed === s ? 'bg-[#3d195c]' : ''" x-text="s + 'x'"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- صورة الدرس --}}
                @if(in_array($lesson->content_type ?? '', ['image', 'mixed']) && $lesson->image_path)
                    <div class="p-6">
                        <img src="{{ asset('storage/' . ltrim($lesson->image_path, '/')) }}" alt="{{ $lesson->title }}" class="w-full rounded-2xl shadow-lg" loading="lazy">
                    </div>
                @endif

                {{-- المحتوى النصي --}}
                @if(in_array($lesson->content_type ?? '', ['text', 'mixed']) && $lesson->content)
                    <div class="px-6 py-5 prose prose-slate max-w-none prose-headings:font-extrabold prose-a:text-[#3d195c]">
                        {!! $lesson->content !!}
                    </div>
                @endif

                {{-- Zoom Meeting --}}
                @if(($lesson->has_zoom_meeting ?? false) && $lesson->zoomMeeting)
                    @php $zm = $lesson->zoomMeeting; @endphp
                    <div class="px-6 py-5 border-t bg-slate-50">
                        <h3 class="text-lg font-extrabold text-slate-900 mb-3">📹 اجتماع Zoom</h3>
                        <div class="space-y-2 text-sm text-slate-700">
                            @if($zm->scheduled_start_time)
                                <p><span class="font-bold">الموعد:</span> {{ $zm->scheduled_start_time->format('Y-m-d H:i') }}</p>
                            @endif
                            @if($zm->duration)
                                <p><span class="font-bold">المدة:</span> {{ $zm->duration }} دقيقة</p>
                            @endif
                            @if($zm->join_url)
                                <a href="{{ $zm->join_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#3d195c] text-white font-bold hover:bg-[#3d195c]/95 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    انضم للاجتماع
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- الملفات المرفقة --}}
                @if($lesson->files->count() > 0)
                    <div class="px-6 py-5 border-t">
                        <h3 class="text-lg font-extrabold text-slate-900 mb-3">الملفات المرفقة</h3>
                        <div class="space-y-2">
                            @foreach($lesson->files as $file)
                                <a href="{{ asset('storage/' . ltrim($file->path, '/')) }}" target="_blank" rel="noopener" class="flex items-center gap-3 p-3 rounded-2xl border bg-white hover:bg-slate-50 hover:border-[#3d195c]/30 transition">
                                    <svg class="w-5 h-5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span class="font-bold text-slate-800">{{ $file->name }}</span>
                                    <span class="text-xs text-slate-500">({{ number_format($file->size / 1024, 1) }} KB)</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- الاختبار --}}
                @if($lesson->quiz)
                    <div class="px-6 py-5 border-t bg-slate-50">
                        <h3 class="text-lg font-extrabold text-slate-900 mb-3">اختبار الدرس</h3>
                        <p class="text-sm text-slate-700 mb-4">{{ $lesson->quiz->title }}</p>
                        @if($isEnrolled)
                            <a href="{{ route('site.course.quiz.show', [$course, $lesson]) }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zm0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                أداء الاختبار
                            </a>
                        @endif
                    </div>
                @endif

                {{-- الواجبات --}}
                @if($lesson->assignments->count() > 0)
                    <div class="px-6 py-5 border-t">
                        <h3 class="text-lg font-extrabold text-slate-900 mb-3">الواجبات</h3>
                        <div class="space-y-3">
                            @foreach($lesson->assignments as $assignment)
                                <div class="p-4 rounded-2xl border bg-white">
                                    <div class="font-bold text-slate-900">{{ $assignment->title }}</div>
                                    @if($assignment->description)
                                        <p class="text-sm text-slate-600 mt-1">{{ Str::limit($assignment->description, 150) }}</p>
                                    @endif
                                    @if($isEnrolled)
                                        <a href="{{ route('site.my-assignments') }}" class="inline-flex items-center gap-2 mt-3 text-sm font-bold text-[#3d195c] hover:underline">
                                            عرض الواجب
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- أسئلة وأجوبة (Q&A) --}}
                @if($canAccess)
                    <div class="px-6 py-5 border-t">
                        <h3 class="text-lg font-extrabold text-slate-900 mb-3">أسئلة وأجوبة</h3>
                        @if($isEnrolled)
                            <form action="{{ route('site.course.lesson.questions.store', [$course, $lesson]) }}" method="post" class="mb-6">
                                @csrf
                                <textarea name="question" rows="3" class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-[#3d195c] focus:ring-2 focus:ring-[#3d195c]/20" placeholder="اطرح سؤالك عن هذا الدرس..." required></textarea>
                                <button type="submit" class="mt-2 px-5 py-2.5 rounded-xl bg-[#3d195c] text-white font-bold hover:bg-[#3d195c]/95 transition">
                                    طرح السؤال
                                </button>
                            </form>
                        @endif
                        <div class="space-y-4">
                            @forelse($lesson->questions ?? [] as $question)
                                <div class="p-4 rounded-2xl border bg-white">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-full bg-[#3d195c]/10 flex items-center justify-center shrink-0 font-bold text-[#3d195c]">
                                            {{ strtoupper(mb_substr($question->user->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 text-sm mb-1">
                                                <span class="font-bold text-slate-900">{{ $question->user->name ?? 'مجهول' }}</span>
                                                <span class="text-slate-500">{{ $question->created_at?->diffForHumans() }}</span>
                                                @if($question->is_answered)
                                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold">تم الرد</span>
                                                @endif
                                            </div>
                                            <p class="text-slate-700">{{ $question->question }}</p>
                                            @if($question->answers->count() > 0)
                                                <div class="mt-3 space-y-2 pl-4 border-r-2 border-[#3d195c]/20">
                                                    @foreach($question->answers as $answer)
                                                        <div>
                                                            <div class="text-xs text-slate-500 mb-0.5">{{ $answer->user->name ?? 'المدرب' }} · {{ $answer->created_at?->diffForHumans() }}</div>
                                                            <p class="text-sm text-slate-700">{{ $answer->answer }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-slate-500 text-sm">لا توجد أسئلة بعد. كن أول من يطرح سؤالاً!</p>
                            @endforelse
                        </div>
                    </div>
                @endif

                {{-- مدة الدرس + حالة الإكمال --}}
                <div class="px-6 py-4 border-t flex flex-wrap items-center justify-between gap-3">
                    @if($lesson->duration_minutes)
                        <span class="text-sm text-slate-600">{{ (int) $lesson->duration_minutes }} دقيقة</span>
                    @endif
                    @if($progress && $progress->completed)
                        <span class="inline-flex items-center gap-1 text-sm font-bold text-emerald-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            مكتمل
                        </span>
                    @endif
                </div>
            </div>

            {{-- تنقل الدروس --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                @if($prevLesson)
                    <a href="{{ route('site.course.lesson.show', [$course, $prevLesson]) }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl border-2 border-slate-200 text-slate-700 font-extrabold hover:bg-slate-50 hover:border-[#3d195c]/30 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        الدرس السابق
                    </a>
                @else
                    <a href="{{ route('site.course.show', $course) }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl border-2 border-slate-200 text-slate-700 font-extrabold hover:bg-slate-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        العودة للدورة
                    </a>
                @endif
                @if($nextLesson)
                    <a href="{{ route('site.course.lesson.show', [$course, $nextLesson]) }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                        الدرس التالي
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <a href="{{ route('site.course.show', $course) }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                        العودة للدورة
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
