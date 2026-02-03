<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernorateShippingRate extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'code',
        'region',
        'shipping_cost',
        'free_shipping_threshold',
        'estimated_days_min',
        'estimated_days_max',
        'is_active',
        'cash_on_delivery',
        'notes',
        'sort_order',
    ];
    
    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'is_active' => 'boolean',
        'cash_on_delivery' => 'boolean',
    ];
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
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
    
    public function calculateShipping(float $orderTotal): float
    {
        if ($this->free_shipping_threshold && $orderTotal >= $this->free_shipping_threshold) {
            return 0;
        }
        
        return $this->shipping_cost;
    }
    
    public static function getRegions(): array
    {
        return static::distinct()
            ->pluck('region')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
    
    public static function getByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
