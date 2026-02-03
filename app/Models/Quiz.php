<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'lesson_id',
        'question_bank_id',
        'questions_count',
        'randomize_questions',
        'title',
        'description',
        'duration_minutes',
        'pass_percentage',
        'allow_retake',
        'max_attempts',
    ];

    protected function casts(): array
    {
        return [
            'pass_percentage' => 'decimal:2',
            'allow_retake' => 'boolean',
            'randomize_questions' => 'boolean',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }
}
