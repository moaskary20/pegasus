@extends('layouts.site')

@section('content')
    @php
        $flatSubs = collect($categoriesTree)
            ->flatMap(fn ($c) => collect($c->children ?? [])->map(fn ($ch) => ['id' => $ch->id, 'name' => $ch->name, 'parent_id' => $c->id]))
            ->values();
    @endphp

    <section class="bg-slate-50 border-b">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <div class="text-xs font-extrabold tracking-widest text-slate-500 uppercase">STORE</div>
                    <h1 class="mt-2 text-2xl md:text-3xl font-extrabold text-slate-900">المتجر</h1>
                    <p class="text-sm text-slate-600 mt-2">تصفّح المنتجات مع فلاتر للوصول السريع لما تحتاجه.</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="text-xs text-slate-500">النتائج:</div>
                    <div class="text-sm font-extrabold text-slate-900">{{ number_format((int) $products->total()) }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 py-10" style="direction: rtl;">
        <div class="grid lg:grid-cols-12 gap-6 items-start">
            {{-- Filters --}}
            <aside class="lg:col-span-4 xl:col-span-3 lg:col-start-10">
                <form action="{{ route('site.store') }}" method="get" class="rounded-3xl border bg-white overflow-hidden shadow-sm">
                    <div class="px-5 py-4 border-b bg-slate-50 flex items-center justify-between">
                        <div class="text-sm font-extrabold text-slate-900">فلترة المنتجات</div>
                        <a href="{{ route('site.store') }}" class="text-xs font-bold text-[#3d195c] hover:underline">مسح الفلاتر</a>
                    </div>

                    <div class="p-5 space-y-5">
                        {{-- Sort --}}
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 mb-2">الترتيب</label>
                            <select name="sort" class="w-full rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c] text-sm">
                                <option value="newest" @selected($sort === 'newest')>الأحدث</option>
                                <option value="price_asc" @selected($sort === 'price_asc')>السعر: من الأقل</option>
                                <option value="price_desc" @selected($sort === 'price_desc')>السعر: من الأعلى</option>
                                <option value="rating" @selected($sort === 'rating')>الأعلى تقييماً</option>
                                <option value="popular" @selected($sort === 'popular')>الأكثر مبيعاً</option>
                            </select>
                        </div>

                        {{-- Price --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-extrabold text-slate-700">السعر</label>
                                <div class="text-[11px] text-slate-500">ج.م</div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" step="0.01" min="0" name="min_price" value="{{ $minPrice ?? '' }}" placeholder="من" class="w-full rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c] text-sm">
                                <input type="number" step="0.01" min="0" name="max_price" value="{{ $maxPrice ?? '' }}" placeholder="إلى" class="w-full rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c] text-sm">
                            </div>
                            <div class="mt-3 grid grid-cols-3 gap-2 text-xs font-bold">
                                <label class="cursor-pointer">
                                    <input type="radio" name="price_type" value="" class="hidden" @checked($priceType === '')>
                                    <span class="block text-center rounded-2xl border px-3 py-2 hover:bg-slate-50 transition {{ $priceType === '' ? 'border-[#3d195c] bg-[#3d195c]/5 text-[#3d195c]' : 'border-slate-200 text-slate-700' }}">الكل</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="price_type" value="free" class="hidden" @checked($priceType === 'free')>
                                    <span class="block text-center rounded-2xl border px-3 py-2 hover:bg-slate-50 transition {{ $priceType === 'free' ? 'border-[#3d195c] bg-[#3d195c]/5 text-[#3d195c]' : 'border-slate-200 text-slate-700' }}">مجاني</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="price_type" value="paid" class="hidden" @checked($priceType === 'paid')>
                                    <span class="block text-center rounded-2xl border px-3 py-2 hover:bg-slate-50 transition {{ $priceType === 'paid' ? 'border-[#3d195c] bg-[#3d195c]/5 text-[#3d195c]' : 'border-slate-200 text-slate-700' }}">مدفوع</span>
                                </label>
                            </div>
                        </div>

                        {{-- Category --}}
                        <div x-data="{ selectedCategory: {{ $categoryId ?: 0 }}, selectedSub: {{ $subCategoryId ?: 0 }}, subs: @js($flatSubs->toArray()), get filteredSubs() { return this.subs.filter(s => !this.selectedCategory || s.parent_id == this.selectedCategory); } }">
                            <label class="block text-xs font-extrabold text-slate-700 mb-2">التصنيف</label>
                            <select name="category" class="w-full rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c] text-sm" x-model.number="selectedCategory" @change="selectedSub = 0">
                                <option value="0">كل التصنيفات</option>
                                @foreach($categoriesTree as $cat)
                                    <option value="{{ (int) $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <div class="mt-2">
                                <select name="sub" class="w-full rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c] text-sm" x-model.number="selectedSub" :disabled="!selectedCategory">
                                    <option value="0">كل الأقسام الفرعية</option>
                                    <template x-for="s in filteredSubs" :key="s.id">
                                        <option :value="s.id" x-text="s.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Featured --}}
                        <div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="featured" value="1" class="rounded accent-[#3d195c]" @checked($featured)>
                                <span class="text-sm font-bold text-slate-700">منتجات مميزة فقط</span>
                            </label>
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                            تطبيق الفلاتر
                        </button>
                    </div>
                </form>
            </aside>

            {{-- Results --}}
            <div class="lg:col-span-9 xl:col-span-9 lg:col-start-1 lg:col-end-10">
                {{-- Quick category chips --}}
                <div class="flex flex-wrap gap-2 mb-5">
                    <a href="{{ route('site.store') }}" class="text-xs font-extrabold px-4 py-2 rounded-2xl border transition {{ ($categoryId === 0 && $subCategoryId === 0) ? 'border-[#3d195c] bg-[#3d195c]/5 text-[#3d195c]' : 'border-slate-200 text-slate-700 hover:bg-white' }}">
                        كل التصنيفات
                    </a>
                    @foreach($categoriesTree->take(10) as $cat)
                        <a href="{{ route('site.store', array_filter(['category' => (int) $cat->id, 'sort' => $sort])) }}" class="text-xs font-extrabold px-4 py-2 rounded-2xl border transition {{ ($categoryId === (int) $cat->id && $subCategoryId === 0) ? 'border-[#3d195c] bg-[#3d195c]/5 text-[#3d195c]' : 'border-slate-200 text-slate-700 hover:bg-white' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>

                @if(session('notice'))
                    <div class="mb-4 rounded-2xl border px-4 py-3 text-sm font-semibold {{ (session('notice')['type'] ?? '') === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}">
                        {{ session('notice')['message'] ?? '' }}
                    </div>
                @endif

                @if($products->count() === 0)
                    <div class="rounded-3xl border bg-white p-10 text-center">
                        <div class="text-lg font-extrabold text-slate-900">لا توجد منتجات مطابقة</div>
                        <div class="text-sm text-slate-600 mt-2">جرّب تعديل الفلاتر أو مسحها ثم البحث مجدداً.</div>
                        <div class="mt-6">
                            <a href="{{ route('site.store') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-slate-100 text-slate-800 font-extrabold hover:bg-slate-200 transition">
                                عرض كل المنتجات
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($products as $product)
                            @php
                                $imgUrl = $product->main_image ? asset('storage/' . ltrim($product->main_image, '/')) : ($product->images->first()?->url ?? null);
                            @endphp
                            <div class="group rounded-3xl border bg-white overflow-hidden hover:shadow-lg transition">
                                <div class="relative aspect-square bg-slate-100 overflow-hidden">
                                    <x-wishlist-heart-product :product="$product" :in-wishlist="in_array((int) $product->id, $productWishlistIds ?? [])" />
                                    <a href="{{ route('site.store.product', $product) }}" class="block w-full h-full">
                                        @if($imgUrl)
                                            <img src="{{ $imgUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition duration-300" loading="lazy">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-200 to-slate-100">
                                                <svg class="w-16 h-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6 6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </a>
                                    @if($product->discount_percentage)
                                        <span class="absolute top-2 right-2 px-2 py-1 rounded-lg bg-rose-500 text-white text-xs font-bold">خصم {{ (int) $product->discount_percentage }}%</span>
                                    @endif
                                    @if($product->is_featured)
                                        <span class="absolute bottom-2 right-2 px-2 py-1 rounded-lg bg-[#3d195c] text-white text-xs font-bold">مميز</span>
                                    @endif
                                </div>
                                <a href="{{ route('site.store.product', $product) }}" class="block p-4">
                                    <div class="text-sm font-extrabold text-slate-900 line-clamp-2">{{ $product->name }}</div>
                                    <div class="mt-2 text-xs text-slate-500">{{ $product->category?->name ?? '—' }}</div>
                                    <div class="mt-3 flex items-center justify-between">
                                        <div class="flex items-center gap-1 text-xs">
                                            <span>⭐</span>
                                            <span class="font-extrabold text-slate-900">{{ number_format((float) ($product->average_rating ?? 0), 1) }}</span>
                                            <span class="text-slate-500">({{ (int) ($product->ratings_count ?? 0) }})</span>
                                        </div>
                                        <div class="text-sm font-extrabold text-slate-900">
                                            @if($product->compare_price && $product->compare_price > $product->price)
                                                <span class="text-slate-400 line-through text-xs">{{ number_format((float) $product->compare_price, 2) }}</span>
                                                <span class="text-rose-600">{{ number_format((float) $product->price, 2) }} ج.م</span>
                                            @else
                                                {{ (float) $product->price > 0 ? number_format((float) $product->price, 2) . ' ج.م' : 'مجاني' }}
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
