<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'type',
        'name',
        'course_id',
        'created_by',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // Types
    const TYPE_PRIVATE = 'private';
    const TYPE_GROUP = 'group';
    const TYPE_COURSE = 'course';

    /**
     * Get the participants of this conversation
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * Get the users in this conversation
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['last_read_at', 'is_muted'])
            ->withTimestamps();
    }

    /**
     * Get all messages in this conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the latest message
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Get the course (for course conversations)
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the creator of this conversation
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if user is a participant
     */
    public function hasParticipant(int $userId): bool
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCountFor(int $userId): int
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        
        if (!$participant || !$participant->last_read_at) {
            return $this->messages()->where('user_id', '!=', $userId)->count();
        }
        
        return $this->messages()
            ->where('user_id', '!=', $userId)
            ->where('created_at', '>', $participant->last_read_at)
            ->count();
    }

    /**
     * Get the other participant in a private conversation
     */
    public function getOtherParticipant(int $userId): ?User
    {
        if ($this->type !== self::TYPE_PRIVATE) {
            return null;
        }
        
        return $this->users()->where('users.id', '!=', $userId)->first();
    }

    /**
     * Get or create a private conversation between two users
     */
    public static function getOrCreatePrivate(int $userId1, int $userId2): self
    {
        // Find existing private conversation
        $conversation = self::where('type', self::TYPE_PRIVATE)
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId1))
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId2))
            ->first();
        
        if ($conversation) {
            return $conversation;
        }
        
        // Create new conversation
        $conversation = self::create([
            'type' => self::TYPE_PRIVATE,
            'created_by' => $userId1,
        ]);
        
        // Add participants
        $conversation->participants()->createMany([
            ['user_id' => $userId1],
            ['user_id' => $userId2],
        ]);
        
        return $conversation;
    }

    /**
     * Get or create a course conversation
     */
    public static function getOrCreateForCourse(Course $course): self
    {
        $conversation = self::where('type', self::TYPE_COURSE)
            ->where('course_id', $course->id)
            ->first();
        
        if ($conversation) {
            return $conversation;
        }
        
        // Create new conversation
        $conversation = self::create([
            'type' => self::TYPE_COURSE,
            'name' => $course->title,
            'course_id' => $course->id,
            'created_by' => $course->user_id,
        ]);
        
        // Add instructor
        $conversation->participants()->create(['user_id' => $course->user_id]);
        
        // Add all enrolled students
        $enrolledUserIds = $course->enrollments()->pluck('user_id');
        foreach ($enrolledUserIds as $userId) {
            if ($userId !== $course->user_id) {
                $conversation->participants()->firstOrCreate(['user_id' => $userId]);
            }
        }
        
        return $conversation;
    }

    /**
     * Scope for user's conversations
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('participants', fn($q) => $q->where('user_id', $userId));
    }

    /**
     * Scope for unread conversations
     */
    public function scopeWithUnread($query, int $userId)
    {
        return $query->whereHas('messages', function ($q) use ($userId) {
            $q->where('user_id', '!=', $userId)
              ->whereRaw('messages.created_at > COALESCE(
                  (SELECT last_read_at FROM conversation_participants 
                   WHERE conversation_id = conversations.id AND user_id = ?), 
                  "1970-01-01"
              )', [$userId]);
        });
    }
}
