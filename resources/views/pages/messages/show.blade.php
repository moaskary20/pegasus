@extends('layouts.site')

@section('content')
@php
    $brand = '#2c004d';
    $name = \App\Http\Controllers\Site\MessagesController::getConversationName($conversation);
    $avatar = \App\Http\Controllers\Site\MessagesController::getConversationAvatar($conversation);
@endphp
<section class="max-w-4xl mx-auto px-4 py-6" style="direction: rtl;">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('site.messages') }}" class="p-2 rounded-xl hover:bg-slate-100 transition text-slate-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="w-12 h-12 rounded-full bg-[#2c004d]/10 flex items-center justify-center text-[#2c004d] font-bold text-lg shrink-0 overflow-hidden">
            @if($avatar)
                <img src="{{ $avatar }}" alt="{{ $name }}" class="w-full h-full object-cover">
            @elseif($conversation->type === 'private')
                {{ mb_substr($name, 0, 1) }}
            @else
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-extrabold text-slate-900">{{ $name }}</h1>
            <p class="text-xs text-slate-500">
                @if($conversation->type !== 'private')
                    {{ $conversation->participants()->count() }} مشارك
                @else
                    محادثة خاصة
                @endif
            </p>
        </div>
    </div>

    @if(session('notice'))
        <div class="mb-4 rounded-2xl border px-4 py-3 text-sm font-semibold {{ (session('notice')['type'] ?? '') === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}">
            {{ session('notice')['message'] ?? '' }}
        </div>
    @endif

    {{-- Messages --}}
    <div class="rounded-3xl border bg-white overflow-hidden shadow-sm flex flex-col" style="min-height: 400px;">
        <div id="messages-area" class="flex-1 overflow-y-auto p-6 space-y-4 max-h-[50vh]">
            @forelse($messages as $message)
                @php
                    $isOwn = $message->user_id === auth()->id();
                @endphp
                <div class="flex {{ $isOwn ? 'justify-start' : 'justify-end' }}">
                    <div class="flex gap-2 max-w-[85%] {{ $isOwn ? '' : 'flex-row-reverse' }}">
                        @if(!$isOwn && $conversation->type !== 'private')
                            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600 shrink-0 overflow-hidden">
                                @if($message->user?->avatar)
                                    <img src="{{ $message->user->avatar_url }}" class="w-full h-full object-cover">
                                @else
                                    {{ mb_substr($message->user?->name ?? '?', 0, 1) }}
                                @endif
                            </div>
                        @endif
                        <div class="rounded-2xl px-4 py-3 {{ $isOwn ? 'bg-[#2c004d] text-white rounded-br-md' : 'bg-slate-100 text-slate-900 rounded-bl-md' }}">
                            @if(!$isOwn && $conversation->type !== 'private')
                                <p class="text-xs font-bold text-[#2c004d] mb-1">{{ $message->user?->name ?? 'مستخدم' }}</p>
                            @endif
                            @if($message->attachments->count() > 0)
                                @foreach($message->attachments as $att)
                                    @if(str_starts_with($att->file_type ?? '', 'image/'))
                                        <a href="{{ asset('storage/' . ltrim($att->file_path, '/')) }}" target="_blank" class="block mb-2">
                                            <img src="{{ asset('storage/' . ltrim($att->file_path, '/')) }}" alt="" class="max-w-full max-h-48 rounded-lg">
                                        </a>
                                    @else
                                        <a href="{{ asset('storage/' . ltrim($att->file_path, '/')) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/20 mb-2 text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            {{ $att->file_name }}
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                            @if($message->body)
                                <p class="text-sm whitespace-pre-wrap">{{ $message->body }}</p>
                            @endif
                            <p class="text-xs opacity-75 mt-1">{{ $message->created_at->format('h:i A') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-slate-500">
                    <p>ابدأ المحادثة بإرسال رسالة</p>
                </div>
            @endforelse
        </div>
        <div id="messages-bottom"></div>

        {{-- Send Form --}}
        <div class="p-4 border-t bg-slate-50">
            <form action="{{ route('site.messages.send', $conversation->id) }}" method="POST" enctype="multipart/form-data" class="flex gap-2">
                @csrf
                <label class="p-2.5 rounded-xl bg-white border border-slate-200 hover:border-[#2c004d]/30 cursor-pointer transition shrink-0">
                    <input type="file" name="attachment" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" class="hidden" onchange="this.form.submit()">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                </label>
                <input type="text" name="body" placeholder="اكتب رسالة..." class="flex-1 px-4 py-3 rounded-xl border border-slate-200 focus:border-[#2c004d] focus:ring-2 focus:ring-[#2c004d]/20 outline-none text-sm" autocomplete="off">
                <button type="submit" class="px-5 py-3 rounded-xl bg-[#2c004d] text-white font-bold hover:bg-[#2c004d]/90 transition shrink-0">
                    إرسال
                </button>
            </form>
        </div>
    </div>

</section>
@endsection
