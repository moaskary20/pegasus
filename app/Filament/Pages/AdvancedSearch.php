<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\User;
use App\Services\SearchService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;

class AdvancedSearch extends Page
{
    protected static ?string $navigationLabel = 'البحث المتقدم';
    
    protected static ?string $title = 'البحث المتقدم';
    
    protected static ?int $navigationSort = 1;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;
    
    protected string $view = 'filament.pages.advanced-search';
    
    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الدورات التدريبية';
    }
    
    protected static ?string $slug = 'search';
    
    // Search query
    #[Url]
    public string $query = '';
    
    // Filters
    #[Url]
    public ?int $categoryId = null;
    
    #[Url]
    public ?string $level = null;
    
    #[Url]
    public ?string $priceType = null; // 'free', 'paid', null (all)
    
    #[Url]
    public ?float $minRating = null;
    
    #[Url]
    public ?int $instructorId = null;
    
    #[Url]
    public ?string $sort = 'relevance';
    
    // Results
    public array $results = [];
    public array $suggestions = [];
    public bool $hasSearched = false;
    public int $totalResults = 0;
    
    // Active tab
    public string $activeTab = 'courses';
    
    protected SearchService $searchService;
    
    public function boot(SearchService $searchService): void
    {
        $this->searchService = $searchService;
    }
    
    public function mount(): void
    {
        // Load suggestions on mount
        $this->loadSuggestions();
        
        // If query exists in URL, perform search
        if (!empty($this->query)) {
            $this->performSearch();
        }
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return true; // Visible to all users
    }
    
    public function updatedQuery(): void
    {
        if (strlen($this->query) >= 2) {
            $this->loadSuggestions();
        }
    }
    
    public function search(): void
    {
        $this->performSearch();
    }
    
    public function performSearch(): void
    {
        if (strlen(trim($this->query)) < 2) {
            $this->results = [];
            $this->hasSearched = false;
            return;
        }
        
        $filters = $this->getFilters();
        
        $this->results = $this->searchService->search(
            $this->query,
            $filters,
            auth()->id()
        );
        
        $this->hasSearched = true;
        $this->totalResults = $this->calculateTotalResults();
        
        // Set active tab to first non-empty result
        $this->setActiveTabToFirstResult();
    }
    
    public function clearFilters(): void
    {
        $this->categoryId = null;
        $this->level = null;
        $this->priceType = null;
        $this->minRating = null;
        $this->instructorId = null;
        $this->sort = 'relevance';
        
        if ($this->hasSearched) {
            $this->performSearch();
        }
    }
    
    public function clearSearch(): void
    {
        $this->query = '';
        $this->results = [];
        $this->hasSearched = false;
        $this->totalResults = 0;
        $this->loadSuggestions();
    }
    
    public function clearHistory(): void
    {
        if (auth()->check()) {
            $this->searchService->clearUserHistory(auth()->id());
            $this->loadSuggestions();
        }
    }
    
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
    
    public function searchFromSuggestion(string $suggestion): void
    {
        $this->query = $suggestion;
        $this->performSearch();
    }
    
    protected function loadSuggestions(): void
    {
        $this->suggestions = $this->searchService->getSuggestions(
            auth()->id(),
            $this->query
        );
    }
    
    protected function getFilters(): array
    {
        $filters = [];
        
        if ($this->categoryId) {
            $filters['category_id'] = $this->categoryId;
        }
        
        if ($this->level) {
            $filters['level'] = $this->level;
        }
        
        if ($this->priceType === 'free') {
            $filters['is_free'] = true;
        } elseif ($this->priceType === 'paid') {
            $filters['is_free'] = false;
        }
        
        if ($this->minRating) {
            $filters['min_rating'] = $this->minRating;
        }
        
        if ($this->instructorId) {
            $filters['instructor_id'] = $this->instructorId;
        }
        
        if ($this->sort && $this->sort !== 'relevance') {
            $filters['sort'] = $this->sort;
        }
        
        return $filters;
    }
    
    protected function calculateTotalResults(): int
    {
        if (empty($this->results)) {
            return 0;
        }
        
        return collect($this->results)->sum(fn($items) => $items->count());
    }
    
    protected function setActiveTabToFirstResult(): void
    {
        $tabs = ['courses', 'lessons', 'instructors', 'questions'];
        
        foreach ($tabs as $tab) {
            if (isset($this->results[$tab]) && $this->results[$tab]->isNotEmpty()) {
                $this->activeTab = $tab;
                return;
            }
        }
    }
    
    public function getCategories(): array
    {
        return Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
    
    public function getInstructors(): array
    {
        return User::instructors()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
    
    public function getLevels(): array
    {
        return [
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
        ];
    }
    
    public function getPriceTypes(): array
    {
        return [
            '' => 'الكل',
            'free' => 'مجاني',
            'paid' => 'مدفوع',
        ];
    }
    
    public function getRatings(): array
    {
        return [
            '' => 'الكل',
            '4' => '4+ نجوم',
            '3' => '3+ نجوم',
            '2' => '2+ نجوم',
        ];
    }
    
    public function getSortOptions(): array
    {
        return [
            'relevance' => 'الأكثر صلة',
            'newest' => 'الأحدث',
            'rating' => 'الأعلى تقييماً',
            'students' => 'الأكثر طلاباً',
            'price_asc' => 'السعر: من الأقل للأعلى',
            'price_desc' => 'السعر: من الأعلى للأقل',
        ];
    }
    
    public function getResultsCount(string $type): int
    {
        return isset($this->results[$type]) ? $this->results[$type]->count() : 0;
    }
}
