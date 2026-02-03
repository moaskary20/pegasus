<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithPagination;

class StoreReviews extends Page
{
    use WithPagination;
    
    protected static ?string $navigationLabel = 'التقييمات';
    
    protected static ?string $title = 'تقييمات المنتجات';
    
    protected static ?int $navigationSort = 6;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected string $view = 'filament.pages.store-reviews';
    
    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المتجر';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
    
    public static function getNavigationBadge(): ?string
    {
        $count = ProductReview::where('is_approved', false)->count();
        return $count > 0 ? (string) $count : null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    
    // Filters
    public string $search = '';
    public string $filterStatus = 'all';
    public string $filterRating = 'all';
    public ?int $filterProduct = null;
    
    // Form
    public bool $showForm = false;
    public ?int $editingId = null;
    public ?int $productId = null;
    public ?int $userId = null;
    public int $rating = 5;
    public string $reviewTitle = '';
    public string $comment = '';
    public bool $isApproved = false;
    public bool $isVerifiedPurchase = false;
    
    // Selected review for detail view
    public ?int $selectedReviewId = null;
    
    public function mount(): void
    {
        //
    }
    
    public function getReviewsProperty()
    {
        return ProductReview::with(['product', 'user'])
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$this->search}%"))
                ->orWhere('title', 'like', "%{$this->search}%")
                ->orWhere('comment', 'like', "%{$this->search}%"))
            ->when($this->filterStatus === 'approved', fn ($q) => $q->where('is_approved', true))
            ->when($this->filterStatus === 'pending', fn ($q) => $q->where('is_approved', false))
            ->when($this->filterRating !== 'all', fn ($q) => $q->where('rating', $this->filterRating))
            ->when($this->filterProduct, fn ($q) => $q->where('product_id', $this->filterProduct))
            ->orderByDesc('created_at')
            ->paginate(15);
    }
    
    public function getProductsProperty()
    {
        return Product::orderBy('name')->get(['id', 'name']);
    }
    
    public function getUsersProperty()
    {
        return User::orderBy('name')->get(['id', 'name', 'email']);
    }
    
    public function getStatsProperty(): array
    {
        return [
            'total' => ProductReview::count(),
            'approved' => ProductReview::where('is_approved', true)->count(),
            'pending' => ProductReview::where('is_approved', false)->count(),
            'average' => round(ProductReview::where('is_approved', true)->avg('rating') ?? 0, 1),
            'five_star' => ProductReview::where('rating', 5)->count(),
            'one_star' => ProductReview::where('rating', 1)->count(),
        ];
    }
    
    public function getRatingDistributionProperty(): array
    {
        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = ProductReview::where('rating', $i)->count();
            $distribution[$i] = [
                'count' => $count,
                'percentage' => ProductReview::count() > 0 ? round(($count / ProductReview::count()) * 100) : 0,
            ];
        }
        return $distribution;
    }
    
    public function getSelectedReviewProperty()
    {
        if (!$this->selectedReviewId) {
            return null;
        }
        return ProductReview::with(['product', 'user', 'order'])->find($this->selectedReviewId);
    }
    
    public function selectReview(?int $id): void
    {
        $this->selectedReviewId = $id;
    }
    
    public function resetForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->productId = null;
        $this->userId = null;
        $this->rating = 5;
        $this->reviewTitle = '';
        $this->comment = '';
        $this->isApproved = false;
        $this->isVerifiedPurchase = false;
    }
    
    public function openAddForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }
    
    public function editReview(int $id): void
    {
        $review = ProductReview::find($id);
        if ($review) {
            $this->editingId = $review->id;
            $this->productId = $review->product_id;
            $this->userId = $review->user_id;
            $this->rating = $review->rating;
            $this->reviewTitle = $review->title ?? '';
            $this->comment = $review->comment ?? '';
            $this->isApproved = $review->is_approved;
            $this->isVerifiedPurchase = $review->is_verified_purchase;
            $this->showForm = true;
        }
    }
    
    public function saveReview(): void
    {
        $this->validate([
            'productId' => 'required|exists:products,id',
            'userId' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
        ], [
            'productId.required' => 'المنتج مطلوب',
            'userId.required' => 'العميل مطلوب',
        ]);
        
        $data = [
            'product_id' => $this->productId,
            'user_id' => $this->userId,
            'rating' => $this->rating,
            'title' => $this->reviewTitle ?: null,
            'comment' => $this->comment ?: null,
            'is_approved' => $this->isApproved,
            'is_verified_purchase' => $this->isVerifiedPurchase,
        ];
        
        if ($this->editingId) {
            $review = ProductReview::find($this->editingId);
            $review->update($data);
            session()->flash('success', 'تم تحديث التقييم بنجاح');
        } else {
            ProductReview::create($data);
            session()->flash('success', 'تم إضافة التقييم بنجاح');
        }
        
        // Update product rating
        if ($this->productId) {
            Product::find($this->productId)?->updateRating();
        }
        
        $this->resetForm();
    }
    
    public function deleteReview(int $id): void
    {
        $review = ProductReview::find($id);
        if ($review) {
            $productId = $review->product_id;
            $review->delete();
            
            // Update product rating
            Product::find($productId)?->updateRating();
            
            if ($this->selectedReviewId === $id) {
                $this->selectedReviewId = null;
            }
            
            session()->flash('success', 'تم حذف التقييم بنجاح');
        }
    }
    
    public function approveReview(int $id): void
    {
        $review = ProductReview::find($id);
        if ($review) {
            $review->update(['is_approved' => true]);
            Product::find($review->product_id)?->updateRating();
            session()->flash('success', 'تم الموافقة على التقييم');
        }
    }
    
    public function rejectReview(int $id): void
    {
        $review = ProductReview::find($id);
        if ($review) {
            $review->update(['is_approved' => false]);
            Product::find($review->product_id)?->updateRating();
            session()->flash('success', 'تم رفض التقييم');
        }
    }
    
    public function toggleApproval(int $id): void
    {
        $review = ProductReview::find($id);
        if ($review) {
            $review->update(['is_approved' => !$review->is_approved]);
            Product::find($review->product_id)?->updateRating();
        }
    }
    
    public function bulkApprove(): void
    {
        ProductReview::where('is_approved', false)->update(['is_approved' => true]);
        
        // Update all products ratings
        $productIds = ProductReview::distinct()->pluck('product_id');
        foreach ($productIds as $productId) {
            Product::find($productId)?->updateRating();
        }
        
        session()->flash('success', 'تمت الموافقة على جميع التقييمات المعلقة');
    }
}
