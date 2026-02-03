<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseQuestion extends Model
{
    protected $fillable = [
        'course_id',
        'user_id',
        'lesson_id',
        'question',
        'is_answered',
    ];

    protected function casts(): array
    {
        return [
            'is_answered' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CourseAnswer::class);
    }
}
