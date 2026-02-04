<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class StoreProducts extends Page
{
    use WithFileUploads, WithPagination;
    
    protected static ?string $navigationLabel = 'المنتجات';
    
    protected static ?string $title = 'إدارة المنتجات';
    
    protected static ?int $navigationSort = 2;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected string $view = 'filament.pages.store-products';

    protected static ?string $slug = 'store-products-legacy';

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المتجر';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return false; // تم استبداله بـ ProductResource
    }
    
    // Filters
    public string $search = '';
    public ?int $filterCategory = null;
    public string $filterStatus = 'all';
    public string $filterStock = 'all';
    
    // Form
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $sku = '';
    public string $shortDescription = '';
    public string $description = '';
    public string $price = '';
    public string $comparePrice = '';
    public string $costPrice = '';
    public string $quantity = '0';
    public string $lowStockThreshold = '5';
    public ?int $categoryId = null;
    public $mainImage = null;
    public string $existingMainImage = '';
    public $additionalImages = [];
    public string $weight = '';
    public string $dimensions = '';
    public bool $isActive = true;
    public bool $isFeatured = false;
    public bool $isDigital = false;
    public bool $requiresShipping = true;
    public bool $trackQuantity = true;
    
    public function mount(): void
    {
        //
    }
    
    public function getProductsProperty()
    {
        return Product::with('category')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('sku', 'like', "%{$this->search}%"))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->filterStock === 'in_stock', fn ($q) => $q->inStock())
            ->when($this->filterStock === 'low_stock', fn ($q) => $q->lowStock())
            ->when($this->filterStock === 'out_of_stock', fn ($q) => $q->outOfStock())
            ->orderByDesc('created_at')
            ->paginate(15);
    }
    
    public function getCategoriesProperty()
    {
        return ProductCategory::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();
    }
    
    public function getStatsProperty(): array
    {
        return [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'low_stock' => Product::lowStock()->count(),
            'out_of_stock' => Product::outOfStock()->count(),
        ];
    }
    
    public function resetForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->name = '';
        $this->sku = '';
        $this->shortDescription = '';
        $this->description = '';
        $this->price = '';
        $this->comparePrice = '';
        $this->costPrice = '';
        $this->quantity = '0';
        $this->lowStockThreshold = '5';
        $this->categoryId = null;
        $this->mainImage = null;
        $this->existingMainImage = '';
        $this->additionalImages = [];
        $this->weight = '';
        $this->dimensions = '';
        $this->isActive = true;
        $this->isFeatured = false;
        $this->isDigital = false;
        $this->requiresShipping = true;
        $this->trackQuantity = true;
    }
    
    public function openAddForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }
    
    public function editProduct(int $id): void
    {
        $product = Product::find($id);
        if ($product) {
            $this->editingId = $product->id;
            $this->name = $product->name;
            $this->sku = $product->sku ?? '';
            $this->shortDescription = $product->short_description ?? '';
            $this->description = $product->description ?? '';
            $this->price = (string) $product->price;
            $this->comparePrice = $product->compare_price ? (string) $product->compare_price : '';
            $this->costPrice = $product->cost_price ? (string) $product->cost_price : '';
            $this->quantity = (string) $product->quantity;
            $this->lowStockThreshold = (string) $product->low_stock_threshold;
            $this->categoryId = $product->category_id;
            $this->existingMainImage = $product->main_image ?? '';
            $this->weight = $product->weight ? (string) $product->weight : '';
            $this->dimensions = $product->dimensions ?? '';
            $this->isActive = $product->is_active;
            $this->isFeatured = $product->is_featured;
            $this->isDigital = $product->is_digital;
            $this->requiresShipping = $product->requires_shipping;
            $this->trackQuantity = $product->track_quantity;
            $this->showForm = true;
        }
    }
    
    public function saveProduct(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'mainImage' => 'nullable|image|max:5120',
        ], [
            'name.required' => 'اسم المنتج مطلوب',
            'price.required' => 'سعر المنتج مطلوب',
        ]);
        
        $data = [
            'name' => $this->name,
            'sku' => $this->sku ?: null,
            'short_description' => $this->shortDescription ?: null,
            'description' => $this->description ?: null,
            'price' => (float) $this->price,
            'compare_price' => $this->comparePrice ? (float) $this->comparePrice : null,
            'cost_price' => $this->costPrice ? (float) $this->costPrice : null,
            'quantity' => (int) $this->quantity,
            'low_stock_threshold' => (int) $this->lowStockThreshold,
            'category_id' => $this->categoryId,
            'weight' => $this->weight ? (float) $this->weight : null,
            'dimensions' => $this->dimensions ?: null,
            'is_active' => $this->isActive,
            'is_featured' => $this->isFeatured,
            'is_digital' => $this->isDigital,
            'requires_shipping' => $this->requiresShipping,
            'track_quantity' => $this->trackQuantity,
        ];
        
        if ($this->mainImage) {
            $data['main_image'] = $this->mainImage->store('products', 'public');
        }
        
        if ($this->editingId) {
            $product = Product::find($this->editingId);
            $product->update($data);
            session()->flash('success', 'تم تحديث المنتج بنجاح');
        } else {
            $product = Product::create($data);
            session()->flash('success', 'تم إضافة المنتج بنجاح');
        }
        
        // Handle additional images
        if (!empty($this->additionalImages)) {
            foreach ($this->additionalImages as $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $image->store('products', 'public'),
                ]);
            }
        }
        
        $this->resetForm();
    }
    
    public function deleteProduct(int $id): void
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            session()->flash('success', 'تم حذف المنتج بنجاح');
        }
    }
    
    public function toggleStatus(int $id): void
    {
        $product = Product::find($id);
        if ($product) {
            $product->update(['is_active' => !$product->is_active]);
        }
    }
    
    public function duplicateProduct(int $id): void
    {
        $product = Product::find($id);
        if ($product) {
            $newProduct = $product->replicate();
            $newProduct->name = $product->name . ' (نسخة)';
            $newProduct->slug = null;
            $newProduct->sku = null;
            $newProduct->save();
            session()->flash('success', 'تم نسخ المنتج بنجاح');
        }
    }
}
