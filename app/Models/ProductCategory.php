<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'parent_id',
        'sort_order',
        'is_active',
        'is_featured',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
    
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }
    
    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id')->orderBy('sort_order');
    }
    
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
    
    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id')->where('is_active', true);
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeMain($query)
    {
        return $query->whereNull('parent_id');
    }
    
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    
    public function getFullPathAttribute(): string
    {
        $path = $this->name;
        $parent = $this->parent;
        
        while ($parent) {
            $path = $parent->name . ' > ' . $path;
            $parent = $parent->parent;
        }
        
        return $path;
    }
    
    public function getAllProductsCount(): int
    {
        $count = $this->products()->count();
        
        foreach ($this->children as $child) {
            $count += $child->getAllProductsCount();
        }
        
        return $count;
    }
}
