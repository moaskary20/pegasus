<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentComment extends Model
{
    protected $fillable = [
        'submission_id',
        'user_id',
        'parent_id',
        'content',
        'is_private',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(AssignmentSubmission::class, 'submission_id');
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AssignmentComment::class, 'parent_id');
    }
    
    public function replies(): HasMany
    {
        return $this->hasMany(AssignmentComment::class, 'parent_id')->orderBy('created_at');
    }
}
