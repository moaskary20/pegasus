@extends('layouts.site')

@section('content')
@php
    $brand = '#2c004d';
@endphp
<section class="max-w-2xl mx-auto px-4 py-10" style="direction: rtl;">
    <div class="mb-8">
        <a href="{{ route('site.messages') }}" class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-[#2c004d] mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            العودة للرسائل
        </a>
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">محادثة جديدة</h1>
        <p class="text-sm text-slate-600 mt-1">ابحث عن مستخدم لبدء محادثة معه</p>
    </div>

    <form method="GET" action="{{ route('site.messages.new') }}" class="mb-6 flex gap-2">
        <input type="search" name="q" value="{{ old('q', $search) }}" placeholder="ابحث بالاسم أو البريد أو رقم الهاتف..." class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-sm focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none" autofocus>
        <button type="submit" class="px-5 py-3 rounded-xl bg-[#2c004d] text-white font-bold hover:bg-[#2c004d]/95 transition">
            بحث
        </button>
    </form>

    <div class="rounded-3xl border bg-white overflow-hidden shadow-sm">
        @if(strlen($search) >= 2)
            @forelse($users as $user)
                <form method="POST" action="{{ route('site.messages.start') }}" class="block">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <button type="submit" class="w-full flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition text-right">
                        <div class="w-12 h-12 rounded-full bg-[#2c004d]/10 flex items-center justify-center text-[#2c004d] font-bold text-lg shrink-0 overflow-hidden">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . ltrim($user->avatar, '/')) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                {{ mb_substr($user->name, 0, 1) }}
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-slate-900">{{ $user->name }}</p>
                            <p class="text-sm text-slate-500 truncate">{{ $user->email }}</p>
                        </div>
                        <span class="text-sm font-bold text-[#2c004d]">بدء المحادثة</span>
                    </button>
                </form>
            @empty
                <div class="p-12 text-center text-slate-500">
                    <p>لا توجد نتائج لـ "{{ $search }}"</p>
                </div>
            @endforelse
        @else
            <div class="p-12 text-center text-slate-500">
                <p>اكتب حرفين على الأقل للبحث</p>
            </div>
        @endif
    </div>
</section>
@endsection
