<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'section_id',
        'title',
        'description',
        'video_path',
        'youtube_url',
        'image_path',
        'content',
        'content_type',
        'duration_minutes',
        'sort_order',
        'is_free_preview',
        'is_free',
        'can_unlock_without_completion',
        'has_zoom_meeting',
        'zoom_scheduled_time',
        'zoom_duration',
        'zoom_password',
        'zoom_link',
    ];

    protected function casts(): array
    {
        return [
            'is_free_preview' => 'boolean',
            'is_free' => 'boolean',
            'can_unlock_without_completion' => 'boolean',
            'has_zoom_meeting' => 'boolean',
            'zoom_scheduled_time' => 'datetime',
            'zoom_duration' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function video(): HasOne
    {
        return $this->hasOne(Video::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(LessonFile::class);
    }

    public function videoProgress(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VideoProgress::class);
    }

    public function quiz(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LessonComment::class)->whereNull('parent_id');
    }

    public function allComments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LessonComment::class);
    }

    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CourseQuestion::class);
    }
    
    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function zoomMeeting(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ZoomMeeting::class);
    }
    
    /**
     * Scope to search lessons by title, description, and content
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
              ->orWhere('content', 'LIKE', "%{$search}%");
        });
    }
    
    /**
     * Scope to filter lessons from published courses only
     */
    public function scopeFromPublishedCourses($query)
    {
        return $query->whereHas('section.course', fn($q) => $q->where('is_published', true));
    }
    
    /**
     * Check if lesson has YouTube video
     */
    public function isYoutubeVideo(): bool
    {
        return !empty($this->youtube_url) && $this->getYoutubeVideoId() !== null;
    }

    /**
     * Extract YouTube video ID from URL
     * Supports: youtube.com/watch?v=ID, youtu.be/ID, youtube.com/embed/ID
     */
    public function getYoutubeVideoId(): ?string
    {
        $url = trim((string) ($this->youtube_url ?? ''));
        if (empty($url)) {
            return null;
        }
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Get YouTube embed URL
     */
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        $id = $this->getYoutubeVideoId();
        return $id ? 'https://www.youtube.com/embed/' . $id : null;
    }

    /**
     * Get the video URL for playback (from video relation or video_path)
     * For YouTube use youtube_embed_url and isYoutubeVideo()
     */
    public function getVideoUrlAttribute(): ?string
    {
        if ($this->isYoutubeVideo()) {
            return $this->youtube_embed_url;
        }
        if ($this->video) {
            if ($this->video->hls_path) {
                return $this->video->hls_path;
            }
            if ($this->video->path) {
                return asset('storage/' . ltrim($this->video->path, '/'));
            }
        }
        if ($this->video_path) {
            return asset('storage/' . ltrim($this->video_path, '/'));
        }
        return null;
    }

    /**
     * Check if user can watch this lesson (enrolled or free preview)
     */
    public function canWatch(?bool $isEnrolled): bool
    {
        if ($isEnrolled) {
            return true;
        }
        return (bool) ($this->is_free ?? false) || (bool) ($this->is_free_preview ?? false);
    }

    /**
     * Scope to filter free lessons
     */
    public function scopeFreePreview($query)
    {
        return $query->where(fn($q) => $q->where('is_free', true)->orWhere('is_free_preview', true));
    }
    
    /**
     * Get the course this lesson belongs to
     */
    public function getCourseAttribute()
    {
        return $this->section?->course;
    }
}
