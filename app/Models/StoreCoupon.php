<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreCoupon extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_order',
        'maximum_discount',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'is_active',
        'starts_at',
        'expires_at',
        'applicable_categories',
        'applicable_products',
    ];
    
    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'applicable_categories' => 'array',
        'applicable_products' => 'array',
    ];
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereColumn('usage_count', '<', 'usage_limit');
            });
    }
    
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }
        
        return true;
    }
    
    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->isValid()) {
            return 0;
        }
        
        if ($this->minimum_order && $subtotal < $this->minimum_order) {
            return 0;
        }
        
        $discount = $this->type === 'percentage'
            ? ($subtotal * $this->value / 100)
            : $this->value;
        
        if ($this->maximum_discount) {
            $discount = min($discount, $this->maximum_discount);
        }
        
        return min($discount, $subtotal);
    }
    
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
