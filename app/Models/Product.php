<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'compare_price',
        'cost_price',
        'quantity',
        'low_stock_threshold',
        'category_id',
        'main_image',
        'weight',
        'dimensions',
        'is_active',
        'is_featured',
        'is_digital',
        'digital_file',
        'requires_shipping',
        'track_quantity',
        'views_count',
        'sales_count',
        'average_rating',
        'ratings_count',
        'meta_data',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_digital' => 'boolean',
        'requires_shipping' => 'boolean',
        'track_quantity' => 'boolean',
        'meta_data' => 'array',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }
    
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
    
    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }
    
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }
    
    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true);
    }
    
    public function orderItems(): HasMany
    {
        return $this->hasMany(StoreOrderItem::class);
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    
    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_quantity', false)
              ->orWhere('quantity', '>', 0);
        });
    }
    
    public function scopeLowStock($query)
    {
        return $query->where('track_quantity', true)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where('quantity', '>', 0);
    }
    
    public function scopeOutOfStock($query)
    {
        return $query->where('track_quantity', true)->where('quantity', '<=', 0);
    }
    
    public function isInStock(): bool
    {
        if (!$this->track_quantity) {
            return true;
        }
        return $this->quantity > 0;
    }
    
    public function isLowStock(): bool
    {
        if (!$this->track_quantity) {
            return false;
        }
        return $this->quantity <= $this->low_stock_threshold && $this->quantity > 0;
    }
    
    public function getDiscountPercentageAttribute(): ?float
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }
        return round((($this->compare_price - $this->price) / $this->compare_price) * 100, 1);
    }
    
    public function getProfitMarginAttribute(): ?float
    {
        if (!$this->cost_price || $this->cost_price <= 0) {
            return null;
        }
        return round((($this->price - $this->cost_price) / $this->cost_price) * 100, 1);
    }
    
    public function getStockStatusAttribute(): string
    {
        if (!$this->track_quantity) {
            return 'available';
        }
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        }
        if ($this->quantity <= $this->low_stock_threshold) {
            return 'low_stock';
        }
        return 'in_stock';
    }
    
    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'in_stock' => 'متوفر',
            'low_stock' => 'مخزون منخفض',
            'out_of_stock' => 'غير متوفر',
            'available' => 'متوفر',
            default => 'غير محدد',
        };
    }
    
    public function decrementStock(int $quantity = 1): void
    {
        if ($this->track_quantity) {
            $this->decrement('quantity', $quantity);
        }
    }
    
    public function incrementStock(int $quantity = 1): void
    {
        if ($this->track_quantity) {
            $this->increment('quantity', $quantity);
        }
    }
    
    public function updateRating(): void
    {
        $reviews = $this->approvedReviews();
        $this->update([
            'average_rating' => $reviews->avg('rating') ?? 0,
            'ratings_count' => $reviews->count(),
        ]);
    }
}
