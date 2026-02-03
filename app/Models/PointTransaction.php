<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'points',
        'type',
        'description',
        'pointable_type',
        'pointable_id',
        'metadata',
    ];

    protected $casts = [
        'points' => 'integer',
        'metadata' => 'array',
    ];

    // Transaction types
    const TYPE_LESSON_COMPLETED = 'lesson_completed';
    const TYPE_QUIZ_PASSED = 'quiz_passed';
    const TYPE_COURSE_COMPLETED = 'course_completed';
    const TYPE_REWARD_REDEEMED = 'reward_redeemed';
    const TYPE_BONUS = 'bonus';
    const TYPE_REFERRAL = 'referral';
    const TYPE_DAILY_LOGIN = 'daily_login';
    const TYPE_FIRST_ENROLLMENT = 'first_enrollment';

    // Points configuration
    const POINTS_LESSON_COMPLETED = 10;
    const POINTS_QUIZ_PASSED = 25;
    const POINTS_QUIZ_PERFECT = 50;
    const POINTS_COURSE_COMPLETED = 100;
    const POINTS_FIRST_ENROLLMENT = 50;
    const POINTS_DAILY_LOGIN = 5;

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pointable model
     */
    public function pointable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if transaction is positive (earning)
     */
    public function isEarning(): bool
    {
        return $this->points > 0;
    }

    /**
     * Check if transaction is negative (spending)
     */
    public function isSpending(): bool
    {
        return $this->points < 0;
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_LESSON_COMPLETED => 'إكمال درس',
            self::TYPE_QUIZ_PASSED => 'اجتياز اختبار',
            self::TYPE_COURSE_COMPLETED => 'إكمال دورة',
            self::TYPE_REWARD_REDEEMED => 'استبدال مكافأة',
            self::TYPE_BONUS => 'مكافأة',
            self::TYPE_REFERRAL => 'إحالة صديق',
            self::TYPE_DAILY_LOGIN => 'تسجيل دخول يومي',
            self::TYPE_FIRST_ENROLLMENT => 'أول تسجيل',
            default => 'معاملة',
        };
    }

    /**
     * Get type icon
     */
    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_LESSON_COMPLETED => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z',
            self::TYPE_QUIZ_PASSED => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            self::TYPE_COURSE_COMPLETED => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
            self::TYPE_REWARD_REDEEMED => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
            default => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }

    /**
     * Get type color
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_LESSON_COMPLETED => 'purple',
            self::TYPE_QUIZ_PASSED => 'green',
            self::TYPE_COURSE_COMPLETED => 'amber',
            self::TYPE_REWARD_REDEEMED => 'red',
            self::TYPE_BONUS => 'blue',
            self::TYPE_REFERRAL => 'teal',
            self::TYPE_DAILY_LOGIN => 'indigo',
            self::TYPE_FIRST_ENROLLMENT => 'emerald',
            default => 'gray',
        };
    }
}
