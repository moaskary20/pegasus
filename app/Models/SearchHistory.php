<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    protected $table = 'search_history';
    
    protected $fillable = [
        'user_id',
        'query',
        'results_count',
    ];

    protected function casts(): array
    {
        return [
            'results_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the most popular searches
     */
    public static function getPopularSearches(int $limit = 10): array
    {
        return static::select('query')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('query')
            ->toArray();
    }
    
    /**
     * Get recent searches for a user
     */
    public static function getRecentSearches(int $userId, int $limit = 10): array
    {
        return static::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->pluck('query')
            ->unique()
            ->values()
            ->toArray();
    }
}
