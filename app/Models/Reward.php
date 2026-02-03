<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'points_required',
        'value',
        'course_id',
        'image',
        'is_active',
        'quantity',
        'redeemed_count',
        'expires_at',
    ];

    protected $casts = [
        'points_required' => 'integer',
        'value' => 'integer',
        'is_active' => 'boolean',
        'quantity' => 'integer',
        'redeemed_count' => 'integer',
        'expires_at' => 'datetime',
    ];

    // Reward types
    const TYPE_DISCOUNT = 'discount';
    const TYPE_FREE_COURSE = 'free_course';
    const TYPE_BADGE = 'badge';
    const TYPE_CERTIFICATE = 'certificate';

    /**
     * Get the course (for free course rewards)
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get redemptions
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Check if reward is available
     */
    public function isAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->quantity !== null && $this->redeemed_count >= $this->quantity) {
            return false;
        }

        return true;
    }

    /**
     * Get remaining quantity
     */
    public function getRemainingQuantityAttribute(): ?int
    {
        if ($this->quantity === null) {
            return null;
        }

        return max(0, $this->quantity - $this->redeemed_count);
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_DISCOUNT => 'خصم',
            self::TYPE_FREE_COURSE => 'دورة مجانية',
            self::TYPE_BADGE => 'شارة',
            self::TYPE_CERTIFICATE => 'شهادة',
            default => 'مكافأة',
        };
    }

    /**
     * Get type icon
     */
    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_DISCOUNT => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
            self::TYPE_FREE_COURSE => 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
            self::TYPE_BADGE => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
            self::TYPE_CERTIFICATE => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            default => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
        };
    }

    /**
     * Get type color
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_DISCOUNT => 'orange',
            self::TYPE_FREE_COURSE => 'purple',
            self::TYPE_BADGE => 'amber',
            self::TYPE_CERTIFICATE => 'blue',
            default => 'gray',
        };
    }

    /**
     * Scope for available rewards
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('quantity')
                  ->orWhereRaw('redeemed_count < quantity');
            });
    }
}
