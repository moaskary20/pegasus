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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                الرسائل
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">الرسائل</h1>
            <p class="text-sm text-slate-600 mt-1">{{ $conversations->total() }} محادثة</p>
        </div>
        <a href="{{ route('site.messages.new') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#2c004d] text-white text-sm font-bold hover:bg-[#2c004d]/90 transition shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            محادثة جديدة
        </a>
    </div>

    @if(session('notice'))
        <div class="mb-6 rounded-2xl border px-4 py-3 text-sm font-semibold {{ (session('notice')['type'] ?? '') === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}">
            {{ session('notice')['message'] ?? '' }}
        </div>
    @endif

    {{-- Filters & Search --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <div class="flex rounded-xl bg-slate-100 p-1">
            <a href="{{ route('site.messages', ['filter' => 'all', 'search' => $search]) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filter === 'all' ? 'bg-white text-[#2c004d] shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">الكل</a>
            <a href="{{ route('site.messages', ['filter' => 'unread', 'search' => $search]) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filter === 'unread' ? 'bg-white text-[#2c004d] shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">غير مقروء</a>
            <a href="{{ route('site.messages', ['filter' => 'groups', 'search' => $search]) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filter === 'groups' ? 'bg-white text-[#2c004d] shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">المجموعات</a>
        </div>
        <form method="GET" action="{{ route('site.messages') }}" class="flex-1 min-w-[200px]">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="search" name="search" value="{{ $search }}" placeholder="بحث في المحادثات..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none">
        </form>
    </div>

    {{-- Conversations List --}}
    <div class="rounded-3xl border bg-white overflow-hidden shadow-sm">
        @forelse($conversations as $conversation)
            @php
                $unreadCount = $conversation->getUnreadCountFor(auth()->id());
                $name = \App\Http\Controllers\Site\MessagesController::getConversationName($conversation);
                $avatar = \App\Http\Controllers\Site\MessagesController::getConversationAvatar($conversation);
                $lastMessage = $conversation->latestMessage;
            @endphp
            <a href="{{ route('site.messages.show', $conversation->id) }}" class="flex items-center gap-4 px-5 py-4 border-b border-slate-100 hover:bg-slate-50 transition {{ $unreadCount > 0 ? 'bg-[#2c004d]/5' : '' }}">
                <div class="relative w-12 h-12 rounded-full bg-[#2c004d]/10 flex items-center justify-center text-[#2c004d] font-bold text-lg shrink-0 overflow-hidden">
                    @if($avatar)
                        <img src="{{ $avatar }}" alt="{{ $name }}" class="w-full h-full object-cover">
                    @elseif($conversation->type === 'private')
                        {{ mb_substr($name, 0, 1) }}
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    @endif
                    @if($unreadCount > 0)
                        <span class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 rounded-full bg-rose-500 text-white text-xs font-bold flex items-center justify-center">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-bold text-slate-900 truncate">{{ $name }}</p>
                        @if($lastMessage)
                            <span class="text-xs text-slate-500 shrink-0">{{ $lastMessage->created_at->diffForHumans() }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-600 truncate mt-0.5">
                        @if($lastMessage)
                            @if($lastMessage->user_id === auth()->id())
                                <span class="text-[#2c004d] font-medium">أنت:</span>
                            @else
                                <span>{{ $lastMessage->user?->name }}:</span>
                            @endif
                            {{ Str::limit($lastMessage->body ?? 'مرفق', 40) }}
                        @else
                            <span class="italic">لا توجد رسائل</span>
                        @endif
                    </p>
                </div>
                @if($conversation->type !== 'private')
                    <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-[#2c004d]/10 text-[#2c004d] shrink-0">{{ $conversation->type === 'course' ? 'دورة' : 'مجموعة' }}</span>
                @endif
            </a>
        @empty
            <div class="p-16 text-center">
                <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-900">لا توجد محادثات</p>
                <p class="text-sm text-slate-600 mt-1">ابدأ محادثة جديدة للتواصل</p>
                <a href="{{ route('site.messages.new') }}" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-xl bg-[#2c004d] text-white text-sm font-bold hover:bg-[#2c004d]/90 transition">
                    محادثة جديدة
                </a>
            </div>
        @endforelse
    </div>

    @if($conversations->hasPages())
        <div class="mt-6">{{ $conversations->links() }}</div>
    @endif
</section>
@endsection
