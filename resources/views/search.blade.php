@extends('layouts.site')

@section('content')
    @php $q = request('q', ''); @endphp

    <section class="max-w-7xl mx-auto px-4 py-8">
        <div class="rounded-3xl border bg-white p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900">نتائج البحث</h1>
                    <p class="text-sm text-slate-600 mt-1">اكتب كلمة البحث وستظهر النتائج فوراً</p>
                </div>
                <div class="text-sm text-slate-600">
                    <span class="font-bold">الكلمة:</span>
                    <span class="font-mono">{{ $q }}</span>
                </div>
            </div>

            <div
                class="mt-6"
                x-data="{
                    q: @js($q),
                    loading: false,
                    results: { courses: [], lessons: [], instructors: [], questions: [] },
                    total: 0,
                    active: 'courses',
                    async run() {
                        if (!this.q || this.q.trim().length < 2) return;
                        this.loading = true;
                        try {
                            const res = await fetch(`/api/search/results?q=${encodeURIComponent(this.q)}`, { headers: { 'Accept': 'application/json' } });
                            if (!res.ok) return;
                            const data = await res.json();
                            this.results = data.results || this.results;
                            this.total = data.total || 0;
                        } finally {
                            this.loading = false;
                        }
                    },
                    count(key) { return (this.results[key] || []).length; }
                }"
                x-init="run()"
            >
                <div class="flex items-center justify-between gap-3">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="active='courses'" :class="active==='courses' ? 'bg-[#2c004d] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 rounded-xl text-sm font-bold transition">
                            الدورات <span class="opacity-80" x-text="'(' + count('courses') + ')'"></span>
                        </button>
                        <button type="button" @click="active='lessons'" :class="active==='lessons' ? 'bg-[#2c004d] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 rounded-xl text-sm font-bold transition">
                            الدروس <span class="opacity-80" x-text="'(' + count('lessons') + ')'"></span>
                        </button>
                        <button type="button" @click="active='instructors'" :class="active==='instructors' ? 'bg-[#2c004d] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 rounded-xl text-sm font-bold transition">
                            المدرسون <span class="opacity-80" x-text="'(' + count('instructors') + ')'"></span>
                        </button>
                        <button type="button" @click="active='questions'" :class="active==='questions' ? 'bg-[#2c004d] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 rounded-xl text-sm font-bold transition">
                            الأسئلة <span class="opacity-80" x-text="'(' + count('questions') + ')'"></span>
                        </button>
                    </div>

                    <div class="text-sm text-slate-600">
                        الإجمالي: <span class="font-extrabold text-slate-900" x-text="total"></span>
                    </div>
                </div>

                <div class="mt-6">
                    <template x-if="loading">
                        <div class="py-10 text-center text-sm text-slate-500">جاري البحث…</div>
                    </template>

                    <template x-if="!loading && total === 0 && q && q.trim().length >= 2">
                        <div class="py-10 text-center text-sm text-slate-500">لا توجد نتائج.</div>
                    </template>

                    {{-- Courses --}}
                    <div x-show="active==='courses'" x-cloak class="space-y-3">
                        <template x-for="c in results.courses" :key="c.id">
                            <div class="rounded-2xl border p-4 hover:shadow-sm transition">
                                <div class="font-extrabold text-slate-900" x-text="c.title"></div>
                                <div class="text-xs text-slate-600 mt-1">
                                    <span x-text="c.instructor || ''"></span>
                                    <span x-show="c.category" class="opacity-60"> • </span>
                                    <span x-text="c.category || ''"></span>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <div class="text-xs text-slate-500">⭐ <span x-text="c.rating || 0"></span></div>
                                    <div class="text-sm font-extrabold">
                                        <span x-text="(c.price && c.price > 0) ? (Number(c.price).toFixed(2) + ' ج.م') : 'مجاني'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Lessons --}}
                    <div x-show="active==='lessons'" x-cloak class="space-y-3">
                        <template x-for="l in results.lessons" :key="l.id">
                            <div class="rounded-2xl border p-4 hover:shadow-sm transition">
                                <div class="font-extrabold text-slate-900" x-text="l.title"></div>
                                <div class="text-xs text-slate-600 mt-1">
                                    <span x-text="l.course_title || ''"></span>
                                    <span x-show="l.instructor" class="opacity-60"> • </span>
                                    <span x-text="l.instructor || ''"></span>
                                </div>
                                <div class="text-xs text-slate-500 mt-2" x-text="l.description || ''"></div>
                            </div>
                        </template>
                    </div>

                    {{-- Instructors --}}
                    <div x-show="active==='instructors'" x-cloak class="grid md:grid-cols-2 gap-3">
                        <template x-for="i in results.instructors" :key="i.id">
                            <div class="rounded-2xl border p-4 flex items-center gap-3 hover:shadow-sm transition">
                                <div class="w-12 h-12 rounded-full bg-slate-100 overflow-hidden shrink-0">
                                    <template x-if="i.avatar">
                                        <img :src="i.avatar" :alt="i.name" class="w-full h-full object-cover" />
                                    </template>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-extrabold text-slate-900 truncate" x-text="i.name"></div>
                                    <div class="text-xs text-slate-600 mt-1">
                                        <span x-text="i.job || ''"></span>
                                        <span x-show="i.city" class="opacity-60"> • </span>
                                        <span x-text="i.city || ''"></span>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1">
                                        الدورات: <span class="font-bold" x-text="i.courses_count || 0"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Questions --}}
                    <div x-show="active==='questions'" x-cloak class="space-y-3">
                        <template x-for="qq in results.questions" :key="qq.id">
                            <div class="rounded-2xl border p-4 hover:shadow-sm transition">
                                <div class="font-extrabold text-slate-900" x-text="qq.course_title"></div>
                                <div class="text-xs text-slate-600 mt-1">السؤال:</div>
                                <div class="text-sm text-slate-800 mt-1" x-text="qq.question"></div>
                                <div class="text-xs text-slate-500 mt-2">
                                    بواسطة <span class="font-bold" x-text="qq.user_name"></span>
                                    • إجابات: <span class="font-bold" x-text="qq.answers_count"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

