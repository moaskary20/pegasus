<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Course;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Notifications\NewMessageNotification;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CourseChat extends Component
{
    use WithFileUploads;

    public int $courseId;
    public int $conversationId;
    public string $newMessage = '';
    public $attachment = null;
    public array $messages = [];

    public function mount(Course $course): void
    {
        $isInstructor = $course->user_id === auth()->id();
        $isEnrolled = $course->enrollments()->where('user_id', auth()->id())->exists();

        if (!$isInstructor && !$isEnrolled) {
            abort(403, 'يجب أن تكون مسجلاً في الدورة للوصول إلى المحادثة');
        }

        $conversation = Conversation::getOrCreateForCourse($course);
        $conversation->participants()->firstOrCreate(['user_id' => auth()->id()]);

        $this->courseId = $course->id;
        $this->conversationId = $conversation->id;

        $this->loadMessages();
        $this->markAsRead();
    }

    public function getCourseProperty(): Course
    {
        return Course::with('instructor')->findOrFail($this->courseId);
    }

    public function getConversationProperty(): Conversation
    {
        return Conversation::findOrFail($this->conversationId);
    }

    public function loadMessages(): void
    {
        $conversation = $this->conversation;

        $messages = $conversation
            ->messages()
            ->with(['user:id,name,avatar,email', 'attachments'])
            ->latest()
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        $this->messages = $messages
            ->map(fn ($m) => [
                'id' => $m->id,
                'conversation_id' => $m->conversation_id,
                'user_id' => $m->user_id,
                'body' => $m->body,
                'type' => $m->type,
                'created_at' => $m->created_at->toIso8601String(),
                'user' => $m->user ? [
                    'id' => $m->user->id,
                    'name' => $m->user->name,
                    'avatar' => $m->user->avatar,
                    'email' => $m->user->email,
                ] : null,
                'attachments' => $m->attachments->map(fn ($a) => [
                    'id' => $a->id,
                    'file_name' => $a->file_name,
                    'file_path' => $a->file_path,
                    'file_type' => $a->file_type,
                ])->toArray(),
            ])
            ->values()
            ->all();
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

        $conversation = $this->conversation;
        $course = $this->course;

        $messageData = [
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'body' => trim($this->newMessage),
            'type' => Message::TYPE_TEXT,
        ];

        if ($this->attachment) {
            $messageData['type'] = str_starts_with($this->attachment->getMimeType(), 'image/')
                ? Message::TYPE_IMAGE
                : Message::TYPE_FILE;
        }

        $message = Message::create($messageData);

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

        $this->notifyParticipants($message, $course);

        $this->newMessage = '';
        $this->attachment = null;
        $this->loadMessages();
    }

    protected function notifyParticipants(Message $message, Course $course): void
    {
        if ($course->user_id !== auth()->id()) {
            $instructor = $course->instructor;
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

    public function render(): View
    {
        return view('livewire.course-chat')
            ->layout('layouts.site');
    }
}
