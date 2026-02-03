<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'quantity',
        'attributes',
        'image',
        'is_active',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($variant) {
            if (empty($variant->sku)) {
                $variant->sku = 'VAR-' . strtoupper(Str::random(8));
            }
        });
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    public function getFinalPriceAttribute(): float
    {
        return $this->price ?? $this->product->price;
    }
    
    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }
}
