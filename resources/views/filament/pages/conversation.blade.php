<x-filament-panels::page>
    <style>
        .chat-container {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 200px);
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        
        .chat-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .back-btn {
            padding: 8px;
            background: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            color: #6b7280;
            transition: background 0.2s;
        }
        .back-btn:hover { background: #e5e7eb; }
        
        .chat-avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 16px;
        }
        .chat-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        
        .chat-info { flex: 1; }
        .chat-name { font-size: 15px; font-weight: 600; color: #1f2937; margin: 0; }
        .chat-status { font-size: 12px; color: #6b7280; margin: 2px 0 0 0; }
        
        .refresh-btn {
            padding: 8px;
            background: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.2s;
        }
        .refresh-btn:hover { background: #e5e7eb; color: #1f2937; }
        
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .message-row {
            display: flex;
            max-width: 75%;
        }
        .message-row.own { align-self: flex-start; }
        .message-row.other { align-self: flex-end; flex-direction: row-reverse; }
        
        .message-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 600; color: #6b7280;
            flex-shrink: 0;
            margin-left: 8px;
        }
        .message-row.own .message-avatar { margin-left: 0; margin-right: 8px; }
        .message-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        
        .message-bubble {
            padding: 12px 16px;
            border-radius: 18px;
            max-width: 100%;
        }
        .message-row.own .message-bubble {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border-bottom-left-radius: 4px;
        }
        .message-row.other .message-bubble {
            background: #f3f4f6;
            color: #1f2937;
            border-bottom-right-radius: 4px;
        }
        
        .message-sender {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
            color: rgba(255,255,255,0.8);
        }
        .message-row.other .message-sender { color: #6366f1; }
        
        .message-text {
            font-size: 14px;
            line-height: 1.5;
            word-wrap: break-word;
            white-space: pre-wrap;
            margin: 0;
        }
        
        .message-time {
            font-size: 10px;
            margin-top: 6px;
            opacity: 0.6;
        }
        
        .message-attachment {
            display: block;
            margin-bottom: 8px;
            border-radius: 10px;
            overflow: hidden;
        }
        .message-attachment img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
        }
        .message-file {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            color: inherit;
            text-decoration: none;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .message-row.other .message-file { background: rgba(0,0,0,0.05); }
        
        .empty-messages {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .empty-messages-content { text-align: center; }
        .empty-messages-icon {
            width: 70px; height: 70px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
        }
        .empty-messages-text { font-size: 14px; color: #6b7280; margin: 0; }
        
        .chat-input-area {
            padding: 14px 18px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        .attachment-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #e5e7eb;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 13px;
            color: #4b5563;
        }
        .attachment-preview span { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .remove-attachment {
            background: none; border: none;
            color: #ef4444; cursor: pointer;
            padding: 4px;
        }
        
        .input-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .attach-btn {
            padding: 10px;
            background: none;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            color: #6b7280;
            transition: background 0.2s;
        }
        .attach-btn:hover { background: #e5e7eb; }
        
        .message-input {
            flex: 1;
            padding: 12px 18px;
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            font-size: 14px;
            outline: none;
            transition: border 0.2s;
        }
        .message-input:focus { border-color: #6366f1; }
        
        .send-btn {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: transform 0.2s;
        }
        .send-btn:hover { transform: scale(1.05); }
        
        @media (prefers-color-scheme: dark) {
            .chat-container { background: #1f2937; border-color: #374151; }
            .chat-header { background: #111827; border-color: #374151; }
            .back-btn, .refresh-btn { color: #9ca3af; }
            .back-btn:hover, .refresh-btn:hover { background: #374151; color: white; }
            .chat-name { color: white; }
            .message-row.other .message-bubble { background: #374151; color: white; }
            .message-row.other .message-sender { color: #a5b4fc; }
            .empty-messages-icon { background: #374151; }
            .chat-input-area { background: #111827; border-color: #374151; }
            .attachment-preview { background: #374151; color: #d1d5db; }
            .message-input { background: #374151; border-color: #4b5563; color: white; }
            .message-avatar { background: #374151; color: #9ca3af; }
        }
    </style>

    <div class="chat-container">
        {{-- Header --}}
        <div class="chat-header">
            <a href="{{ route('filament.admin.pages.messages') }}" class="back-btn">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            
            @php
                $avatar = $this->getConversationAvatar();
                $name = $this->getConversationName();
            @endphp
            
            <div class="chat-avatar">
                @if($avatar)
                    <img src="{{ Storage::url($avatar) }}" alt="{{ $name }}">
                @elseif($conversation->type === 'private')
                    {{ mb_substr($name, 0, 1) }}
                @else
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                @endif
            </div>
            
            <div class="chat-info">
                <p class="chat-name">{{ $name }}</p>
                <p class="chat-status">
                    @if($conversation->type !== 'private')
                        {{ $this->getParticipantsCount() }} مشارك
                    @else
                        محادثة خاصة
                    @endif
                </p>
            </div>
            
            <button class="refresh-btn" wire:click="refreshMessages" title="تحديث">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>
        
        {{-- Messages --}}
        <div class="messages-area" id="messages-container" wire:poll.5s="refreshMessages">
            @forelse($messages as $message)
                @php
                    $isOwn = $message['user_id'] === auth()->id();
                @endphp
                
                <div class="message-row {{ $isOwn ? 'own' : 'other' }}">
                    @if(!$isOwn && isset($message['user']))
                        <div class="message-avatar">
                            @if($message['user']['avatar'] ?? null)
                                <img src="{{ Storage::url($message['user']['avatar']) }}" alt="">
                            @else
                                {{ mb_substr($message['user']['name'] ?? '?', 0, 1) }}
                            @endif
                        </div>
                    @endif
                    
                    <div class="message-bubble">
                        @if(!$isOwn && $conversation->type !== 'private' && isset($message['user']))
                            <p class="message-sender">{{ $message['user']['name'] ?? 'مستخدم' }}</p>
                        @endif
                        
                        @if(!empty($message['attachments']))
                            @foreach($message['attachments'] as $attachment)
                                @if(str_starts_with($attachment['file_type'], 'image/'))
                                    <a href="{{ Storage::url($attachment['file_path']) }}" target="_blank" class="message-attachment">
                                        <img src="{{ Storage::url($attachment['file_path']) }}" alt="{{ $attachment['file_name'] }}">
                                    </a>
                                @else
                                    <a href="{{ Storage::url($attachment['file_path']) }}" target="_blank" class="message-file">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>{{ $attachment['file_name'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        @endif
                        
                        @if($message['body'])
                            <p class="message-text">{{ $message['body'] }}</p>
                        @endif
                        
                        <p class="message-time">{{ \Carbon\Carbon::parse($message['created_at'])->format('h:i A') }}</p>
                    </div>
                </div>
            @empty
                <div class="empty-messages">
                    <div class="empty-messages-content">
                        <div class="empty-messages-icon">
                            <svg width="28" height="28" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <p class="empty-messages-text">ابدأ المحادثة بإرسال رسالة</p>
                    </div>
                </div>
            @endforelse
        </div>
        
        {{-- Input Area --}}
        <div class="chat-input-area">
            @if($attachment)
                <div class="attachment-preview">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    <span>{{ $attachment->getClientOriginalName() }}</span>
                    <button class="remove-attachment" wire:click="removeAttachment">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif
            
            <form wire:submit.prevent="sendMessage" class="input-row">
                <label class="attach-btn">
                    <input type="file" wire:model="attachment" style="display: none;" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                </label>
                
                <input 
                    type="text" 
                    class="message-input"
                    wire:model="newMessage" 
                    placeholder="اكتب رسالة..."
                >
                
                <button type="submit" class="send-btn">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function scrollToBottom() {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
        
        document.addEventListener('livewire:navigated', scrollToBottom);
        document.addEventListener('livewire:updated', scrollToBottom);
        scrollToBottom();
    </script>
</x-filament-panels::page>
