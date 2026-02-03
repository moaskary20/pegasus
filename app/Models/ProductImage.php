<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image',
        'alt_text',
        'sort_order',
        'is_primary',
    ];
    
    protected $casts = [
        'is_primary' => 'boolean',
    ];
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        return asset('storage/' . $this->image);
    }
}
