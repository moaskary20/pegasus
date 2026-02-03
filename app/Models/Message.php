<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
        'type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Types
    const TYPE_TEXT = 'text';
    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';

    /**
     * Get the conversation
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the sender
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attachments
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Check if message has attachments
     */
    public function hasAttachments(): bool
    {
        return $this->attachments()->exists();
    }

    /**
     * Check if message is from a specific user
     */
    public function isFrom(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    /**
     * Get formatted time
     */
    public function getFormattedTimeAttribute(): string
    {
        $now = now();
        
        if ($this->created_at->isToday()) {
            return $this->created_at->format('h:i A');
        }
        
        if ($this->created_at->isYesterday()) {
            return 'أمس ' . $this->created_at->format('h:i A');
        }
        
        if ($this->created_at->isCurrentWeek()) {
            return $this->created_at->translatedFormat('l h:i A');
        }
        
        return $this->created_at->format('Y/m/d h:i A');
    }

    /**
     * Boot method
     */
    protected static function booted(): void
    {
        static::created(function (Message $message) {
            // Update conversation's last_message_at
            $message->conversation->update(['last_message_at' => $message->created_at]);
        });
    }
}
