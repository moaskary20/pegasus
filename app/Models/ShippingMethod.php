<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingMethod extends Model
{
    protected $fillable = [
        'zone_id',
        'name',
        'description',
        'type',
        'cost',
        'minimum_order_for_free',
        'estimated_days_min',
        'estimated_days_max',
        'is_active',
        'sort_order',
    ];
    
    protected $casts = [
        'cost' => 'decimal:2',
        'minimum_order_for_free' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    const TYPE_FLAT = 'flat';
    const TYPE_PER_ITEM = 'per_item';
    const TYPE_PER_WEIGHT = 'per_weight';
    const TYPE_FREE = 'free';
    
    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function calculateCost(float $subtotal, int $itemsCount = 1, float $weight = 0): float
    {
        if ($this->type === self::TYPE_FREE) {
            return 0;
        }
        
        if ($this->minimum_order_for_free && $subtotal >= $this->minimum_order_for_free) {
            return 0;
        }
        
        return match ($this->type) {
            self::TYPE_FLAT => $this->cost,
            self::TYPE_PER_ITEM => $this->cost * $itemsCount,
            self::TYPE_PER_WEIGHT => $this->cost * ($weight / 1000), // per kg
            default => $this->cost,
        };
    }
    
    public function getEstimatedDeliveryAttribute(): ?string
    {
        if (!$this->estimated_days_min && !$this->estimated_days_max) {
            return null;
        }
        
        if ($this->estimated_days_min === $this->estimated_days_max) {
            return "{$this->estimated_days_min} أيام";
        }
        
        return "{$this->estimated_days_min}-{$this->estimated_days_max} أيام";
    }
    
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_FLAT => 'سعر ثابت',
            self::TYPE_PER_ITEM => 'لكل منتج',
            self::TYPE_PER_WEIGHT => 'حسب الوزن',
            self::TYPE_FREE => 'مجاني',
            default => 'غير محدد',
        };
    }
}
