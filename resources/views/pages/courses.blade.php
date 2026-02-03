@extends('layouts.site')

@section('content')
    <section class="max-w-7xl mx-auto px-4 py-10">
        <div class="rounded-3xl border bg-white p-8">
            <h1 class="text-2xl font-extrabold text-slate-900">الدورات</h1>
            <p class="text-sm text-slate-600 mt-2">
                صفحة عامة مبدئية لعرض الدورات. حالياً يمكنك تصفح الدورات من لوحة الطالب.
            </p>
            <div class="mt-6">
                <a href="{{ url('/admin/browse-courses') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-[#2c004d] text-white font-bold hover:opacity-95 transition">
                    تصفّح الدورات الآن
                </a>
            </div>
        </div>
    </section>
@endsection

