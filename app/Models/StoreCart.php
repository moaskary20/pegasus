<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreCart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'variant_id',
        'quantity',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
    
    public function getUnitPriceAttribute(): float
    {
        return $this->variant?->final_price ?? $this->product->price;
    }
    
    public function getTotalAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }
    
    public static function getCart(?int $userId = null, ?string $sessionId = null)
    {
        return static::with(['product', 'variant'])
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(!$userId && $sessionId, fn ($q) => $q->where('session_id', $sessionId))
            ->get();
    }
    
    public static function getCartTotal(?int $userId = null, ?string $sessionId = null): float
    {
        return static::getCart($userId, $sessionId)->sum(fn ($item) => $item->total);
    }
}
