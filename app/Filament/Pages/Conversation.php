<?php

namespace App\Filament\Pages;

use App\Models\Conversation as ConversationModel;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Notifications\NewMessageNotification;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class Conversation extends Page
{
    use WithFileUploads;
    
    protected static ?string $navigationLabel = 'المحادثة';
    
    protected static ?string $title = 'المحادثة';
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;
    
    protected string $view = 'filament.pages.conversation';
    
    protected static ?string $slug = 'conversation/{id}';
    
    protected static bool $shouldRegisterNavigation = false;
    
    public ?int $conversationId = null;
    public ?ConversationModel $conversation = null;
    public string $newMessage = '';
    public $attachment = null;
    public array $messages = [];
    
    public function mount(int $id): void
    {
        $this->conversationId = $id;
        $this->conversation = ConversationModel::with(['users', 'participants'])
            ->findOrFail($id);
        
        // Check if user is a participant
        if (!$this->conversation->hasParticipant(auth()->id())) {
            abort(403);
        }
        
        $this->loadMessages();
        $this->markAsRead();
    }
    
    public function getTitle(): string
    {
        return $this->getConversationName();
    }
    
    public function loadMessages(): void
    {
        $this->messages = $this->conversation
            ->messages()
            ->with(['user', 'attachments'])
            ->latest()
            ->limit(100)
            ->get()
            ->reverse()
            ->values()
            ->toArray();
    }
    
    public function markAsRead(): void
    {
        $participant = $this->conversation->participants()
            ->where('user_id', auth()->id())
            ->first();
        
        if ($participant) {
            $participant->markAsRead();
        }
    }
    
    public function sendMessage(): void
    {
        if (empty(trim($this->newMessage)) && !$this->attachment) {
            return;
        }
        
        $messageData = [
            'conversation_id' => $this->conversationId,
            'user_id' => auth()->id(),
            'body' => trim($this->newMessage),
            'type' => Message::TYPE_TEXT,
        ];
        
        // Handle attachment
        if ($this->attachment) {
            $messageData['type'] = str_starts_with($this->attachment->getMimeType(), 'image/')
                ? Message::TYPE_IMAGE
                : Message::TYPE_FILE;
        }
        
        $message = Message::create($messageData);
        
        // Save attachment
        if ($this->attachment) {
            $path = $this->attachment->store('message-attachments', 'public');
            
            MessageAttachment::create([
                'message_id' => $message->id,
                'file_name' => $this->attachment->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $this->attachment->getMimeType(),
                'file_size' => $this->attachment->getSize(),
            ]);
        }
        
        // Notify other participants
        $this->notifyParticipants($message);
        
        // Reset form
        $this->newMessage = '';
        $this->attachment = null;
        
        // Reload messages
        $this->loadMessages();
    }
    
    protected function notifyParticipants(Message $message): void
    {
        $participants = $this->conversation->participants()
            ->where('user_id', '!=', auth()->id())
            ->where('is_muted', false)
            ->with('user')
            ->get();
        
        foreach ($participants as $participant) {
            if ($participant->user) {
                $participant->user->notify(new NewMessageNotification($message));
            }
        }
    }
    
    public function removeAttachment(): void
    {
        $this->attachment = null;
    }
    
    public function refreshMessages(): void
    {
        $this->loadMessages();
        $this->markAsRead();
    }
    
    public function getConversationName(): string
    {
        if ($this->conversation->type === ConversationModel::TYPE_PRIVATE) {
            $other = $this->conversation->getOtherParticipant(auth()->id());
            return $other?->name ?? 'محادثة';
        }
        
        return $this->conversation->name ?? 'مجموعة';
    }
    
    public function getConversationAvatar(): ?string
    {
        if ($this->conversation->type === ConversationModel::TYPE_PRIVATE) {
            $other = $this->conversation->getOtherParticipant(auth()->id());
            return $other?->avatar;
        }
        
        return null;
    }
    
    public function getParticipantsCount(): int
    {
        return $this->conversation->participants()->count();
    }
}
