<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreOrderItem extends Model
{
    protected $fillable = [
        'store_order_id',
        'product_id',
        'variant_id',
        'product_name',
        'variant_name',
        'sku',
        'price',
        'quantity',
        'total',
        'options',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'options' => 'array',
    ];
    
    public function order(): BelongsTo
    {
        return $this->belongsTo(StoreOrder::class, 'store_order_id');
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
