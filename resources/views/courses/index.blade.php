@extends('layouts.site')

@section('content')
    @php
        $selectedCategory = (int) request()->query('category', 0);
        $selectedSub = (int) request()->query('sub', 0);
        $selectedInstructor = (int) request()->query('instructor', 0);
        $selectedRating = (string) request()->query('rating', '');
        $priceType = (string) request()->query('price_type', '');
        $minPrice = request()->query('min_price', '');
        $maxPrice = request()->query('max_price', '');
        $sort = (string) request()->query('sort', 'newest');

        $flatSubs = collect($categoriesTree)
            ->flatMap(fn ($c) => collect($c->children)->map(fn ($ch) => ['id' => $ch->id, 'name' => $ch->name, 'parent_id' => $c->id]))
            ->values();
    @endphp

    <section class="bg-slate-50 border-b">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <div class="text-xs font-extrabold tracking-widest text-slate-500 uppercase">COURSES</div>
                    <h1 class="mt-2 text-2xl md:text-3xl font-extrabold text-slate-900">الدورات</h1>
                    <p class="text-sm text-slate-600 mt-2">تصفّح كل الدورات مع فلاتر احترافية للوصول السريع لما يناسبك.</p>
                </div>

                <div class="flex items-center gap-2">
                    <div class="text-xs text-slate-500">النتائج:</div>
                    <div class="text-sm font-extrabold text-slate-900">{{ number_format((int) $courses->total()) }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 py-10" style="direction: rtl;">
        <div class="grid lg:grid-cols-12 gap-6 items-start">
            {{-- Right: Filters --}}
            <aside class="lg:col-span-4 xl:col-span-3 lg:col-start-10">
                <form action="{{ route('site.courses') }}" method="get" class="rounded-3xl border bg-white overflow-hidden shadow-sm">
                    <div class="px-5 py-4 border-b bg-slate-50 flex items-center justify-between">
                        <div class="text-sm font-extrabold text-slate-900">فلترة الدورات</div>
                        <a href="{{ route('site.courses') }}" class="text-xs font-bold text-[#3d195c] hover:underline">مسح الفلاتر</a>
                    </div>

                    <div class="p-5 space-y-5">
                        {{-- Sort --}}
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 mb-2">الترتيب</label>
                            <select name="sort" class="w-full rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c] text-sm">
                                <option value="newest" @selected($sort === 'newest')>الأحدث</option>
                                <option value="top" @selected($sort === 'top')>الأعلى تقييماً</option>
                                <option value="price_asc" @selected($sort === 'price_asc')>السعر: من الأقل</option>
                                <option value="price_desc" @selected($sort === 'price_desc')>السعر: من الأعلى</option>
                            </select>
                        </div>

                        {{-- Price --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-extrabold text-slate-700">السعر</label>
                                <div class="text-[11px] text-slate-500">ج.م</div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="min_price"
                                    value="{{ $minPrice }}"
                                    placeholder="من"
                                    class="w-full rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c] text-sm"
                                />
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="max_price"
                                    value="{{ $maxPrice }}"
                                    placeholder="إلى"
                                    class="w-full rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c] text-sm"
                                />
                            </div>

                            <div class="mt-3 grid grid-cols-3 gap-2 text-xs font-bold">
                                <label class="cursor-pointer">
                                    <input type="radio" name="price_type" value="" class="hidden" @checked($priceType === '')>
                                    <span class="block text-center rounded-2xl border px-3 py-2 hover:bg-slate-50 transition {{ $priceType === '' ? 'border-[#3d195c] bg-[#3d195c]/5 text-[#3d195c]' : 'border-slate-200 text-slate-700' }}">
                                        الكل
                                    </span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="price_type" value="free" class="hidden" @checked($priceType === 'free')>
                                    <span class="block text-center rounded-2xl border px-3 py-2 hover:bg-slate-50 transition {{ $priceType === 'free' ? 'border-[#3d195c] bg-[#3d195c]/5 text-[#3d195c]' : 'border-slate-200 text-slate-700' }}">
                                        مجاني
                                    </span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="price_type" value="paid" class="hidden" @checked($priceType === 'paid')>
                                    <span class="block text-center rounded-2xl border px-3 py-2 hover:bg-slate-50 transition {{ $priceType === 'paid' ? 'border-[#3d195c] bg-[#3d195c]/5 text-[#3d195c]' : 'border-slate-200 text-slate-700' }}">
                                        مدفوع
                                    </span>
                                </label>
                            </div>
                        </div>

                        {{-- Category --}}
                        <div x-data="{
                            selectedCategory: {{ $selectedCategory }},
                            selectedSub: {{ $selectedSub }},
                            subs: @js($flatSubs),
                            get filteredSubs() { return this.subs.filter(s => !this.selectedCategory || s.parent_id === this.selectedCategory); },
                        }">
                            <label class="block text-xs font-extrabold text-slate-700 mb-2">التصنيف</label>
                            <select
                                name="category"
                                class="w-full rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c] text-sm"
                                x-model.number="selectedCategory"
                                @change="selectedSub = 0"
                            >
                                <option value="0">كل التصنيفات</option>
                                @foreach($categoriesTree as $cat)
                                    <option value="{{ (int) $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>

                            <div class="mt-2">
                                <select
                                    name="sub"
                                    class="w-full rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c] text-sm"
                                    x-model.number="selectedSub"
                                    :disabled="!selectedCategory"
                                >
                                    <option value="0">كل الأقسام الفرعية</option>
                                    <template x-for="s in filteredSubs" :key="s.id">
                                        <option :value="s.id" x-text="s.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Instructor --}}
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 mb-2">المدرب</label>
                            <select name="instructor" class="w-full rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c] text-sm">
                                <option value="0">كل المدربين</option>
                                @foreach($instructors as $inst)
                                    <option value="{{ (int) $inst->id }}" @selected($selectedInstructor === (int) $inst->id)>{{ $inst->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Rating --}}
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 mb-2">التقييم</label>
                            <div class="space-y-2 text-sm">
                                @php $ratingOptions = ['' => 'الكل', '4' => '4+ نجوم', '3' => '3+ نجوم', '2' => '2+ نجوم']; @endphp
                                @foreach($ratingOptions as $val => $label)
                                    <label class="flex items-center justify-between gap-3 cursor-pointer rounded-2xl border px-4 py-3 hover:bg-slate-50 transition {{ (string) $selectedRating === (string) $val ? 'border-[#3d195c] bg-[#3d195c]/5' : 'border-slate-200' }}">
                                        <span class="font-bold text-slate-800">{{ $label }}</span>
                                        <input type="radio" name="rating" value="{{ $val }}" class="accent-[#3d195c]" @checked((string) $selectedRating === (string) $val)>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                            تطبيق الفلاتر
                        </button>
                    </div>
                </form>
            </aside>

            {{-- Left: Results --}}
            <div class="lg:col-span-9 xl:col-span-9 lg:col-start-1 lg:col-end-10">
                {{-- Quick category chips --}}
                <div class="flex flex-wrap gap-2 mb-5">
                    <a
                        href="{{ route('site.courses') }}"
                        class="text-xs font-extrabold px-4 py-2 rounded-2xl border transition {{ ($selectedCategory === 0 && $selectedSub === 0) ? 'border-[#3d195c] bg-[#3d195c]/5 text-[#3d195c]' : 'border-slate-200 text-slate-700 hover:bg-white' }}"
                    >
                        كل التصنيفات
                    </a>
                    @foreach($categoriesTree->take(10) as $cat)
                        <a
                            href="{{ route('site.courses', array_filter(['category' => (int) $cat->id, 'sort' => $sort])) }}"
                            class="text-xs font-extrabold px-4 py-2 rounded-2xl border transition {{ ($selectedCategory === (int) $cat->id && $selectedSub === 0) ? 'border-[#3d195c] bg-[#3d195c]/5 text-[#3d195c]' : 'border-slate-200 text-slate-700 hover:bg-white' }}"
                        >
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>

                @if($courses->count() === 0)
                    <div class="rounded-3xl border bg-white p-10 text-center">
                        <div class="text-lg font-extrabold text-slate-900">لا توجد دورات مطابقة</div>
                        <div class="text-sm text-slate-600 mt-2">جرّب تعديل الفلاتر أو مسحها ثم البحث مجددًا.</div>
                        <div class="mt-6">
                            <a href="{{ route('site.courses') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-slate-100 text-slate-800 font-extrabold hover:bg-slate-200 transition">
                                عرض كل الدورات
                            </a>
                        </div>
                    </div>
                @else
                    @php
                        $courseWishlistIds = session('course_wishlist', []);
                        $courseWishlistIds = is_array($courseWishlistIds) ? array_map('intval', $courseWishlistIds) : [];
                    @endphp
                    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($courses as $course)
                            <div class="group rounded-3xl border bg-white overflow-hidden hover:shadow-lg transition">
                                <div class="relative aspect-[16/9] bg-slate-100 overflow-hidden">
                                    <x-wishlist-heart :course="$course" :in-wishlist="in_array((int) $course->id, $courseWishlistIds)" />
                                    <a href="{{ route('site.course.show', $course) }}" class="block w-full h-full">
                                        @if($course->cover_image)
                                            <img src="{{ $course->cover_image }}" alt="{{ $course->title }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition duration-300" loading="lazy" />
                                        @endif
                                    </a>
                                </div>
                                <a href="{{ route('site.course.show', $course) }}" class="block p-4">
                                    <div class="text-sm font-extrabold text-slate-900 line-clamp-2">{{ $course->title }}</div>
                                    <div class="mt-2 flex items-center justify-between gap-2 text-xs text-slate-600">
                                        <div class="font-bold line-clamp-1">{{ $course->instructor?->name }}</div>
                                        <div class="inline-flex items-center gap-1">
                                            <span>⭐</span>
                                            <span class="font-extrabold text-slate-900">{{ number_format((float) ($course->rating ?? 0), 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between">
                                        <div class="text-[11px] font-bold text-slate-500">
                                            {{ $course->subCategory?->name ?? $course->category?->name }}
                                        </div>
                                        <div class="text-sm font-extrabold text-slate-900">
                                            @php $p = (float) ($course->offer_price ?? $course->price ?? 0); @endphp
                                            {{ $p > 0 ? number_format($p, 2) . ' ج.م' : 'مجاني' }}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $courses->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

