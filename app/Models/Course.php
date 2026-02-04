<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    protected $fillable = [
        'user_id',
        'cover_image',
        'preview_video_path',
        'preview_youtube_url',
        'preview_lesson_id',
        'title',
        'slug',
        'description',
        'announcement',
        'objectives',
        'hours',
        'lectures_count',
        'level',
        'price',
        'offer_price',
        'price_once',
        'price_monthly',
        'price_daily',
        'category_id',
        'sub_category_id',
        'is_published',
        'students_count',
        'rating',
        'reviews_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'offer_price' => 'decimal:2',
            'price_once' => 'decimal:2',
            'price_monthly' => 'decimal:2',
            'price_daily' => 'decimal:2',
            'rating' => 'decimal:2',
            'is_published' => 'boolean',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('sort_order');
    }

    public function lessons(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Section::class)
            ->orderBy('lessons.sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(CourseRating::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CourseQuestion::class);
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class);
    }

    public function earnings(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(InstructorEarning::class);
    }
    
    /**
     * Scope to search courses by multiple fields
     */
    public function scopeSearch($query, string $search)
    {
        $search = trim($search);
        
        if (strlen($search) < 2) {
            return $query;
        }
        
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%")
              ->orWhere('objectives', 'LIKE', "%{$search}%")
              ->orWhereHas('instructor', fn($q) => $q->where('name', 'LIKE', "%{$search}%"))
              ->orWhereHas('category', fn($q) => $q->where('name', 'LIKE', "%{$search}%"))
              ->orWhereHas('lessons', fn($q) => $q->where('title', 'LIKE', "%{$search}%"));
        });
    }
    
    /**
     * Scope to filter published courses only
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
    
    /**
     * Scope to filter by category
     */
    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
    
    /**
     * Scope to filter by level
     */
    public function scopeOfLevel($query, string $level)
    {
        return $query->where('level', $level);
    }
    
    /**
     * Scope to filter free courses
     */
    public function scopeFree($query)
    {
        return $query->where('price', 0);
    }
    
    /**
     * Scope to filter paid courses
     */
    public function scopePaid($query)
    {
        return $query->where('price', '>', 0);
    }
    
    /**
     * Scope to filter by minimum rating
     */
    public function scopeMinRating($query, float $rating)
    {
        return $query->where('rating', '>=', $rating);
    }
    
    /**
     * Get the full URL for cover image
     */
    public function getCoverImageAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        if (str_starts_with($value, 'http')) {
            return $value;
        }
        return asset('storage/' . ltrim($value, '/'));
    }

    public function previewLesson(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'preview_lesson_id');
    }

    /**
     * Check if course has YouTube preview
     */
    public function isPreviewYoutube(): bool
    {
        return !empty($this->getPreviewYoutubeVideoId());
    }

    /**
     * Extract YouTube video ID from preview_youtube_url
     */
    public function getPreviewYoutubeVideoId(): ?string
    {
        $url = trim((string) ($this->preview_youtube_url ?? ''));
        if (empty($url) && $this->relationLoaded('previewLesson') && $this->previewLesson?->youtube_url) {
            $url = $this->previewLesson->youtube_url;
        }
        if (empty($url)) {
            return null;
        }
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Get YouTube embed URL for preview
     */
    public function getPreviewYoutubeEmbedUrlAttribute(): ?string
    {
        $id = $this->getPreviewYoutubeVideoId();
        return $id ? 'https://www.youtube.com/embed/' . $id : null;
    }

    /**
     * Get the full URL for preview video (preview lesson, youtube, or file)
     */
    public function getPreviewVideoUrlAttribute(): ?string
    {
        $lesson = $this->previewLesson ?? $this->previewLesson()->first();
        if ($lesson && ($lesson->video_url || $lesson->youtube_url)) {
            if ($lesson->isYoutubeVideo()) {
                return $lesson->youtube_embed_url;
            }
            return $lesson->video_url;
        }
        if ($this->isPreviewYoutube()) {
            return $this->preview_youtube_embed_url;
        }
        $value = $this->attributes['preview_video_path'] ?? null;
        if (empty($value)) {
            return null;
        }
        if (str_starts_with($value, 'http')) {
            return $value;
        }
        return asset('storage/' . ltrim($value, '/'));
    }

    /**
     * Check if preview is YouTube (from course or preview lesson)
     */
    public function isPreviewVideoYoutube(): bool
    {
        $lesson = $this->previewLesson ?? $this->previewLesson()->first();
        if ($lesson && $lesson->isYoutubeVideo()) {
            return true;
        }
        return $this->isPreviewYoutube();
    }

    /**
     * Get the effective price (offer price or regular price)
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->offer_price ?? $this->price;
    }
    
    /**
     * Get the slug attribute, generating from title if missing
     */
    public function getSlugAttribute(): string
    {
        if (empty($this->attributes['slug'])) {
            return Str::slug($this->attributes['title'] ?? 'course');
        }
        return $this->attributes['slug'];
    }
    
    /**
     * Get price for a specific subscription type
     */
    public function getPriceForSubscriptionType(string $type): float
    {
        return match ($type) {
            'once' => (float) ($this->price_once ?? $this->price ?? 0),
            'monthly' => (float) ($this->price_monthly ?? $this->price ?? 0),
            'daily' => (float) ($this->price_daily ?? $this->price ?? 0),
            default => (float) ($this->price ?? 0),
        };
    }
    
    /**
     * Get subscription type label
     */
    public function getSubscriptionTypeLabelAttribute(): string
    {
        return match ($this->subscription_type ?? 'once') {
            'once' => 'اشتراك واحد (120 يوم)',
            'monthly' => 'اشتراك شهري',
            'daily' => 'اشتراك يومي (درس واحد)',
            default => 'اشتراك واحد',
        };
    }
}
