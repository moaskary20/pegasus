<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationParticipant extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'last_read_at',
        'is_muted',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'is_muted' => 'boolean',
    ];

    /**
     * Get the conversation
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead(): void
    {
        $this->update(['last_read_at' => now()]);
    }

    /**
     * Toggle mute status
     */
    public function toggleMute(): void
    {
        $this->update(['is_muted' => !$this->is_muted]);
    }
}
