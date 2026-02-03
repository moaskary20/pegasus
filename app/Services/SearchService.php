<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseQuestion;
use App\Models\Lesson;
use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchService
{
    /**
     * Perform a comprehensive search across all entities
     */
    public function search(string $query, array $filters = [], ?int $userId = null): array
    {
        $query = trim($query);
        
        if (strlen($query) < 2) {
            return $this->getEmptyResults();
        }
        
        // Sanitize query
        $query = $this->sanitizeQuery($query);
        
        // Get cached results if available
        $cacheKey = $this->getCacheKey($query, $filters);
        
        $results = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($query, $filters) {
            return [
                'courses' => $this->searchCourses($query, $filters),
                'lessons' => $this->searchLessons($query, $filters),
                'instructors' => $this->searchInstructors($query),
                'questions' => $this->searchQuestions($query),
            ];
        });
        
        // Save search history for logged-in users
        if ($userId) {
            $this->saveSearchHistory($userId, $query, $this->getTotalResultsCount($results));
        }
        
        return $results;
    }
    
    /**
     * Search courses by title, description, objectives, and instructor name
     */
    public function searchCourses(string $query, array $filters = []): Collection
    {
        $coursesQuery = Course::query()
            ->where('is_published', true)
            ->with(['instructor', 'category', 'enrollments'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('objectives', 'LIKE', "%{$query}%")
                  ->orWhereHas('instructor', fn($q) => $q->where('name', 'LIKE', "%{$query}%"));
            });
        
        // Apply filters
        $this->applyFilters($coursesQuery, $filters);
        
        return $coursesQuery
            ->orderByDesc('students_count')
            ->orderByDesc('rating')
            ->limit(20)
            ->get()
            ->map(fn ($course) => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $this->highlightMatch($course->description, $query, 150),
                'cover_image' => $course->cover_image,
                'instructor' => $course->instructor?->name,
                'instructor_id' => $course->user_id,
                'category' => $course->category?->name,
                'level' => $course->level,
                'price' => $course->offer_price ?? $course->price,
                'original_price' => $course->price,
                'rating' => $course->rating,
                'students_count' => $course->enrollments->count(),
                'hours' => $course->hours,
                'type' => 'course',
            ]);
    }
    
    /**
     * Search lessons by title, description, and content
     */
    public function searchLessons(string $query, array $filters = []): Collection
    {
        $lessonsQuery = Lesson::query()
            ->with(['section.course.instructor', 'section.course'])
            ->whereHas('section.course', fn($q) => $q->where('is_published', true))
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%");
            });
        
        // Apply course-level filters
        if (!empty($filters['category_id'])) {
            $lessonsQuery->whereHas('section.course', fn($q) => 
                $q->where('category_id', $filters['category_id'])
            );
        }
        
        if (!empty($filters['level'])) {
            $lessonsQuery->whereHas('section.course', fn($q) => 
                $q->where('level', $filters['level'])
            );
        }
        
        return $lessonsQuery
            ->limit(15)
            ->get()
            ->map(fn ($lesson) => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'description' => $this->highlightMatch($lesson->description ?? $lesson->content, $query, 150),
                'course_id' => $lesson->section->course_id,
                'course_title' => $lesson->section->course->title,
                'instructor' => $lesson->section->course->instructor?->name,
                'section_title' => $lesson->section->title,
                'duration_minutes' => $lesson->duration_minutes,
                'is_free' => $lesson->is_free || $lesson->is_free_preview,
                'type' => 'lesson',
            ]);
    }
    
    /**
     * Search instructors by name, job, and city
     */
    public function searchInstructors(string $query): Collection
    {
        return User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'instructor'))
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('job', 'LIKE', "%{$query}%")
                  ->orWhere('city', 'LIKE', "%{$query}%");
            })
            ->withCount(['courses' => fn($q) => $q->where('is_published', true)])
            ->limit(10)
            ->get()
            ->map(fn ($instructor) => [
                'id' => $instructor->id,
                'name' => $instructor->name,
                'avatar' => $instructor->avatar,
                'job' => $instructor->job,
                'city' => $instructor->city,
                'courses_count' => $instructor->courses_count,
                'type' => 'instructor',
            ]);
    }
    
    /**
     * Search course questions
     */
    public function searchQuestions(string $query): Collection
    {
        return CourseQuestion::query()
            ->with(['course', 'user', 'lesson'])
            ->whereHas('course', fn($q) => $q->where('is_published', true))
            ->where(function ($q) use ($query) {
                $q->where('question', 'LIKE', "%{$query}%");
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($question) => [
                'id' => $question->id,
                'question' => $this->highlightMatch($question->question, $query, 200),
                'course_id' => $question->course_id,
                'course_title' => $question->course->title,
                'lesson_title' => $question->lesson?->title,
                'user_name' => $question->user->name,
                'answers_count' => $question->answers()->count(),
                'created_at' => $question->created_at->diffForHumans(),
                'type' => 'question',
            ]);
    }
    
    /**
     * Get search suggestions based on user history and popular searches
     */
    public function getSuggestions(?int $userId, string $partialQuery = ''): array
    {
        $suggestions = [];
        
        // User's recent searches
        if ($userId) {
            $userHistory = SearchHistory::where('user_id', $userId)
                ->when($partialQuery, fn($q) => $q->where('query', 'LIKE', "{$partialQuery}%"))
                ->orderByDesc('created_at')
                ->limit(5)
                ->pluck('query')
                ->unique()
                ->values()
                ->toArray();
            
            $suggestions['recent'] = $userHistory;
        }
        
        // Popular searches (cached)
        $popularSearches = Cache::remember('popular_searches', now()->addHours(1), function () {
            return SearchHistory::select('query', DB::raw('COUNT(*) as count'))
                ->groupBy('query')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('query')
                ->toArray();
        });
        
        if ($partialQuery) {
            $popularSearches = array_filter($popularSearches, fn($s) => 
                str_starts_with(strtolower($s), strtolower($partialQuery))
            );
        }
        
        $suggestions['popular'] = array_slice(array_values($popularSearches), 0, 5);
        
        // Course title suggestions
        if (strlen($partialQuery) >= 2) {
            $courseTitles = Course::where('is_published', true)
                ->where('title', 'LIKE', "{$partialQuery}%")
                ->limit(5)
                ->pluck('title')
                ->toArray();
            
            $suggestions['courses'] = $courseTitles;
        }
        
        return $suggestions;
    }
    
    /**
     * Clear search history for a user
     */
    public function clearUserHistory(int $userId): void
    {
        SearchHistory::where('user_id', $userId)->delete();
    }
    
    /**
     * Apply filters to course query
     */
    protected function applyFilters($query, array $filters): void
    {
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        
        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }
        
        if (isset($filters['is_free'])) {
            if ($filters['is_free']) {
                $query->where('price', 0);
            } else {
                $query->where('price', '>', 0);
            }
        }
        
        if (!empty($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }
        
        if (!empty($filters['max_price'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('offer_price', '<=', $filters['max_price'])
                  ->orWhere(function ($q) use ($filters) {
                      $q->whereNull('offer_price')
                        ->where('price', '<=', $filters['max_price']);
                  });
            });
        }
        
        if (!empty($filters['min_price'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('offer_price', '>=', $filters['min_price'])
                  ->orWhere(function ($q) use ($filters) {
                      $q->whereNull('offer_price')
                        ->where('price', '>=', $filters['min_price']);
                  });
            });
        }
        
        if (!empty($filters['instructor_id'])) {
            $query->where('user_id', $filters['instructor_id']);
        }
        
        if (!empty($filters['min_hours'])) {
            $query->where('hours', '>=', $filters['min_hours']);
        }
        
        if (!empty($filters['max_hours'])) {
            $query->where('hours', '<=', $filters['max_hours']);
        }
        
        // Sorting
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'newest':
                    $query->orderByDesc('created_at');
                    break;
                case 'rating':
                    $query->orderByDesc('rating');
                    break;
                case 'students':
                    $query->orderByDesc('students_count');
                    break;
                case 'price_asc':
                    $query->orderByRaw('COALESCE(offer_price, price) ASC');
                    break;
                case 'price_desc':
                    $query->orderByRaw('COALESCE(offer_price, price) DESC');
                    break;
            }
        }
    }
    
    /**
     * Sanitize search query to prevent SQL injection
     */
    protected function sanitizeQuery(string $query): string
    {
        // Remove special SQL characters
        $query = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $query);
        
        // Limit length
        return substr(trim($query), 0, 100);
    }
    
    /**
     * Get cache key for search results
     */
    protected function getCacheKey(string $query, array $filters): string
    {
        return 'search:' . md5($query . json_encode($filters));
    }
    
    /**
     * Highlight matching text in description
     */
    protected function highlightMatch(?string $text, string $query, int $maxLength = 200): string
    {
        if (!$text) {
            return '';
        }
        
        // Strip HTML tags
        $text = strip_tags($text);
        
        // Find the position of the match
        $pos = stripos($text, $query);
        
        if ($pos !== false) {
            // Extract context around the match
            $start = max(0, $pos - 50);
            $end = min(strlen($text), $pos + strlen($query) + 100);
            
            $excerpt = ($start > 0 ? '...' : '') . substr($text, $start, $end - $start) . ($end < strlen($text) ? '...' : '');
        } else {
            // No match found, just truncate
            $excerpt = substr($text, 0, $maxLength) . (strlen($text) > $maxLength ? '...' : '');
        }
        
        return $excerpt;
    }
    
    /**
     * Save search to history
     */
    protected function saveSearchHistory(int $userId, string $query, int $resultsCount): void
    {
        // Check if this query was recently searched by this user
        $recentSearch = SearchHistory::where('user_id', $userId)
            ->where('query', $query)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();
        
        if (!$recentSearch) {
            SearchHistory::create([
                'user_id' => $userId,
                'query' => $query,
                'results_count' => $resultsCount,
            ]);
        }
    }
    
    /**
     * Get total results count
     */
    protected function getTotalResultsCount(array $results): int
    {
        return collect($results)->sum(fn($items) => $items->count());
    }
    
    /**
     * Get empty results structure
     */
    protected function getEmptyResults(): array
    {
        return [
            'courses' => collect(),
            'lessons' => collect(),
            'instructors' => collect(),
            'questions' => collect(),
        ];
    }
}
