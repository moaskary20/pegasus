@extends('layouts.site')

@section('content')
@php
    $brand = '#2c004d';
@endphp
<section class="max-w-4xl mx-auto px-4 py-10" style="direction: rtl;">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#2c004d]/10 text-[#2c004d] text-sm font-bold mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                الإشعارات
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">الإشعارات</h1>
            <p class="text-sm text-slate-600 mt-1">
                @if($unreadCount > 0)
                    لديك {{ $unreadCount }} إشعار غير مقروء.
                @else
                    لا توجد إشعارات جديدة.
                @endif
            </p>
        </div>
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('site.notifications.read-all') }}" class="shrink-0">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#2c004d] text-white text-sm font-bold hover:bg-[#2c004d]/90 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                تعليم الكل كمقروء
            </button>
        </form>
        @endif
    </div>

    @if(session('notice'))
        <div class="mb-6 rounded-2xl border px-4 py-3 text-sm font-semibold {{ session('notice.type') === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
            {{ session('notice.message') }}
        </div>
    @endif

    <div class="rounded-3xl border bg-white overflow-hidden shadow-sm">
        @if($notifications->isEmpty())
            <div class="p-12 text-center">
                <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h3 class="text-lg font-extrabold text-slate-800">لا توجد إشعارات</h3>
                <p class="mt-2 text-sm text-slate-600">ستظهر إشعاراتك هنا عند وصولها.</p>
            </div>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($notifications as $notification)
                    @php
                        $data = is_array($notification->data) ? $notification->data : (is_string($notification->data) ? json_decode($notification->data, true) : []);
                        $title = $data['title'] ?? 'إشعار';
                        $message = $data['message'] ?? '';
                        $isRead = $notification->read_at !== null;
                    @endphp
                    <li class="{{ $isRead ? 'bg-white' : 'bg-sky-50/50' }} hover:bg-slate-50/50 transition">
                        <div class="flex items-start gap-4 p-4 sm:p-5">
                            <div class="w-10 h-10 shrink-0 rounded-full flex items-center justify-center {{ $isRead ? 'bg-slate-100 text-slate-500' : 'bg-[#2c004d]/10 text-[#2c004d]' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <h3 class="font-extrabold text-slate-900 {{ $isRead ? '' : 'text-[#2c004d]' }}">{{ $title }}</h3>
                                    <span class="text-xs text-slate-500 shrink-0">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                                @if($message)
                                    <p class="mt-1 text-sm text-slate-600 leading-relaxed">{{ $message }}</p>
                                @endif
                                @if(!$isRead)
                                <form method="POST" action="{{ route('site.notifications.read-one', $notification->id) }}" class="mt-3 inline-block">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-[#2c004d] hover:underline">تعليم كمقروء</button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="px-4 py-3 bg-slate-50 border-t">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
