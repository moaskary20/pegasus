<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}
    
    /**
     * Get search suggestions with caching
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $userId = auth()->id();
        
        // Cache suggestions for anonymous users
        if (!$userId && strlen($query) >= 2) {
            $cacheKey = 'suggestions:anon:' . md5($query);
            $suggestions = Cache::remember($cacheKey, now()->addMinutes(10), fn() => 
                $this->searchService->getSuggestions(null, $query)
            );
        } else {
            $suggestions = $this->searchService->getSuggestions($userId, $query);
        }
        
        return response()->json($suggestions);
    }
    
    /**
     * Perform search and return results with caching
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'category_id' => 'nullable|integer',
            'level' => 'nullable|string|in:beginner,intermediate,advanced',
            'is_free' => 'nullable|boolean',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'instructor_id' => 'nullable|integer',
            'sort' => 'nullable|string|in:relevance,newest,rating,students,price_asc,price_desc',
        ]);
        
        $query = $request->get('q');
        $filters = $request->only([
            'category_id',
            'level',
            'is_free',
            'min_rating',
            'instructor_id',
            'sort',
        ]);
        
        $results = $this->searchService->search($query, array_filter($filters), auth()->id());
        
        return response()->json([
            'query' => $query,
            'results' => $results,
            'total' => collect($results)->sum(fn($items) => $items->count()),
        ]);
    }
    
    /**
     * Clear user search history
     */
    public function clearHistory(): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $this->searchService->clearUserHistory(auth()->id());
        
        // Clear user-specific cache
        Cache::forget('user:' . auth()->id() . ':recent_searches');
        
        return response()->json(['success' => true]);
    }
}
