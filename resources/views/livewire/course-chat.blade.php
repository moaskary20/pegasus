<div class="max-w-4xl mx-auto px-4 py-10" style="direction: rtl;">
    {{-- Breadcrumb --}}
    <div class="text-xs text-slate-600 mb-6">
        <a href="{{ url('/') }}" class="hover:underline">الرئيسية</a>
        <span class="mx-1">/</span>
        <a href="{{ route('site.courses') }}" class="hover:underline">الدورات</a>
        <span class="mx-1">/</span>
        <a href="{{ route('site.course.show', $this->course) }}" class="hover:underline">{{ $this->course->title }}</a>
        <span class="mx-1">/</span>
        <span class="text-slate-900 font-bold">محادثة الدورة</span>
    </div>

    <div class="rounded-3xl border bg-white overflow-hidden shadow-sm" style="min-height: 500px;">
        {{-- Header --}}
        <div class="flex items-center gap-4 px-6 py-4 bg-gradient-to-l from-[#2c004d] to-[#3d195c] text-white">
            <a href="{{ route('site.course.show', $this->course) }}" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="w-12 h-12 rounded-xl bg-white/10 overflow-hidden shrink-0 flex items-center justify-center">
                @if($this->course->cover_image)
                    <img src="{{ $this->course->cover_image }}" alt="{{ $this->course->title }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-lg font-extrabold truncate">{{ $this->course->title }}</h1>
                <p class="text-sm text-white/80">{{ $this->getParticipantsCount() }} مشارك</p>
            </div>
            <button type="button" wire:click="refreshMessages" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 transition" title="تحديث">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>

        {{-- Participants --}}
        <div class="flex items-center gap-3 px-6 py-3 bg-slate-50 border-b">
            <span class="text-xs font-bold text-slate-600">المشاركون:</span>
            <div class="flex -space-x-2">
                @foreach($this->participants->take(5) as $participant)
                    <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600 overflow-hidden" title="{{ $participant->name }}">
                        @if($participant->avatar)
                            <img src="{{ str_starts_with($participant->avatar, 'http') ? $participant->avatar : asset('storage/' . ltrim($participant->avatar, '/')) }}" alt="" class="w-full h-full object-cover">
                        @else
                            {{ mb_substr($participant->name, 0, 1) }}
                        @endif
                    </div>
                @endforeach
                @if($this->getParticipantsCount() > 5)
                    <div class="w-8 h-8 rounded-full border-2 border-white bg-[#3d195c]/20 text-[#3d195c] flex items-center justify-center text-xs font-bold">+{{ $this->getParticipantsCount() - 5 }}</div>
                @endif
            </div>
        </div>

        {{-- Messages --}}
        <div class="h-[400px] overflow-y-auto p-6 space-y-4" id="messages-container" wire:poll.5s="refreshMessages">
            @forelse($messages as $message)
                @php
                    $isOwn = $message['user_id'] === auth()->id();
                    $isInstructor = $message['user_id'] === $this->course->user_id;
                @endphp
                <div class="flex {{ $isOwn ? 'justify-start' : 'justify-end flex-row-reverse' }} gap-3 max-w-[85%] {{ $isOwn ? 'ml-0 mr-auto' : 'mr-0 ml-auto' }}">
                    @if(!$isOwn && isset($message['user']))
                        <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-sm font-bold text-slate-600 shrink-0 overflow-hidden">
                            @if($message['user']['avatar'] ?? null)
                                @php $av = $message['user']['avatar']; @endphp
                                <img src="{{ str_starts_with($av, 'http') ? $av : asset('storage/' . ltrim($av, '/')) }}" alt="" class="w-full h-full object-cover">
                            @else
                                {{ mb_substr($message['user']['name'] ?? '?', 0, 1) }}
                            @endif
                        </div>
                    @endif
                    <div class="rounded-2xl px-4 py-3 {{ $isOwn ? 'bg-[#3d195c] text-white' : ($isInstructor ? 'bg-amber-50 border border-amber-200 text-slate-800' : 'bg-slate-100 text-slate-800') }}">
                        @if(!$isOwn && isset($message['user']))
                            <p class="text-xs font-bold mb-1 {{ $isInstructor ? 'text-amber-700' : 'text-[#3d195c]' }}">
                                {{ $message['user']['name'] ?? 'مستخدم' }}
                                @if($isInstructor)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-200 text-amber-800">المدرس</span>
                                @endif
                            </p>
                        @endif
                        @if(!empty($message['attachments']))
                            @foreach($message['attachments'] as $attachment)
                                @if(str_starts_with($attachment['file_type'], 'image/'))
                                    <a href="{{ asset('storage/' . $attachment['file_path']) }}" target="_blank" class="block mb-2 rounded-xl overflow-hidden">
                                        <img src="{{ asset('storage/' . $attachment['file_path']) }}" alt="" class="max-w-full max-h-48 rounded-xl">
                                    </a>
                                @else
                                    <a href="{{ asset('storage/' . $attachment['file_path']) }}" target="_blank" class="flex items-center gap-2 py-2 px-3 rounded-lg bg-white/50 text-sm mb-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>{{ $attachment['file_name'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        @endif
                        @if($message['body'])
                            <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $message['body'] }}</p>
                        @endif
                        <p class="text-[10px] opacity-75 mt-1">{{ \Carbon\Carbon::parse($message['created_at'])->format('H:i') }}</p>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-slate-500 py-12">
                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <p class="text-sm font-bold">ابدأ المحادثة مع زملائك في الدورة</p>
                </div>
            @endforelse
        </div>

        {{-- Input --}}
        <div class="p-4 bg-slate-50 border-t">
            @if($attachment)
                <div class="flex items-center gap-2 p-3 bg-slate-200 rounded-xl mb-3">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span class="flex-1 text-sm truncate">{{ $attachment->getClientOriginalName() }}</span>
                    <button type="button" wire:click="removeAttachment" class="text-rose-600 hover:text-rose-700 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
            <form wire:submit.prevent="sendMessage" class="flex items-center gap-3">
                <label class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 cursor-pointer transition">
                    <input type="file" wire:model="attachment" class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                </label>
                <input type="text" wire:model="newMessage" placeholder="اكتب رسالة..." class="flex-1 px-4 py-3 rounded-2xl border border-slate-200 focus:border-[#3d195c] focus:ring-2 focus:ring-[#3d195c]/20 outline-none">
                <button type="submit" class="p-3 rounded-2xl bg-[#3d195c] text-white hover:bg-[#3d195c]/90 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:navigated', () => {
    const c = document.getElementById('messages-container');
    if (c) c.scrollTop = c.scrollHeight;
});
document.addEventListener('livewire:updated', () => {
    const c = document.getElementById('messages-container');
    if (c) c.scrollTop = c.scrollHeight;
});
const c = document.getElementById('messages-container');
if (c) c.scrollTop = c.scrollHeight;
</script>
