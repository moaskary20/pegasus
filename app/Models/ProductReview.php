<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'store_order_id',
        'rating',
        'title',
        'comment',
        'pros',
        'cons',
        'is_verified_purchase',
        'is_approved',
        'helpful_count',
    ];
    
    protected $casts = [
        'pros' => 'array',
        'cons' => 'array',
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
    ];
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function order(): BelongsTo
    {
        return $this->belongsTo(StoreOrder::class, 'store_order_id');
    }
    
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
    
    public function scopeVerified($query)
    {
        return $query->where('is_verified_purchase', true);
    }
}
