@extends('layouts.site')

@section('content')
    @php
        $cartSubscriptionTypes = session('cart_subscription_types', []);
        
        // Calculate course total with subscription types
        $courseTotal = 0;
        foreach ($courseCart as $c) {
            $courseId = (int) $c->id;
            $subscriptionType = $cartSubscriptionTypes[$courseId] ?? 'once';
            $coursePrice = $c->getPriceForSubscriptionType($subscriptionType);
            $courseTotal += $coursePrice;
        }
        
        $storeTotal = (float) $storeCart->sum(fn ($i) => (float) ($i->total ?? 0));
        $couponCode = (string) ($couponCode ?? '');
        $discount = (float) ($discount ?? 0);
        $grandTotal = $courseTotal + $storeTotal;
        $grandAfterDiscount = max(0, ($courseTotal - $discount)) + $storeTotal;
        $notice = session('notice');
        
        function getSubscriptionLabel($type) {
            return match ($type) {
                'once' => 'اشتراك واحد (120 يوم)',
                'monthly' => 'اشتراك شهري',
                'daily' => 'اشتراك يومي',
                default => 'اشتراك واحد',
            };
        }
    @endphp

    <section class="bg-slate-50 border-b">
        <div class="max-w-7xl mx-auto px-4 py-8" style="direction: rtl;">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <div class="text-xs font-extrabold tracking-widest text-slate-500 uppercase">CART</div>
                    <h1 class="mt-2 text-2xl md:text-3xl font-extrabold text-slate-900">سلة المشتريات</h1>
                    <p class="text-sm text-slate-600 mt-2">راجع العناصر قبل إتمام العملية.</p>
                </div>
                <div class="rounded-3xl border bg-white px-5 py-4">
                    <div class="text-xs text-slate-500">الإجمالي</div>
                    <div class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format($grandAfterDiscount, 2) }} ج.م</div>
                    @if($discount > 0)
                        <div class="text-xs text-emerald-700 font-bold mt-1">تم تطبيق خصم: {{ number_format($discount, 2) }} ج.م</div>
                    @endif
                </div>
            </div>

            @if(is_array($notice) && !empty($notice['message'] ?? ''))
                <div class="mt-5 rounded-2xl border px-4 py-3 text-sm font-bold
                    {{ ($notice['type'] ?? '') === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                    {{ $notice['message'] }}
                </div>
            @endif
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 py-10" style="direction: rtl;">
        <div class="grid lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-8 xl:col-span-9">
                {{-- Courses cart --}}
                <div class="rounded-3xl border bg-white overflow-hidden">
                    <div class="px-6 py-5 border-b bg-slate-50 flex items-center justify-between">
                        <div>
                            <div class="text-lg font-extrabold text-slate-900">سلة الدورات</div>
                            <div class="text-xs text-slate-600 mt-1">{{ number_format((int) $courseCart->count()) }} دورة</div>
                        </div>
                        @if($courseCart->count())
                            <form method="POST" action="{{ route('site.cart.courses.clear') }}">
                                @csrf
                                <button type="submit" class="text-xs font-extrabold text-rose-600 hover:underline">تفريغ سلة الدورات</button>
                            </form>
                        @endif
                    </div>

                    @if($courseCart->count() === 0)
                        <div class="p-10 text-center">
                            <div class="text-sm text-slate-600">سلة الدورات فارغة.</div>
                            <div class="mt-5">
                                <a href="{{ route('site.courses') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                                    استكشف الدورات
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="divide-y">
                            @foreach($courseCart as $c)
                                @php
                                    $coursePrice = $c->getPriceForSubscriptionType($subscriptionType);
                                @endphp
                                <div class="p-5 flex items-start gap-4">
                                    <a href="{{ route('site.course.show', $c) }}" class="w-28 h-20 rounded-2xl bg-slate-100 overflow-hidden shrink-0">
                                        @if($c->cover_image)
                                            <img src="{{ $c->cover_image }}" alt="{{ $c->title }}" class="w-full h-full object-cover" loading="lazy">
                                        @endif
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('site.course.show', $c) }}" class="block text-sm font-extrabold text-slate-900 hover:text-[#3d195c] line-clamp-2">
                                            {{ $c->title }}
                                        </a>
                                        <div class="text-xs text-slate-600 mt-1">
                                            {{ $c->instructor?->name }}
                                            @if($c->subCategory?->name || $c->category?->name)
                                                <span class="mx-1">•</span>{{ $c->subCategory?->name ?? $c->category?->name }}
                                            @endif
                                        </div>
                                        <div class="mt-3 flex items-center justify-between gap-3">
                                            <div class="text-xs text-slate-500">⭐ {{ number_format((float) ($c->rating ?? 0), 1) }}</div>
                                            <div class="text-right">
                                                <div class="text-xs text-slate-400">{{ getSubscriptionLabel($subscriptionType) }}</div>
                                                <div class="text-sm font-extrabold text-slate-900">
                                                    {{ $coursePrice > 0 ? number_format($coursePrice, 2) . ' ج.م' : 'مجاني' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('site.cart.courses.remove', $c) }}" class="shrink-0">
                                        @csrf
                                        <button type="submit" class="px-3 py-2 rounded-2xl bg-slate-100 text-slate-700 text-xs font-extrabold hover:bg-rose-50 hover:text-rose-600 transition">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Store cart (read-only preview) --}}
                <div class="mt-6 rounded-3xl border bg-white overflow-hidden">
                    <div class="px-6 py-5 border-b bg-slate-50">
                        <div class="text-lg font-extrabold text-slate-900">سلة المتجر</div>
                        <div class="text-xs text-slate-600 mt-1">{{ number_format((int) $storeCart->count()) }} منتج</div>
                    </div>
                    @if($storeCart->count() === 0)
                        <div class="p-8 text-center text-sm text-slate-600">سلة المتجر فارغة.</div>
                    @else
                        <div class="divide-y">
                            @foreach($storeCart as $i)
                                <div class="p-5 flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-extrabold text-slate-900 line-clamp-1">{{ $i->product?->name ?? 'منتج' }}</div>
                                        <div class="text-xs text-slate-600 mt-1">الكمية: {{ (int) $i->quantity }}</div>
                                    </div>
                                    <div class="text-sm font-extrabold text-slate-900 shrink-0">{{ number_format((float) $i->total, 2) }} ج.م</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Summary --}}
            <aside class="lg:col-span-4 xl:col-span-3 lg:col-start-10">
                <div class="rounded-3xl border bg-white p-6">
                    <div class="text-sm font-extrabold text-slate-900">ملخص الطلب</div>

                    {{-- Coupon --}}
                    <div class="mt-4 rounded-3xl border bg-slate-50 p-4">
                        <div class="text-xs font-extrabold text-slate-700 mb-2">كود الكوبون</div>

                        @if(!empty($couponCode))
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-extrabold text-slate-900">{{ $couponCode }}</div>
                                <form method="POST" action="{{ route('site.cart.coupon.clear') }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-extrabold text-rose-600 hover:underline">إزالة</button>
                                </form>
                            </div>
                            <div class="text-xs text-slate-600 mt-1">سيتم خصم القيمة عند الإتمام.</div>
                        @else
                            <form method="POST" action="{{ route('site.cart.coupon.apply') }}" class="flex items-center gap-2">
                                @csrf
                                <input
                                    name="coupon_code"
                                    placeholder="مثال: SAVE10"
                                    class="flex-1 rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c] text-sm"
                                />
                                <button type="submit" class="px-4 py-2 rounded-2xl bg-[#3d195c] text-white text-sm font-extrabold hover:bg-[#3d195c]/95 transition">
                                    تطبيق
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="mt-4 space-y-2 text-sm text-slate-700">
                        <div class="flex items-center justify-between">
                            <span>إجمالي الدورات</span>
                            <span class="font-extrabold">{{ number_format($courseTotal, 2) }} ج.م</span>
                        </div>
                        @if($discount > 0)
                            <div class="flex items-center justify-between text-emerald-700">
                                <span class="font-bold">خصم الكوبون</span>
                                <span class="font-extrabold">- {{ number_format($discount, 2) }} ج.م</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <span>إجمالي المتجر</span>
                            <span class="font-extrabold">{{ number_format($storeTotal, 2) }} ج.م</span>
                        </div>
                        <div class="h-px bg-slate-200 my-3"></div>
                        <div class="flex items-center justify-between text-slate-900">
                            <span class="font-extrabold">الإجمالي</span>
                            <span class="font-extrabold">{{ number_format($grandAfterDiscount, 2) }} ج.م</span>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-2">
                        <a href="{{ route('site.checkout') }}" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                            متابعة الدفع
                        </a>
                        <a href="{{ route('site.courses') }}" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-slate-100 text-slate-900 font-extrabold hover:bg-slate-200 transition">
                            إضافة دورات أخرى
                        </a>
                    </div>

                    <div class="mt-4 text-xs text-slate-500 leading-relaxed">
                        سيتم إنشاء طلب وربط الدورات بحسابك مباشرة بعد تأكيد الطلب.
                    </div>
                </div>
            </aside>
        </div>
    </section>
@endsection
