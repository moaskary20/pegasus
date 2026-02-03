<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBank extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'title',
        'description',
        'is_active',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tags' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionBankQuestion::class)->orderBy('sort_order');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
}
