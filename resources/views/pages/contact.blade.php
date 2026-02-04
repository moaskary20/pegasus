@extends('layouts.site')

@section('content')
    <section class="max-w-7xl mx-auto px-4 py-10" style="direction: rtl;">
        <div class="rounded-3xl border bg-white p-8">
            <h1 class="text-2xl font-extrabold text-slate-900">الاتصال بنا</h1>
            <p class="text-sm text-slate-600 mt-2">
                للاستفسارات والدعم الفني، يرجى التواصل معنا عبر المساعدة والدعم أو البريد الإلكتروني.
            </p>
            <div class="mt-6">
                <a href="{{ route('site.support') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#3d195c] text-white font-extrabold hover:bg-[#3d195c]/95 transition">
                    المساعدة والدعم
                </a>
            </div>
        </div>
    </section>
@endsection
