@extends('layouts.site')

@section('content')
    @php
        $price = (float) ($course->offer_price ?? $course->price ?? 0);
        $isFree = $price <= 0;
        $notice = session('notice');
    @endphp

    <section class="bg-slate-50 border-b">
        <div class="max-w-7xl mx-auto px-4 py-8" style="direction: rtl;">
            <div class="text-xs text-slate-500">
                <a href="{{ url('/') }}" class="hover:underline">الرئيسية</a>
                <span class="mx-1">/</span>
                <a href="{{ route('site.courses') }}" class="hover:underline">الدورات</a>
                <span class="mx-1">/</span>
                <a href="{{ route('site.course.show', $course) }}" class="hover:underline">تفاصيل الدورة</a>
                <span class="mx-1">/</span>
                <span class="text-slate-700 font-bold">طرق الاشتراك</span>
            </div>

            <div class="mt-3 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">اختر طريقة الاشتراك</h1>
                    <p class="text-sm text-slate-600 mt-2">
                        {{ $course->title }}
                    </p>
                </div>
                <div class="rounded-3xl border bg-white px-5 py-4">
                    <div class="text-xs text-slate-500">سعر الدورة</div>
                    <div class="text-xl font-extrabold text-slate-900 mt-1">
                        {{ $isFree ? 'مجاني' : number_format($price, 2) . ' ج.م' }}
                    </div>
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
                <div class="grid md:grid-cols-2 gap-4">
                    {{-- Method 1: Add to cart --}}
                    <div class="rounded-3xl border bg-white p-6 hover:shadow-sm transition">
                        <div class="text-xs font-extrabold tracking-widest text-slate-500 uppercase">METHOD</div>
                        <div class="mt-2 text-lg font-extrabold text-slate-900">
                            {{ $isFree ? 'تسجيل مجاني عبر السلة' : 'شراء الدورة' }}
                        </div>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                            {{ $isFree
                                ? 'سيتم إضافة الدورة للسلة ثم يمكنك إتمام الاشتراك مباشرة.'
                                : 'أضف الدورة للسلة ثم أكمل خطوات الشراء.' }}
                        </p>

                        <form method="POST" action="{{ route('site.course.subscribe.add_to_cart', $course) }}" class="mt-5">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                                المتابعة إلى السلة
                            </button>
                        </form>
                    </div>

                    {{-- Method 2: Coupon (like admin cart) --}}
                    @if(!$isFree)
                        <div class="rounded-3xl border bg-white p-6 hover:shadow-sm transition">
                            <div class="text-xs font-extrabold tracking-widest text-slate-500 uppercase">COUPON</div>
                            <div class="mt-2 text-lg font-extrabold text-slate-900">شراء بكود كوبون</div>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                                أدخل كود الكوبون (مثل لوحة التحكم) وسيتم تطبيقه في صفحة السلة.
                            </p>

                            <form method="POST" action="{{ route('site.course.subscribe.coupon', $course) }}" class="mt-5 space-y-3">
                                @csrf
                                <input
                                    name="coupon_code"
                                    placeholder="أدخل كود الكوبون"
                                    class="w-full rounded-2xl border-slate-200 focus:border-[#3d195c] focus:ring-[#3d195c]"
                                />
                                <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-slate-900 text-white font-extrabold hover:bg-slate-900/95 transition">
                                    تطبيق والذهاب للسلة
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="mt-6 rounded-3xl border bg-white p-6">
                    <div class="text-sm font-extrabold text-slate-900">ملاحظة</div>
                    <div class="mt-2 text-sm text-slate-600 leading-relaxed">
                        ستُعرض نفس فكرة الشراء الموجودة في لوحة التحكم: إضافة للسلة ثم تطبيق الكوبون (إن وجد) ثم إتمام العملية.
                    </div>
                </div>
            </div>

            {{-- Right: summary --}}
            <aside class="lg:col-span-4 xl:col-span-3 lg:col-start-10">
                <div class="rounded-3xl border bg-white p-6">
                    <div class="text-sm font-extrabold text-slate-900">معلومات سريعة</div>
                    <div class="mt-4 space-y-2 text-sm text-slate-700">
                        <div class="flex items-center justify-between">
                            <span>المدرب</span>
                            <span class="font-extrabold">{{ $course->instructor?->name ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>التصنيف</span>
                            <span class="font-extrabold">{{ $course->subCategory?->name ?? $course->category?->name ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-2">
                        <a href="{{ route('site.course.show', $course) }}" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-slate-100 text-slate-900 font-extrabold hover:bg-slate-200 transition">
                            العودة لتفاصيل الدورة
                        </a>
                        <a href="{{ route('site.cart') }}" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white border font-extrabold hover:bg-slate-50 transition">
                            فتح السلة
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </section>
@endsection

