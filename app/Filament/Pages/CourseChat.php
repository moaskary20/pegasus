<?php

namespace App\Filament\Pages;

use App\Models\Conversation;
use App\Models\Course;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Notifications\NewMessageNotification;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class CourseChat extends Page
{
    use WithFileUploads;
    
    protected static ?string $navigationLabel = 'محادثة الدورة';
    
    protected static ?string $title = 'محادثة الدورة';
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;
    
    protected string $view = 'filament.pages.course-chat';
    
    protected static ?string $slug = 'course-chat/{courseId}';
    
    protected static bool $shouldRegisterNavigation = false;
    
    public ?int $courseId = null;
    public ?Course $course = null;
    public ?Conversation $conversation = null;
    public string $newMessage = '';
    public $attachment = null;
    public array $messages = [];
    
    public function mount(int $courseId): void
    {
        $this->courseId = $courseId;
        $this->course = Course::with('instructor')->findOrFail($courseId);
        
        // Check if user is enrolled or is the instructor
        $isInstructor = $this->course->user_id === auth()->id();
        $isEnrolled = $this->course->enrollments()->where('user_id', auth()->id())->exists();
        
        if (!$isInstructor && !$isEnrolled) {
            abort(403, 'يجب أن تكون مسجلاً في الدورة للوصول إلى المحادثة');
        }
        
        // Get or create the course conversation
        $this->conversation = Conversation::getOrCreateForCourse($this->course);
        
        // Ensure user is a participant
        $this->conversation->participants()->firstOrCreate(['user_id' => auth()->id()]);
        
        $this->loadMessages();
        $this->markAsRead();
    }
    
    public function getTitle(): string
    {
        return 'محادثة: ' . $this->course->title;
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
            'conversation_id' => $this->conversation->id,
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
        
        // Notify other participants (limit notifications for course chats)
        $this->notifyParticipants($message);
        
        // Reset form
        $this->newMessage = '';
        $this->attachment = null;
        
        // Reload messages
        $this->loadMessages();
    }
    
    protected function notifyParticipants(Message $message): void
    {
        // For course chats, only notify the instructor if a student sends a message
        // Or notify students if instructor sends a message (optional)
        
        if ($this->course->user_id !== auth()->id()) {
            // Student sent message - notify instructor only
            $instructor = $this->course->instructor;
            if ($instructor) {
                $participant = $this->conversation->participants()
                    ->where('user_id', $instructor->id)
                    ->where('is_muted', false)
                    ->first();
                
                if ($participant) {
                    $instructor->notify(new NewMessageNotification($message));
                }
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
    
    public function getParticipantsCount(): int
    {
        return $this->conversation->participants()->count();
    }
    
    public function getParticipantsProperty()
    {
        return $this->conversation->users()->limit(10)->get();
    }
}
