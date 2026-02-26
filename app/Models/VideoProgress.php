<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoProgress extends Model
{
    protected $table = 'video_progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'last_position_seconds',
        'completed',
        'watch_time_minutes',
        'last_watched_at',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'last_watched_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
