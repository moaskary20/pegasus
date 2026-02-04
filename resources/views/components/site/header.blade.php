@php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    $logoPath = \App\Models\PlatformSetting::get('site_logo_path', null);
    $logoAlt = \App\Models\PlatformSetting::get('site_logo_alt', config('app.name', 'Pegasus Academy'));
    $logoUrl = $logoPath ? asset('storage/' . ltrim($logoPath, '/')) : null;

    // Courses cart (Session)
    $courseCartIds = session('cart', []);
    $courseCart = collect();
    if (is_array($courseCartIds) && count($courseCartIds)) {
        $courseCart = \App\Models\Course::query()
            ->whereIn('id', $courseCartIds)
            ->with('instructor')
            ->get();
    }

    // Courses wishlist (Session)
    $courseWishlistIds = session('course_wishlist', []);
    $courseWishlist = collect();
    if (is_array($courseWishlistIds) && count($courseWishlistIds)) {
        $courseWishlist = \App\Models\Course::query()
            ->whereIn('id', $courseWishlistIds)
            ->with('instructor')
            ->get();
    }

    // Store cart (DB)
    $sessionId = session()->getId();
    $storeCart = \App\Models\StoreCart::getCart($user?->id, $user ? null : $sessionId);

    // Store wishlist (DB)
    $storeWishlist = collect();
    if ($user) {
        $storeWishlist = \App\Models\Wishlist::query()
            ->where('user_id', $user->id)
            ->with('product')
            ->latest()
            ->limit(8)
            ->get();
    }

    $coursesCartCount = $courseCart->count();
    $storeCartCount = $storeCart->count();
    $cartCount = $coursesCartCount + $storeCartCount;

    $coursesWishlistCount = $courseWishlist->count();
    $storeWishlistCount = $storeWishlist->count();
    $wishlistCount = $coursesWishlistCount + $storeWishlistCount;

    $unreadMessagesCount = $user ? ($user->unread_messages_count ?? 0) : 0;

    // Public website mega menu data (Courses)
    $navCategories = \App\Models\Category::query()
        ->whereNull('parent_id')
        ->with([
            'children' => fn ($q) => $q->orderBy('sort_order')->orderBy('name'),
        ])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    // Build a fast lookup for courses under each parent/child category (avoid N+1 queries in Blade)
    $navTopIds = $navCategories->pluck('id')->values();
    $navChildIds = $navCategories->flatMap(fn ($c) => $c->children->pluck('id'))->values();

    $navChildToParent = [];
    foreach ($navCategories as $p) {
        foreach ($p->children as $ch) {
            $navChildToParent[(int) $ch->id] = (int) $p->id;
        }
    }

    $navCourseTree = [];
    foreach ($navCategories as $p) {
        $navCourseTree[(int) $p->id] = [
            'total' => 0,
            'direct' => [],
            'children' => [],
        ];
    }

    $navCategoryIdsForCourses = $navTopIds->merge($navChildIds)->unique()->values();

    if ($navCategoryIdsForCourses->count() > 0) {
        $navCourses = \App\Models\Course::query()
            ->select(['id', 'title', 'rating', 'price', 'offer_price', 'category_id', 'sub_category_id'])
            ->where('is_published', true)
            ->where(function ($q) use ($navCategoryIdsForCourses, $navChildIds) {
                // Courses linked directly to a (parent or child) category
                $q->whereIn('category_id', $navCategoryIdsForCourses);

                // Some data may also use sub_category_id for linking; include it as a fallback
                if ($navChildIds->count() > 0) {
                    $q->orWhereIn('sub_category_id', $navChildIds);
                }
            })
            ->orderByDesc('rating')
            ->orderByDesc('id')
            ->get();

        foreach ($navCourses as $c) {
            $parentId = null;
            $childId = null;

            if (!empty($c->sub_category_id) && isset($navChildToParent[(int) $c->sub_category_id])) {
                $childId = (int) $c->sub_category_id;
                $parentId = (int) $navChildToParent[$childId];
            } elseif (!empty($c->category_id) && isset($navChildToParent[(int) $c->category_id])) {
                // Some records may point child category directly via category_id
                $childId = (int) $c->category_id;
                $parentId = (int) $navChildToParent[$childId];
            } elseif (!empty($c->category_id) && $navTopIds->contains((int) $c->category_id)) {
                $parentId = (int) $c->category_id;
            }

            if (!$parentId || !isset($navCourseTree[$parentId])) {
                continue;
            }

            $navCourseTree[$parentId]['total']++;

            if ($childId) {
                if (!isset($navCourseTree[$parentId]['children'][$childId])) {
                    $navCourseTree[$parentId]['children'][$childId] = [];
                }
                $navCourseTree[$parentId]['children'][$childId][] = $c;
            } else {
                $navCourseTree[$parentId]['direct'][] = $c;
            }
        }
    }

    // Store mega menu data
    $navStoreCategories = \App\Models\ProductCategory::query()
        ->active()
        ->whereNull('parent_id')
        ->with([
            'children' => fn ($q) => $q->active()->orderBy('sort_order')->orderBy('name'),
        ])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    $navStoreTopIds = $navStoreCategories->pluck('id')->values();
    $navStoreChildIds = $navStoreCategories->flatMap(fn ($c) => $c->children->pluck('id'))->values();
    $navStoreChildToParent = [];
    foreach ($navStoreCategories as $p) {
        foreach ($p->children as $ch) {
            $navStoreChildToParent[(int) $ch->id] = (int) $p->id;
        }
    }

    $navStoreTree = [];
    foreach ($navStoreCategories as $p) {
        $navStoreTree[(int) $p->id] = [
            'total' => 0,
            'direct' => [],
            'children' => [],
        ];
    }

    $navStoreCategoryIds = $navStoreTopIds->merge($navStoreChildIds)->unique()->values();

    if ($navStoreCategoryIds->count() > 0) {
        $navProducts = \App\Models\Product::query()
            ->select(['id', 'name', 'slug', 'price', 'average_rating', 'category_id'])
            ->active()
            ->inStock()
            ->whereIn('category_id', $navStoreCategoryIds)
            ->orderByDesc('average_rating')
            ->orderByDesc('sales_count')
            ->get();

        foreach ($navProducts as $prod) {
            $parentId = null;
            $childId = null;

            if (!empty($prod->category_id) && isset($navStoreChildToParent[(int) $prod->category_id])) {
                $childId = (int) $prod->category_id;
                $parentId = (int) $navStoreChildToParent[$childId];
            } elseif (!empty($prod->category_id) && $navStoreTopIds->contains((int) $prod->category_id)) {
                $parentId = (int) $prod->category_id;
            }

            if (!$parentId || !isset($navStoreTree[$parentId])) {
                continue;
            }

            $navStoreTree[$parentId]['total']++;

            if ($childId) {
                if (!isset($navStoreTree[$parentId]['children'][$childId])) {
                    $navStoreTree[$parentId]['children'][$childId] = [];
                }
                $navStoreTree[$parentId]['children'][$childId][] = $prod;
            } else {
                $navStoreTree[$parentId]['direct'][] = $prod;
            }
        }
    }
@endphp

<header class="sticky top-0 z-50">
    {{-- Top strip --}}
    <div class="bg-[#2c004d] text-white">
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between gap-3">
            <div class="text-xs sm:text-sm opacity-90">
                منصة تعليمية احترافية — تعلّم واشتري وشارك بسهولة.
            </div>
            <div class="flex items-center gap-3 text-xs sm:text-sm">
                <a href="{{ route('site.support') }}" class="hover:underline underline-offset-4">المساعدة والدعم</a>
                <span class="opacity-30">|</span>
                <div x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false" class="relative">
                    <button type="button" class="inline-flex items-center gap-1 hover:underline underline-offset-4">
                        <span>اللغة</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-44 rounded-xl border border-white/10 bg-white text-slate-900 shadow-xl overflow-hidden" style="direction: rtl;">
                        <a href="{{ route('lang.switch', ['locale' => 'ar']) }}" class="block px-4 py-2 text-sm hover:bg-slate-50">العربية</a>
                        <a href="{{ route('lang.switch', ['locale' => 'en']) }}" class="block px-4 py-2 text-sm hover:bg-slate-50">English</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main nav (Udemy-like) --}}
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 py-3">
            {{-- Row 1: Icons + Search + Logo (same level) --}}
            <div class="flex items-center gap-4">
                {{-- Icons (right of search) --}}
                <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                    {{-- User menu (moved to wishlist position) --}}
                    <div x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false" class="relative">
                        <a href="{{ $user ? url('/account') : route('site.auth') }}" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition-colors" style="direction: rtl;">
                            <div class="w-9 h-9 rounded-full bg-slate-200 overflow-hidden flex items-center justify-center">
                                @if($user && $user->avatar)
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
                                @else
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                @endif
                            </div>
                        </a>

                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-[320px] rounded-2xl border bg-white shadow-xl overflow-hidden" style="direction: rtl;">
                            <div class="px-4 py-4 bg-gradient-to-l from-[#2c004d] to-[#2c004d]/90 text-white">
                                @if($user)
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-full bg-white/15 overflow-hidden flex items-center justify-center shrink-0">
                                            @if($user->avatar)
                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
                                            @else
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold truncate">{{ $user->name }}</div>
                                            <div class="text-xs opacity-90 truncate">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="font-bold">مرحباً بك</div>
                                    <div class="text-xs opacity-90">سجّل الدخول للوصول لكل المزايا</div>
                                @endif
                            </div>

                            <div class="py-2">
                                <a href="{{ route('site.my-courses') }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-50">
                                    <span>تعلّمي / دوراتي</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                @if($user)
                                <a href="{{ route('site.my-assignments') }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-50">
                                    <span>واجباتي</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                @endif
                                <a href="{{ route('site.cart') }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-50">
                                    <span>سلة المشتريات</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                <a href="{{ route('site.wishlist') }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-50">
                                    <span>قائمة الرغبات</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                @php
                                    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
                                @endphp
                                <a href="{{ route('site.notifications') }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-50">
                                    <div class="flex items-center gap-2">
                                        <span>الإشعارات</span>
                                        @if($unreadCount > 0)
                                            <span class="min-w-[20px] h-5 px-1.5 flex items-center justify-center text-[11px] font-bold text-white bg-red-500 rounded-full">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                        @endif
                                    </div>
                                    <span class="text-slate-400">›</span>
                                </a>
                                <a href="{{ route('site.messages') }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-50">
                                    <span>الرسائل</span>
                                    @if($unreadMessagesCount > 0)
                                        <span class="min-w-[20px] h-5 px-1.5 flex items-center justify-center text-[11px] font-bold text-white bg-rose-500 rounded-full">{{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}</span>
                                    @endif
                                    <span class="text-slate-400">›</span>
                                </a>
                                <div class="h-px bg-slate-100 my-2"></div>
                                <a href="{{ route('site.account') }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-50">
                                    <span>إعدادات الحساب</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                <a href="{{ route('site.subscriptions') }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-50">
                                    <span>الاشتراكات</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                <a href="{{ route('site.purchase-history') }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-50">
                                    <span>سجل المشتريات</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                <a href="{{ route('site.support') }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-50">
                                    <span>المساعدة والدعم</span>
                                    <span class="text-slate-400">›</span>
                                </a>

                                @if($user)
                                    <form method="POST" action="{{ url('/admin/logout') }}" class="mt-2">
                                        @csrf
                                        <button type="submit" class="w-full text-right px-4 py-2 text-sm hover:bg-rose-50 text-rose-600">
                                            تسجيل الخروج
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('site.auth') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 text-[#2c004d] font-semibold">
                                        تسجيل الدخول
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Cart --}}
                    <div x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false" class="relative">
                        <a href="{{ route('site.cart') }}" class="relative p-2 rounded-xl hover:bg-slate-100 transition-colors" aria-label="سلة المشتريات">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            @if($cartCount > 0)
                                <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 flex items-center justify-center text-[10px] font-bold text-white bg-emerald-500 rounded-full">
                                    {{ $cartCount > 99 ? '99+' : $cartCount }}
                                </span>
                            @endif
                        </a>

                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-[380px] rounded-2xl border bg-white shadow-xl overflow-hidden" style="direction: rtl;">
                            <div class="px-4 py-3 bg-slate-50 border-b flex items-center justify-between">
                                <div class="font-semibold text-sm">سلة المشتريات</div>
                                <a href="{{ route('site.cart') }}" class="text-xs text-[#2c004d] hover:underline">فتح السلة</a>
                            </div>

                            <div class="max-h-80 overflow-y-auto">
                                @if($cartCount === 0)
                                    <div class="px-4 py-8 text-center text-sm text-slate-500">السلة فارغة</div>
                                @else
                                    @if($courseCart->count())
                                        <div class="px-4 pt-3 text-xs font-bold text-slate-500">سلة الدورات</div>
                                        @foreach($courseCart->take(4) as $c)
                                            <div class="px-4 py-3 border-b hover:bg-slate-50">
                                                <a href="{{ route('site.course.show', $c) }}" class="text-sm font-semibold text-slate-900 line-clamp-1 hover:underline">{{ $c->title }}</a>
                                                <div class="flex items-center justify-between text-xs text-slate-500 mt-1">
                                                    <span>{{ $c->instructor?->name }}</span>
                                                    <span class="font-semibold text-slate-700">
                                                        @php $p = (float) ($c->offer_price ?? $c->price ?? 0); @endphp
                                                        {{ $p > 0 ? number_format($p, 2) . ' ج.م' : 'مجاني' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                    @if($storeCart->count())
                                        <div class="px-4 pt-3 text-xs font-bold text-slate-500">سلة المتجر</div>
                                        @foreach($storeCart->take(4) as $i)
                                            <div class="px-4 py-3 border-b hover:bg-slate-50">
                                                <div class="text-sm font-semibold text-slate-900 line-clamp-1">{{ $i->product?->name ?? 'منتج' }}</div>
                                                <div class="flex items-center justify-between text-xs text-slate-500 mt-1">
                                                    <span>الكمية: {{ $i->quantity }}</span>
                                                    <span class="font-semibold text-slate-700">{{ number_format($i->total, 2) }} ج.م</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Notifications (API) --}}
                    <div
                        x-data="{
                            open:false,
                            count: 0,
                            notifications: [],
                            init() { this.fetchCount(); },
                            async fetchCount() {
                                try {
                                    const res = await fetch('/api/notifications/unread-count', { headers: { 'Accept': 'application/json' } });
                                    if (!res.ok) return;
                                    const data = await res.json();
                                    this.count = data.count || 0;
                                } catch (e) {}
                            },
                            async fetchNotifications() {
                                try {
                                    const res = await fetch('/api/notifications?unread=1&per_page=5', { headers: { 'Accept': 'application/json' } });
                                    if (!res.ok) return;
                                    const data = await res.json();
                                    this.notifications = (data.notifications || []).map(n => ({
                                        id: n.id,
                                        title: n.data?.title || 'إشعار جديد',
                                        message: n.data?.message || '',
                                        created_at: n.created_at,
                                    }));
                                    this.count = data.meta?.unread_count || this.count;
                                } catch (e) {}
                            },
                            async markAsRead(id) {
                                try {
                                    await fetch(`/api/notifications/${id}/read`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                                            'Accept': 'application/json',
                                        },
                                    });
                                    this.notifications = this.notifications.filter(n => n.id !== id);
                                    this.count = Math.max(0, this.count - 1);
                                } catch (e) {}
                            },
                        }"
                        @mouseenter="open=true; fetchNotifications()"
                        @mouseleave="open=false"
                        class="relative"
                    >
                        <a href="{{ $user ? route('site.notifications') : route('site.auth') }}" class="relative inline-flex p-2 rounded-xl hover:bg-slate-100 transition-colors" aria-label="الإشعارات">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span
                                x-show="count > 0"
                                x-text="count > 99 ? '99+' : count"
                                x-cloak
                                x-transition
                                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 flex items-center justify-center text-[10px] font-bold text-white bg-rose-500 rounded-full"
                            ></span>
                        </a>

                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-[360px] rounded-2xl border bg-white shadow-xl overflow-hidden" style="direction: rtl;">
                            <div class="px-4 py-3 bg-slate-50 border-b flex items-center justify-between">
                                <div class="font-semibold text-sm">الإشعارات</div>
                                @if($user)
                                    <a href="{{ route('site.notifications') }}" class="text-xs text-[#2c004d] hover:underline font-bold">عرض كل الإشعارات</a>
                                @else
                                    <a href="{{ route('site.auth') }}" class="text-xs text-[#2c004d] hover:underline">تسجيل الدخول</a>
                                @endif
                            </div>

                            <div class="max-h-80 overflow-y-auto">
                                <template x-if="notifications.length === 0">
                                    <div class="px-4 py-8 text-center text-sm text-slate-500">لا توجد إشعارات جديدة</div>
                                </template>
                                <template x-for="n in notifications" :key="n.id">
                                    <div class="px-4 py-3 border-b hover:bg-slate-50 cursor-pointer" @click="markAsRead(n.id)">
                                        <div class="text-sm font-semibold text-slate-900" x-text="n.title"></div>
                                        <div class="text-xs text-slate-500 mt-1 line-clamp-1" x-text="n.message"></div>
                                    </div>
                                </template>
                            </div>

                            <div class="px-4 py-3 bg-slate-50 border-t">
                                <a href="{{ route('site.notifications') }}" class="block text-center text-sm font-semibold text-[#2c004d] hover:underline">عرض كل الإشعارات</a>
                            </div>
                        </div>
                    </div>

                    {{-- Messages --}}
                    <div
                        x-data="{
                            open: false,
                            count: {{ $unreadMessagesCount }},
                            conversations: [],
                            init() {
                                this.fetchCount();
                                setInterval(() => this.fetchCount(), 30000);
                            },
                            async fetchCount() {
                                try {
                                    const res = await fetch('/api/messages/unread-count', { headers: { 'Accept': 'application/json' } });
                                    if (!res.ok) return;
                                    const data = await res.json();
                                    this.count = data.count || 0;
                                } catch (e) {}
                            },
                            async fetchMessages() {
                                try {
                                    const res = await fetch('/api/messages/recent?limit=6', { headers: { 'Accept': 'application/json' } });
                                    if (!res.ok) return;
                                    const data = await res.json();
                                    this.conversations = data.conversations || [];
                                    this.count = data.unread_count ?? this.count;
                                } catch (e) {}
                            }
                        }"
                        @mouseenter="open=true; {{ $user ? 'fetchMessages()' : '' }}"
                        @mouseleave="open=false"
                        class="relative"
                    >
                        <a href="{{ $user ? route('site.messages') : route('site.auth') }}" class="relative inline-flex p-2 rounded-xl hover:bg-slate-100 transition-colors" aria-label="الرسائل">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span
                                x-show="count > 0"
                                x-text="count > 99 ? '99+' : count"
                                x-cloak
                                x-transition
                                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 flex items-center justify-center text-[10px] font-bold text-white bg-rose-500 rounded-full"
                            ></span>
                        </a>

                        @if($user)
                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-[360px] rounded-2xl border bg-white shadow-xl overflow-hidden z-50" style="direction: rtl;">
                            <div class="px-4 py-3 bg-slate-50 border-b flex items-center justify-between">
                                <div class="font-semibold text-sm">الرسائل</div>
                                <a href="{{ route('site.messages') }}" class="text-xs text-[#2c004d] hover:underline font-bold">عرض كل الرسائل</a>
                            </div>

                            <div class="max-h-80 overflow-y-auto">
                                <template x-if="conversations.length === 0">
                                    <div class="px-4 py-8 text-center text-sm text-slate-500">لا توجد رسائل</div>
                                </template>
                                <template x-for="c in conversations" :key="c.id">
                                    <a :href="c.url" class="block px-4 py-3 border-b hover:bg-slate-50 transition">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="text-sm font-semibold text-slate-900 truncate" x-text="c.name"></div>
                                            <span x-show="c.unread > 0" x-cloak class="min-w-[18px] h-[18px] px-1 flex items-center justify-center text-[10px] font-bold text-white bg-rose-500 rounded-full" x-text="c.unread > 99 ? '99+' : c.unread"></span>
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1 line-clamp-1" x-text="c.last_preview"></div>
                                        <div class="text-[11px] text-slate-400 mt-0.5" x-text="c.last_at"></div>
                                    </a>
                                </template>
                            </div>

                            <div class="px-4 py-3 bg-slate-50 border-t">
                                <a href="{{ route('site.messages') }}" class="block text-center text-sm font-semibold text-[#2c004d] hover:underline">عرض كل الرسائل</a>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Wishlist (moved to user menu position) --}}
                    <div x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false" class="relative">
                        <a href="{{ route('site.wishlist') }}" class="relative p-2 rounded-xl hover:bg-slate-100 transition-colors" aria-label="قائمة الرغبات">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            @if($wishlistCount > 0)
                                <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 flex items-center justify-center text-[10px] font-bold text-white bg-rose-500 rounded-full">
                                    {{ $wishlistCount > 99 ? '99+' : $wishlistCount }}
                                </span>
                            @endif
                        </a>

                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-[360px] rounded-2xl border bg-white shadow-xl overflow-hidden" style="direction: rtl;">
                            <div class="px-4 py-3 bg-slate-50 border-b flex items-center justify-between">
                                <div class="font-semibold text-sm">قائمة الرغبات</div>
                                <a href="{{ route('site.wishlist') }}" class="text-xs text-[#2c004d] hover:underline font-bold">عرض كل القائمة</a>
                            </div>

                            <div class="max-h-80 overflow-y-auto">
                                @if($wishlistCount === 0)
                                    <div class="px-4 py-8 text-center text-sm text-slate-500">لا توجد عناصر في المفضلة</div>
                                @else
                                    @if($courseWishlist->count())
                                        <div class="px-4 pt-3 text-xs font-bold text-slate-500">مفضلة الدورات</div>
                                        @foreach($courseWishlist->take(4) as $c)
                                            <a href="{{ route('site.course.show', $c) }}" class="block px-4 py-3 border-b hover:bg-slate-50">
                                                <div class="text-sm font-semibold text-slate-900 line-clamp-1">{{ $c->title }}</div>
                                                <div class="text-xs text-slate-500 mt-1">{{ $c->instructor?->name }}</div>
                                            </a>
                                        @endforeach
                                    @endif

                                    @if($storeWishlist->count())
                                        <div class="px-4 pt-3 text-xs font-bold text-slate-500">مفضلة المتجر</div>
                                        @foreach($storeWishlist->take(4) as $w)
                                            <div class="px-4 py-3 border-b hover:bg-slate-50">
                                                <div class="text-sm font-semibold text-slate-900 line-clamp-1">{{ $w->product?->name ?? 'منتج' }}</div>
                                                <div class="text-xs text-slate-500 mt-1">تمت الإضافة {{ $w->created_at?->diffForHumans() }}</div>
                                            </div>
                                        @endforeach
                                    @endif
                                @endif
                            </div>
                            <div class="px-4 py-3 bg-slate-50 border-t">
                                <a href="{{ route('site.wishlist') }}" class="block text-center text-sm font-semibold text-[#2c004d] hover:underline">عرض كل قائمة الرغبات</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Search (center) - desktop only --}}
                <div class="hidden md:block flex-1">
                    <form action="{{ route('site.search') }}" method="GET">
                        <x-search-autocomplete
                            placeholder="ابحث عن الدورات أو الدروس أو المدرسين..."
                            class="w-full"
                        />
                    </form>
                </div>

                {{-- Logo (left of search) --}}
                <a href="{{ url('/') }}" class="flex items-center gap-2 shrink-0">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $logoAlt }}" class="h-9 w-auto" style="max-width: 170px; object-fit: contain;" />
                    @else
                        <div class="h-9 w-9 rounded-xl bg-[#2c004d]/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7"/>
                            </svg>
                        </div>
                        <span class="font-extrabold text-[#2c004d]">{{ config('app.name', 'Pegasus Academy') }}</span>
                    @endif
                </a>
            </div>

            {{-- Mobile search bar + hamburger menu (below header) --}}
            <div class="md:hidden mt-3 pb-1" x-data="{ mobileMenuOpen: false, mobileMenuTab: 'nav' }">
                <div class="flex items-center gap-3">
                    <form action="{{ route('site.search') }}" method="GET" class="flex-1 min-w-0">
                        <x-search-autocomplete
                            placeholder="ابحث عن الدورات أو الدروس أو المدرسين..."
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3 focus:bg-white focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20"
                        />
                    </form>
                    <button
                        type="button"
                        @click="mobileMenuOpen = true"
                        class="shrink-0 w-12 h-12 rounded-2xl border-2 border-slate-200 bg-white hover:bg-slate-50 hover:border-[#2c004d]/30 flex flex-col items-center justify-center gap-1.5 transition-colors order-first"
                        aria-label="فتح القائمة"
                    >
                        <span class="w-6 h-0.5 bg-slate-700 rounded-full"></span>
                        <span class="w-6 h-0.5 bg-slate-700 rounded-full"></span>
                        <span class="w-6 h-0.5 bg-slate-700 rounded-full"></span>
                    </button>
                </div>

                {{-- Side drawer overlay --}}
                <div
                    x-show="mobileMenuOpen"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="mobileMenuOpen = false"
                    class="fixed inset-0 z-[60] bg-black/40 backdrop-blur-sm"
                    style="display: none;"
                ></div>

                {{-- Side drawer panel --}}
                <div
                    x-show="mobileMenuOpen"
                    x-cloak
                    x-transition:enter="transition ease-out duration-250"
                    x-transition:enter-start="transform translate-x-full"
                    x-transition:enter-end="transform translate-x-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="transform translate-x-0"
                    x-transition:leave-end="transform translate-x-full"
                    class="fixed top-0 right-0 bottom-0 z-[70] w-[300px] max-w-[85vw] bg-white shadow-2xl border-l flex flex-col overflow-hidden"
                    style="display: none; direction: rtl;"
                >
                    <div class="flex items-center justify-between px-4 py-3 border-b bg-[#2c004d] text-white shrink-0">
                        <div class="flex rounded-xl bg-white/10 p-1 gap-1">
                            <button type="button" @click="mobileMenuTab = 'nav'" class="px-4 py-2 rounded-lg text-sm font-bold transition" :class="mobileMenuTab === 'nav' ? 'bg-white text-[#2c004d]' : 'text-white/80 hover:text-white'">
                                التنقل
                            </button>
                            <button type="button" @click="mobileMenuTab = 'account'" class="px-4 py-2 rounded-lg text-sm font-bold transition" :class="mobileMenuTab === 'account' ? 'bg-white text-[#2c004d]' : 'text-white/80 hover:text-white'">
                                حسابي والخدمات
                            </button>
                        </div>
                        <button type="button" @click="mobileMenuOpen = false" class="p-2 rounded-xl hover:bg-white/10 transition" aria-label="إغلاق">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto py-4">
                        {{-- تبويب التنقل --}}
                        <div x-show="mobileMenuTab === 'nav'" x-cloak class="px-4" style="display: none;">
                            <nav class="space-y-0.5">
                                <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <svg class="w-5 h-5 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    الرئيسية
                                </a>
                                <a href="{{ route('site.about') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <svg class="w-5 h-5 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    من نحن
                                </a>
                                <a href="{{ url('/courses') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <svg class="w-5 h-5 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    الدورات
                                </a>
                                <a href="{{ route('site.store') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <svg class="w-5 h-5 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    المتجر
                                </a>
                                <a href="{{ route('site.contact') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <svg class="w-5 h-5 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    الاتصال بنا
                                </a>
                            </nav>
                        </div>

                        {{-- تبويب حسابي والخدمات --}}
                        <div x-show="mobileMenuTab === 'account'" x-cloak class="px-4" style="display: none;">
                            <div class="space-y-0.5">
                                @if($user)
                                    <div class="px-4 py-3 flex items-center gap-3 border-b border-slate-100 mb-2">
                                        <div class="w-10 h-10 rounded-full bg-[#2c004d]/10 overflow-hidden flex items-center justify-center shrink-0">
                                            @if($user->avatar)
                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
                                            @else
                                                <svg class="w-5 h-5 text-[#2c004d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 truncate text-sm">{{ $user->name }}</div>
                                            <div class="text-xs text-slate-500 truncate">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                @endif
                                <a href="{{ route('site.my-courses') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <span>تعلّمي / دوراتي</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                @if($user)
                                <a href="{{ route('site.my-assignments') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <span>واجباتي</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                @endif
                                <a href="{{ route('site.cart') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <span>سلة المشتريات</span>
                                    @if($cartCount > 0)
                                        <span class="min-w-[20px] h-5 px-1.5 flex items-center justify-center text-[11px] font-bold text-white bg-emerald-500 rounded-full">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                                    @else
                                        <span class="text-slate-400">›</span>
                                    @endif
                                </a>
                                <a href="{{ route('site.wishlist') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <span>قائمة الرغبات</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                @php $unreadNotif = $user ? $user->unreadNotifications()->count() : 0; @endphp
                                <a href="{{ route('site.notifications') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <span>الإشعارات</span>
                                    @if($unreadNotif > 0)
                                        <span class="min-w-[20px] h-5 px-1.5 flex items-center justify-center text-[11px] font-bold text-white bg-red-500 rounded-full">{{ $unreadNotif > 99 ? '99+' : $unreadNotif }}</span>
                                    @else
                                        <span class="text-slate-400">›</span>
                                    @endif
                                </a>
                                <a href="{{ route('site.messages') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <span>الرسائل</span>
                                    @if($unreadMessagesCount > 0)
                                        <span class="min-w-[20px] h-5 px-1.5 flex items-center justify-center text-[11px] font-bold text-white bg-rose-500 rounded-full">{{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}</span>
                                    @else
                                        <span class="text-slate-400">›</span>
                                    @endif
                                </a>
                                <div class="h-px bg-slate-100 my-2"></div>
                                <a href="{{ route('site.account') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <span>إعدادات الحساب</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                <a href="{{ route('site.subscriptions') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <span>الاشتراكات</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                <a href="{{ route('site.purchase-history') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <span>سجل المشتريات</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                <a href="{{ route('site.support') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 hover:bg-[#2c004d]/5 hover:text-[#2c004d] transition font-semibold" @click="mobileMenuOpen = false">
                                    <span>المساعدة والدعم</span>
                                    <span class="text-slate-400">›</span>
                                </a>
                                @if($user)
                                    <form method="POST" action="{{ url('/admin/logout') }}" class="mt-2" @submit="mobileMenuOpen = false">
                                        @csrf
                                        <button type="submit" class="w-full text-right px-4 py-3 rounded-xl text-sm hover:bg-rose-50 text-rose-600 font-semibold">
                                            تسجيل الخروج
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('site.auth') }}" class="block px-4 py-3 rounded-xl text-sm hover:bg-[#2c004d]/5 text-[#2c004d] font-bold mt-2" @click="mobileMenuOpen = false">
                                        تسجيل الدخول
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2: Menu under search --}}
            <nav class="hidden md:flex items-center justify-center gap-6 text-sm font-semibold text-slate-700 mt-2">
                <a href="{{ url('/') }}" class="hover:text-[#2c004d] transition-colors">الرئيسية</a>
                <a href="{{ route('site.about') }}" class="hover:text-[#2c004d] transition-colors">من نحن</a>
                <div
                    x-data="{
                        open: false,
                        activeId: {{ (int) ($navCategories->first()?->id ?? 0) }},
                        setActive(id){ this.activeId = id; },
                        isActive(id){ return Number(id) === Number(this.activeId); }
                    }"
                    @mouseenter="open = true"
                    @mouseleave="open = false"
                    class="relative"
                >
                    <button type="button" class="inline-flex items-center gap-1 hover:text-[#2c004d] transition-colors">
                        <span>الدورات</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Mega menu --}}
                    <div
                        x-show="open"
                        x-cloak
                        x-transition.opacity.duration.150ms
                        class="absolute right-1/2 translate-x-1/2 mt-3 w-[860px] rounded-2xl border bg-white shadow-2xl overflow-hidden z-50"
                        style="direction: rtl;"
                    >
                        <div class="grid grid-cols-12">
                            {{-- Categories column --}}
                            <div class="col-span-4 bg-slate-50 border-l p-3">
                                <div class="text-xs font-bold text-slate-500 px-2 py-2">الأقسام</div>
                                <div class="space-y-1 max-h-[420px] overflow-auto">
                                    @forelse($navCategories as $cat)
                                        @php
                                            $catTotal = (int) ($navCourseTree[(int) $cat->id]['total'] ?? 0);
                                        @endphp
                                        <button
                                            type="button"
                                            @mouseenter="setActive({{ (int) $cat->id }})"
                                            @click="window.location.href='{{ route('site.courses', ['category' => $cat->id]) }}'"
                                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-xl text-sm transition-colors cursor-pointer"
                                            :class="isActive({{ (int) $cat->id }}) ? 'bg-[#2c004d]/10 text-[#2c004d]' : 'hover:bg-white text-slate-700'"
                                        >
                                            <span class="font-semibold truncate">{{ $cat->name }}</span>
                                            <span class="flex items-center gap-2 shrink-0">
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-white/70 text-slate-600 border border-slate-200">
                                                    {{ $catTotal }}
                                                </span>
                                                <span class="text-slate-400">‹</span>
                                            </span>
                                        </button>
                                    @empty
                                        <div class="px-3 py-6 text-center text-sm text-slate-500">
                                            لا توجد تصنيفات حالياً.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Courses column --}}
                            <div class="col-span-8 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-extrabold text-slate-900">الدورات حسب القسم</div>
                                    <a href="{{ url('/courses') }}" class="text-xs font-bold text-[#2c004d] hover:underline">عرض كل الدورات</a>
                                </div>

                                <div class="mt-3 max-h-[420px] overflow-auto pr-1">
                                    @foreach($navCategories as $cat)
                                        @php
                                            $tree = $navCourseTree[(int) $cat->id] ?? ['total' => 0, 'direct' => [], 'children' => []];
                                            $directCourses = $tree['direct'] ?? [];
                                            $childCoursesMap = $tree['children'] ?? [];
                                            $totalCourses = (int) ($tree['total'] ?? 0);
                                        @endphp

                                        <div x-show="isActive({{ (int) $cat->id }})" x-cloak>
                                            <div class="flex items-center justify-between">
                                                <div class="text-base font-extrabold text-slate-900">{{ $cat->name }}</div>
                                                <div class="text-xs text-slate-500">{{ $totalCourses }} دورة</div>
                                            </div>

                                            {{-- Direct courses under parent category --}}
                                            @if(!empty($directCourses))
                                                <div class="mt-3 rounded-2xl border bg-white overflow-hidden">
                                                    <div class="px-3 py-2 bg-slate-50 border-b text-xs font-bold text-slate-600">
                                                        دورات القسم
                                                    </div>
                                                    <div class="p-3 grid grid-cols-2 gap-2">
                                                        @foreach(array_slice($directCourses, 0, 8) as $c)
                                                            <a
                                                                href="{{ route('site.course.show', ['course' => $c->slug ?? $c->id]) }}"
                                                                class="rounded-xl border hover:border-[#2c004d]/30 hover:shadow-sm transition p-3"
                                                            >
                                                                <div class="text-sm font-bold text-slate-900 line-clamp-1">{{ $c->title }}</div>
                                                                <div class="text-xs text-slate-500 mt-1">
                                                                    ⭐ {{ number_format((float) ($c->rating ?? 0), 1) }}
                                                                    <span class="opacity-60">•</span>
                                                                    {{ ((float) ($c->offer_price ?? $c->price ?? 0)) > 0 ? number_format((float) ($c->offer_price ?? $c->price), 2) . ' ج.م' : 'مجاني' }}
                                                                </div>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Sub-categories + their courses --}}
                                            @if($cat->children->count())
                                                <div class="mt-3 grid grid-cols-2 gap-3">
                                                    @foreach($cat->children as $child)
                                                        @php
                                                            $childCourses = $childCoursesMap[(int) $child->id] ?? [];
                                                        @endphp
                                                        <div class="rounded-2xl border bg-white overflow-hidden">
                                                            <a href="{{ route('site.courses', ['sub' => $child->id]) }}" class="block">
                                                                <div class="px-3 py-2 bg-slate-50 border-b flex items-center justify-between">
                                                                    <div class="text-xs font-extrabold text-slate-800 truncate">{{ $child->name }}</div>
                                                                    <div class="text-[11px] font-bold text-slate-500 shrink-0">{{ count($childCourses) }}</div>
                                                                </div>
                                                            </a>
                                                            <div class="p-3 space-y-2">
                                                                @forelse(array_slice($childCourses, 0, 6) as $c)
                                                                    <a href="{{ route('site.course.show', ['course' => $c->slug ?? $c->id]) }}" class="block rounded-xl border px-3 py-2 hover:border-[#2c004d]/30 hover:bg-slate-50 transition">
                                                                        <div class="text-sm font-bold text-slate-900 line-clamp-1">{{ $c->title }}</div>
                                                                        <div class="text-xs text-slate-500 mt-0.5">
                                                                            ⭐ {{ number_format((float) ($c->rating ?? 0), 1) }}
                                                                            <span class="opacity-60">•</span>
                                                                            {{ ((float) ($c->offer_price ?? $c->price ?? 0)) > 0 ? number_format((float) ($c->offer_price ?? $c->price), 2) . ' ج.م' : 'مجاني' }}
                                                                        </div>
                                                                    </a>
                                                                @empty
                                                                    <div class="py-6 text-center text-sm text-slate-500">
                                                                        لا توجد دورات.
                                                                    </div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if(empty($directCourses) && !$cat->children->count())
                                                <div class="py-10 text-center text-sm text-slate-500">
                                                    لا توجد دورات في هذا القسم حالياً.
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    x-data="{
                        storeOpen: false,
                        storeActiveId: {{ (int) ($navStoreCategories->first()?->id ?? 0) }},
                        storeSetActive(id){ this.storeActiveId = id; },
                        storeIsActive(id){ return Number(id) === Number(this.storeActiveId); }
                    }"
                    @mouseenter="storeOpen = true"
                    @mouseleave="storeOpen = false"
                    class="relative"
                >
                    <button type="button" class="inline-flex items-center gap-1 hover:text-[#2c004d] transition-colors">
                        <span>المتجر</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Store Mega menu --}}
                    <div
                        x-show="storeOpen"
                        x-cloak
                        x-transition.opacity.duration.150ms
                        class="absolute right-1/2 translate-x-1/2 mt-3 w-[860px] rounded-2xl border bg-white shadow-2xl overflow-hidden z-50"
                        style="direction: rtl;"
                    >
                        <div class="grid grid-cols-12">
                            {{-- Store Categories column --}}
                            <div class="col-span-4 bg-slate-50 border-l p-3">
                                <div class="text-xs font-bold text-slate-500 px-2 py-2">أقسام المتجر</div>
                                <div class="space-y-1 max-h-[420px] overflow-auto">
                                    @forelse($navStoreCategories as $cat)
                                        @php
                                            $storeCatTotal = (int) ($navStoreTree[(int) $cat->id]['total'] ?? 0);
                                        @endphp
                                        <button
                                            type="button"
                                            @mouseenter="storeSetActive({{ (int) $cat->id }})"
                                            @click="window.location.href='{{ route('site.store', ['category' => $cat->id]) }}'"
                                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-xl text-sm transition-colors cursor-pointer"
                                            :class="storeIsActive({{ (int) $cat->id }}) ? 'bg-[#2c004d]/10 text-[#2c004d]' : 'hover:bg-white text-slate-700'"
                                        >
                                            <span class="font-semibold truncate">{{ $cat->name }}</span>
                                            <span class="flex items-center gap-2 shrink-0">
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-white/70 text-slate-600 border border-slate-200">
                                                    {{ $storeCatTotal }}
                                                </span>
                                                <span class="text-slate-400">‹</span>
                                            </span>
                                        </button>
                                    @empty
                                        <div class="px-3 py-6 text-center text-sm text-slate-500">
                                            لا توجد تصنيفات للمتجر حالياً.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Products column --}}
                            <div class="col-span-8 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-extrabold text-slate-900">المنتجات حسب القسم</div>
                                    <a href="{{ route('site.store') }}" class="text-xs font-bold text-[#2c004d] hover:underline">عرض كل المتجر</a>
                                </div>

                                <div class="mt-3 max-h-[420px] overflow-auto pr-1">
                                    @forelse($navStoreCategories as $cat)
                                        @php
                                            $storeTree = $navStoreTree[(int) $cat->id] ?? ['total' => 0, 'direct' => [], 'children' => []];
                                            $storeDirectProducts = $storeTree['direct'] ?? [];
                                            $storeChildProductsMap = $storeTree['children'] ?? [];
                                            $storeTotalProducts = (int) ($storeTree['total'] ?? 0);
                                        @endphp

                                        <div x-show="storeIsActive({{ (int) $cat->id }})" x-cloak>
                                            <div class="flex items-center justify-between">
                                                <div class="text-base font-extrabold text-slate-900">{{ $cat->name }}</div>
                                                <div class="text-xs text-slate-500">{{ $storeTotalProducts }} منتج</div>
                                            </div>

                                            {{-- Direct products under parent category --}}
                                            @if(!empty($storeDirectProducts))
                                                <div class="mt-3 rounded-2xl border bg-white overflow-hidden">
                                                    <div class="px-3 py-2 bg-slate-50 border-b text-xs font-bold text-slate-600">
                                                        منتجات القسم
                                                    </div>
                                                    <div class="p-3 grid grid-cols-2 gap-2">
                                                        @foreach(array_slice($storeDirectProducts, 0, 8) as $p)
                                                            <a
                                                                href="{{ route('site.store.product', $p->slug) }}"
                                                                class="rounded-xl border hover:border-[#2c004d]/30 hover:shadow-sm transition p-3"
                                                            >
                                                                <div class="text-sm font-bold text-slate-900 line-clamp-1">{{ $p->name }}</div>
                                                                <div class="text-xs text-slate-500 mt-1">
                                                                    @if((float) ($p->average_rating ?? 0) > 0)
                                                                        ⭐ {{ number_format((float) $p->average_rating, 1) }}
                                                                        <span class="opacity-60">•</span>
                                                                    @endif
                                                                    {{ number_format((float) ($p->price ?? 0), 2) }} ج.م
                                                                </div>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Sub-categories + their products --}}
                                            @if($cat->children->count())
                                                <div class="mt-3 grid grid-cols-2 gap-3">
                                                    @foreach($cat->children as $child)
                                                        @php
                                                            $childProducts = $storeChildProductsMap[(int) $child->id] ?? [];
                                                        @endphp
                                                        <div class="rounded-2xl border bg-white overflow-hidden">
                                                            <a href="{{ route('site.store', ['sub' => $child->id]) }}" class="block">
                                                                <div class="px-3 py-2 bg-slate-50 border-b flex items-center justify-between">
                                                                    <div class="text-xs font-extrabold text-slate-800 truncate">{{ $child->name }}</div>
                                                                    <div class="text-[11px] font-bold text-slate-500 shrink-0">{{ count($childProducts) }}</div>
                                                                </div>
                                                            </a>
                                                            <div class="p-3 space-y-2">
                                                                @forelse(array_slice($childProducts, 0, 6) as $p)
                                                                    <a href="{{ route('site.store.product', $p->slug) }}" class="block rounded-xl border px-3 py-2 hover:border-[#2c004d]/30 hover:bg-slate-50 transition">
                                                                        <div class="text-sm font-bold text-slate-900 line-clamp-1">{{ $p->name }}</div>
                                                                        <div class="text-xs text-slate-500 mt-0.5">
                                                                            @if((float) ($p->average_rating ?? 0) > 0)
                                                                                ⭐ {{ number_format((float) $p->average_rating, 1) }}
                                                                                <span class="opacity-60">•</span>
                                                                            @endif
                                                                            {{ number_format((float) ($p->price ?? 0), 2) }} ج.م
                                                                        </div>
                                                                    </a>
                                                                @empty
                                                                    <div class="py-6 text-center text-sm text-slate-500">
                                                                        لا توجد منتجات.
                                                                    </div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if(empty($storeDirectProducts) && !$cat->children->count())
                                                <div class="py-10 text-center text-sm text-slate-500">
                                                    لا توجد منتجات في هذا القسم حالياً.
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="py-10 text-center text-sm text-slate-500">
                                            <a href="{{ route('site.store') }}" class="text-[#2c004d] font-bold hover:underline">عرض كل المتجر</a>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('site.contact') }}" class="hover:text-[#2c004d] transition-colors">الاتصال بنا</a>
            </nav>
        </div>
    </div>
</header>

