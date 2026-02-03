<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = [
        'name',
        'cities',
        'is_active',
    ];
    
    protected $casts = [
        'cities' => 'array',
        'is_active' => 'boolean',
    ];
    
    public function methods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'zone_id')->orderBy('sort_order');
    }
    
    public function activeMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'zone_id')->where('is_active', true)->orderBy('sort_order');
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function containsCity(string $city): bool
    {
        if (empty($this->cities)) {
            return true; // Empty means all cities
        }
        return in_array($city, $this->cities);
    }
}
