<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionBankQuestion extends Model
{
    protected $fillable = [
        'question_bank_id',
        'type',
        'question_text',
        'options',
        'correct_answer',
        'explanation',
        'points',
        'difficulty',
        'tags',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'correct_answer' => 'array',
            'tags' => 'array',
        ];
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }
}
