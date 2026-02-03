@extends('layouts.site')

@section('content')
    @php
        $gatewayLabel = match ((string) ($order->payment_gateway ?? '')) {
            'kashier' => 'الدفع بالفيزا والبطاقات البنكية',
            'manual' => 'تحويل/دفع يدوي',
            default => $order->payment_gateway ?: '—',
        };
    @endphp

    <section class="bg-slate-50 border-b">
        <div class="max-w-7xl mx-auto px-4 py-10" style="direction: rtl;">
            <div class="rounded-3xl border bg-white p-8">
                <div class="text-xs font-extrabold tracking-widest text-emerald-700 uppercase">SUCCESS</div>
                <h1 class="mt-3 text-2xl md:text-3xl font-extrabold text-slate-900">تم إنشاء الطلب بنجاح</h1>
                <p class="text-sm text-slate-600 mt-2">
                    رقم الطلب: <span class="font-extrabold text-slate-900">{{ $order->order_number }}</span>
                </p>

                @if($order->payment_gateway === 'manual')
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800">
                        تم استلام الإيصال وسيتم مراجعة الدفع من الإدارة قبل تفعيل الدورات.
                    </div>
                @endif

                <div class="mt-6 grid md:grid-cols-3 gap-4">
                    <div class="rounded-3xl border bg-slate-50 p-5">
                        <div class="text-xs text-slate-500">الإجمالي</div>
                        <div class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format((float) $order->total, 2) }} ج.م</div>
                    </div>
                    <div class="rounded-3xl border bg-slate-50 p-5">
                        <div class="text-xs text-slate-500">طريقة الدفع</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">{{ $gatewayLabel }}</div>
                    </div>
                    <div class="rounded-3xl border bg-slate-50 p-5">
                        <div class="text-xs text-slate-500">الحالة</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">{{ $order->status }}</div>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="text-sm font-extrabold text-slate-900">الدورات ضمن الطلب</div>
                    <div class="mt-3 divide-y border rounded-3xl overflow-hidden">
                        @foreach($order->items as $it)
                            <div class="p-4 bg-white flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-slate-900 line-clamp-1">{{ $it->course?->title ?? 'دورة' }}</div>
                                    <div class="text-xs text-slate-500 mt-1">السعر: {{ number_format((float) $it->price, 2) }} ج.م</div>
                                </div>
                                @if($it->course)
                                    <a href="{{ route('site.course.show', $it->course) }}" class="text-xs font-extrabold text-[#3d195c] hover:underline shrink-0">
                                        عرض الدورة
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-7 flex flex-col sm:flex-row gap-3">
                    <a href="{{ url('/admin/my-courses') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                        الذهاب إلى دوراتي
                    </a>
                    <a href="{{ route('site.courses') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-slate-100 text-slate-900 font-extrabold hover:bg-slate-200 transition">
                        استكشف دورات أخرى
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

