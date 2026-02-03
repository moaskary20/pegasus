<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RewardRedemption extends Model
{
    protected $fillable = [
        'user_id',
        'reward_id',
        'points_spent',
        'status',
        'code',
        'used_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'points_spent' => 'integer',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_USED = 'used';

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reward
     */
    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * Generate unique discount code
     */
    public static function generateCode(): string
    {
        do {
            $code = 'PEG-' . strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Check if redemption is valid
     */
    public function isValid(): bool
    {
        if ($this->status === self::STATUS_EXPIRED || $this->status === self::STATUS_USED) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Mark as used
     */
    public function markAsUsed(): void
    {
        $this->update([
            'status' => self::STATUS_USED,
            'used_at' => now(),
        ]);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'معلق',
            self::STATUS_COMPLETED => 'مكتمل',
            self::STATUS_EXPIRED => 'منتهي',
            self::STATUS_USED => 'مستخدم',
            default => 'غير معروف',
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'amber',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_EXPIRED => 'gray',
            self::STATUS_USED => 'blue',
            default => 'gray',
        };
    }

    /**
     * Boot method
     */
    protected static function booted(): void
    {
        static::creating(function (RewardRedemption $redemption) {
            if (!$redemption->code && $redemption->reward?->type === Reward::TYPE_DISCOUNT) {
                $redemption->code = self::generateCode();
            }

            if (!$redemption->expires_at) {
                $redemption->expires_at = now()->addDays(30);
            }
        });
    }
}
