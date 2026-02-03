<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reminder extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'color',
        'action_url',
        'action_label',
        'remindable_type',
        'remindable_id',
        'remind_at',
        'read_at',
        'dismissed_at',
        'is_recurring',
        'recurrence_interval',
    ];

    protected function casts(): array
    {
        return [
            'remind_at' => 'datetime',
            'read_at' => 'datetime',
            'dismissed_at' => 'datetime',
            'is_recurring' => 'boolean',
        ];
    }
    
    // Types
    const TYPE_QUIZ = 'quiz';
    const TYPE_MESSAGE = 'message';
    const TYPE_LESSON = 'lesson';
    const TYPE_COUPON = 'coupon';
    const TYPE_CERTIFICATE = 'certificate';
    const TYPE_RATING = 'rating';
    const TYPE_QUESTION = 'question';
    const TYPE_NEW_COURSE = 'new_course';
    
    public static function getTypes(): array
    {
        return [
            self::TYPE_QUIZ => 'اختبار',
            self::TYPE_MESSAGE => 'رسالة',
            self::TYPE_LESSON => 'درس',
            self::TYPE_COUPON => 'كوبون',
            self::TYPE_CERTIFICATE => 'شهادة',
            self::TYPE_RATING => 'تقييم',
            self::TYPE_QUESTION => 'سؤال',
            self::TYPE_NEW_COURSE => 'دورة جديدة',
        ];
    }
    
    public static function getTypeIcon(string $type): string
    {
        return match($type) {
            self::TYPE_QUIZ => '📝',
            self::TYPE_MESSAGE => '💬',
            self::TYPE_LESSON => '📚',
            self::TYPE_COUPON => '🎟️',
            self::TYPE_CERTIFICATE => '🎓',
            self::TYPE_RATING => '⭐',
            self::TYPE_QUESTION => '❓',
            self::TYPE_NEW_COURSE => '🆕',
            default => '🔔',
        };
    }
    
    public static function getTypeColor(string $type): string
    {
        return match($type) {
            self::TYPE_QUIZ => 'purple',
            self::TYPE_MESSAGE => 'blue',
            self::TYPE_LESSON => 'green',
            self::TYPE_COUPON => 'orange',
            self::TYPE_CERTIFICATE => 'teal',
            self::TYPE_RATING => 'yellow',
            self::TYPE_QUESTION => 'red',
            self::TYPE_NEW_COURSE => 'indigo',
            default => 'gray',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function remindable(): MorphTo
    {
        return $this->morphTo();
    }
    
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
    
    public function scopeActive($query)
    {
        return $query->whereNull('dismissed_at')
            ->where(function ($q) {
                $q->whereNull('remind_at')
                  ->orWhere('remind_at', '<=', now());
            });
    }
    
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
    
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
    
    public function dismiss(): void
    {
        $this->update(['dismissed_at' => now()]);
    }
}
