<?php

namespace App\Filament\Pages;

use App\Models\ProductCategory;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithFileUploads;

class StoreCategories extends Page
{
    use WithFileUploads;
    
    protected static ?string $navigationLabel = 'التصنيفات';
    
    protected static ?string $title = 'تصنيفات المنتجات';
    
    protected static ?int $navigationSort = 1;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected string $view = 'filament.pages.store-categories';
    
    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المتجر';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
    
    // Form fields
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $description = '';
    public ?int $parentId = null;
    public $image = null;
    public string $existingImage = '';
    public int $sortOrder = 0;
    public bool $isActive = true;
    public bool $isFeatured = false;
    
    public ?int $selectedCategoryId = null;
    
    public function mount(): void
    {
        //
    }
    
    public function getMainCategoriesProperty()
    {
        return ProductCategory::whereNull('parent_id')
            ->withCount('children', 'products')
            ->orderBy('sort_order')
            ->get();
    }
    
    public function getSubCategoriesProperty()
    {
        if (!$this->selectedCategoryId) {
            return collect();
        }
        
        return ProductCategory::where('parent_id', $this->selectedCategoryId)
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();
    }
    
    public function getAllCategoriesProperty()
    {
        return ProductCategory::with('parent')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
    }
    
    public function selectCategory(?int $id): void
    {
        $this->selectedCategoryId = $id;
        $this->resetForm();
    }
    
    public function resetForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->parentId = null;
        $this->image = null;
        $this->existingImage = '';
        $this->sortOrder = 0;
        $this->isActive = true;
        $this->isFeatured = false;
    }
    
    public function openAddForm(?int $parentId = null): void
    {
        $this->resetForm();
        $this->parentId = $parentId;
        $this->showForm = true;
    }
    
    public function editCategory(int $id): void
    {
        $category = ProductCategory::find($id);
        if ($category) {
            $this->editingId = $category->id;
            $this->name = $category->name;
            $this->description = $category->description ?? '';
            $this->parentId = $category->parent_id;
            $this->existingImage = $category->image ?? '';
            $this->sortOrder = $category->sort_order;
            $this->isActive = $category->is_active;
            $this->isFeatured = $category->is_featured;
            $this->showForm = true;
        }
    }
    
    public function saveCategory(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ], [
            'name.required' => 'اسم التصنيف مطلوب',
        ]);
        
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'parent_id' => $this->parentId,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
            'is_featured' => $this->isFeatured,
        ];
        
        if ($this->image) {
            $data['image'] = $this->image->store('categories', 'public');
        }
        
        if ($this->editingId) {
            $category = ProductCategory::find($this->editingId);
            $category->update($data);
            session()->flash('success', 'تم تحديث التصنيف بنجاح');
        } else {
            ProductCategory::create($data);
            session()->flash('success', 'تم إضافة التصنيف بنجاح');
        }
        
        $this->resetForm();
    }
    
    public function deleteCategory(int $id): void
    {
        $category = ProductCategory::find($id);
        if ($category) {
            // Move children to parent if any
            ProductCategory::where('parent_id', $id)->update(['parent_id' => $category->parent_id]);
            $category->delete();
            
            if ($this->selectedCategoryId === $id) {
                $this->selectedCategoryId = null;
            }
            
            session()->flash('success', 'تم حذف التصنيف بنجاح');
        }
    }
    
    public function toggleStatus(int $id): void
    {
        $category = ProductCategory::find($id);
        if ($category) {
            $category->update(['is_active' => !$category->is_active]);
        }
    }
}
