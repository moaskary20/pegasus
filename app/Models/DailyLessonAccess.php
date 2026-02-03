<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLessonAccess extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_id',
        'purchased_at',
        'expires_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function isValid(): bool
    {
        return now() <= $this->expires_at;
    }
}
