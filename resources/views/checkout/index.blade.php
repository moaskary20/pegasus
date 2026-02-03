@extends('layouts.site')

@section('content')
    @php
        $notice = session('notice');
    @endphp

    <section class="bg-slate-50 border-b">
        <div class="max-w-7xl mx-auto px-4 py-8" style="direction: rtl;">
            <div class="text-xs text-slate-500">
                <a href="{{ url('/') }}" class="hover:underline">الرئيسية</a>
                <span class="mx-1">/</span>
                <a href="{{ route('site.cart') }}" class="hover:underline">السلة</a>
                <span class="mx-1">/</span>
                <span class="text-slate-700 font-bold">الدفع</span>
            </div>

            <div class="mt-3 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">إتمام الدفع</h1>
                    <p class="text-sm text-slate-600 mt-2">اختر طريقة الدفع ثم أكد الطلب.</p>
                </div>
                <div class="rounded-3xl border bg-white px-5 py-4">
                    <div class="text-xs text-slate-500">الإجمالي</div>
                    <div class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format((float) $total, 2) }} ج.م</div>
                    @if((float) $discount > 0)
                        <div class="text-xs text-emerald-700 font-bold mt-1">خصم: {{ number_format((float) $discount, 2) }} ج.م</div>
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
                <div class="rounded-3xl border bg-white overflow-hidden">
                    <div class="px-6 py-5 border-b bg-slate-50">
                        <div class="text-lg font-extrabold text-slate-900">محتويات الطلب</div>
                        <div class="text-xs text-slate-600 mt-1">{{ number_format((int) $courseCart->count()) }} دورة</div>
                    </div>
                    <div class="divide-y">
                        @foreach($courseCart as $c)
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
                                    <div class="text-xs text-slate-600 mt-1">{{ $c->instructor?->name }}</div>
                                </div>
                                <div class="text-sm font-extrabold text-slate-900 shrink-0">
                                    @php $p = (float) ($c->offer_price ?? $c->price ?? 0); @endphp
                                    {{ $p > 0 ? number_format($p, 2) . ' ج.م' : 'مجاني' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <aside class="lg:col-span-4 xl:col-span-3 lg:col-start-10">
                <form
                    method="POST"
                    action="{{ route('site.checkout.process') }}"
                    enctype="multipart/form-data"
                    class="rounded-3xl border bg-white p-6"
                    x-data="{
                        gateway: 'kashier',
                        manualOpen: false,
                        receiptName: '',
                        requireReceipt() {
                            if (this.gateway !== 'manual') return true;
                            const input = this.$refs.receipt;
                            if (!input || !input.files || !input.files.length) {
                                this.manualOpen = true;
                                return false;
                            }
                            return true;
                        },
                        closeManual() {
                            const input = this.$refs.receipt;
                            if (!input || !input.files || !input.files.length) {
                                this.gateway = 'kashier';
                            }
                            this.manualOpen = false;
                        },
                    }"
                    @submit.prevent="if (requireReceipt()) $el.submit()"
                >
                    @csrf
                    <div class="text-sm font-extrabold text-slate-900">طريقة الدفع</div>
                    <div class="mt-4 space-y-2 text-sm">
                        @php
                            $methods = [
                                'kashier' => 'الدفع بالفيزا والبطاقات البنكية',
                                'manual' => 'تحويل/دفع يدوي (مؤقت)',
                            ];
                        @endphp
                        @foreach($methods as $val => $label)
                            <label class="flex items-center justify-between gap-3 cursor-pointer rounded-2xl border px-4 py-3 hover:bg-slate-50 transition">
                                <span class="font-bold text-slate-800">
                                    {{ $label }}
                                    @if($val === 'kashier')
                                        <span class="inline-flex items-center gap-2 ms-2">
                                            <span class="inline-flex items-center justify-center h-6 px-2 rounded-lg border bg-white text-[11px] font-extrabold text-[#1434cb]">VISA</span>
                                            <span class="inline-flex items-center justify-center h-6 px-2 rounded-lg border bg-white text-[11px] font-extrabold text-[#eb001b]">MasterCard</span>
                                            <span class="inline-flex items-center justify-center h-6 px-2 rounded-lg border bg-white text-[11px] font-extrabold text-[#3d195c]">ميزة</span>
                                        </span>
                                    @endif
                                </span>
                                <input
                                    type="radio"
                                    name="payment_gateway"
                                    value="{{ $val }}"
                                    class="accent-[#3d195c]"
                                    x-model="gateway"
                                    @change="if (gateway === 'manual') manualOpen = true"
                                    @checked($loop->first)
                                >
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-6 rounded-3xl border bg-slate-50 p-4 text-sm text-slate-700">
                        <div class="flex items-center justify-between">
                            <span>الإجمالي الفرعي</span>
                            <span class="font-extrabold">{{ number_format((float) $subtotal, 2) }} ج.م</span>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span>الخصم</span>
                            <span class="font-extrabold">{{ number_format((float) $discount, 2) }} ج.م</span>
                        </div>
                        <div class="h-px bg-slate-200 my-3"></div>
                        <div class="flex items-center justify-between text-slate-900">
                            <span class="font-extrabold">الإجمالي</span>
                            <span class="font-extrabold">{{ number_format((float) $total, 2) }} ج.م</span>
                        </div>
                        @if(!empty($couponCode))
                            <div class="text-xs text-slate-600 mt-2">الكوبون: <span class="font-bold">{{ $couponCode }}</span></div>
                        @endif
                    </div>

                    <button type="submit" class="mt-5 w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                        تأكيد الطلب
                    </button>

                    <a href="{{ route('site.cart') }}" class="mt-2 w-full inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-slate-100 text-slate-900 font-extrabold hover:bg-slate-200 transition">
                        العودة للسلة
                    </a>

                    {{-- Manual payment popup --}}
                    <div x-show="manualOpen" x-cloak class="fixed inset-0 z-[80]" aria-modal="true" role="dialog">
                        <div class="absolute inset-0 bg-black/40" @click="closeManual()"></div>
                        <div class="absolute inset-0 flex items-center justify-center p-4">
                            <div class="w-full max-w-lg rounded-3xl bg-white border shadow-2xl overflow-hidden">
                                <div class="px-6 py-4 bg-slate-50 border-b flex items-center justify-between">
                                    <div class="text-sm font-extrabold text-slate-900">إرفاق إيصال الدفع اليدوي</div>
                                    <button type="button" class="text-slate-500 hover:text-slate-900" @click="closeManual()" aria-label="إغلاق">
                                        ✕
                                    </button>
                                </div>
                                <div class="p-6">
                                    <div class="text-sm text-slate-700 leading-relaxed">
                                        يرجى رفع صورة/ملف إيصال الدفع (JPG/PNG/PDF). سيتم إرسال الطلب للوحة التحكم لمراجعة الإيصال.
                                    </div>

                                    <div class="mt-4">
                                        <input
                                            x-ref="receipt"
                                            type="file"
                                            name="manual_receipt"
                                            accept="image/*,application/pdf"
                                            class="block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-2xl file:border-0 file:text-sm file:font-extrabold file:bg-[#3d195c] file:text-white hover:file:bg-[#3d195c]/95"
                                            @change="receiptName = $event.target.files?.[0]?.name || ''"
                                        >
                                        <div class="mt-2 text-xs text-slate-500" x-show="receiptName" x-text="receiptName"></div>
                                    </div>

                                    <div class="mt-6 grid sm:grid-cols-2 gap-2">
                                        <button type="button" class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-slate-100 text-slate-900 font-extrabold hover:bg-slate-200 transition" @click="closeManual()">
                                            إلغاء
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition"
                                            @click="if (requireReceipt()) { manualOpen = false; $root.submit(); }"
                                        >
                                            حفظ الإيصال وإرسال الطلب
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </aside>
        </div>
    </section>
@endsection

