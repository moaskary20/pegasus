<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'user_id',
        'content',
        'status',
        'score',
        'feedback',
        'submitted_at',
        'graded_at',
        'graded_by',
        'is_late',
        'attempt_number',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
            'is_late' => 'boolean',
        ];
    }
    
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_GRADED = 'graded';
    const STATUS_RETURNED = 'returned';
    const STATUS_RESUBMIT = 'resubmit_requested';
    
    public static function getStatuses(): array
    {
        return [
            self::STATUS_SUBMITTED => 'تم التسليم',
            self::STATUS_GRADED => 'تم التقييم',
            self::STATUS_RETURNED => 'مُعاد',
            self::STATUS_RESUBMIT => 'مطلوب إعادة التسليم',
        ];
    }
    
    public static function getStatusColor(string $status): string
    {
        return match($status) {
            self::STATUS_SUBMITTED => 'warning',
            self::STATUS_GRADED => 'success',
            self::STATUS_RETURNED => 'info',
            self::STATUS_RESUBMIT => 'danger',
            default => 'gray',
        };
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
    
    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class, 'submission_id');
    }
    
    public function comments(): HasMany
    {
        return $this->hasMany(AssignmentComment::class, 'submission_id')->orderBy('created_at');
    }
    
    public function isPassed(): bool
    {
        if ($this->status !== self::STATUS_GRADED || $this->score === null) {
            return false;
        }
        
        return $this->score >= $this->assignment->passing_score;
    }
    
    public function getScorePercentage(): float
    {
        if ($this->score === null) {
            return 0;
        }
        
        return ($this->score / $this->assignment->max_score) * 100;
    }
    
    public function calculateFinalScore(): int
    {
        if ($this->score === null) {
            return 0;
        }
        
        $score = $this->score;
        
        // Apply late penalty if applicable
        if ($this->is_late && $this->assignment->late_penalty_percent > 0) {
            $penalty = ($score * $this->assignment->late_penalty_percent) / 100;
            $score = max(0, $score - $penalty);
        }
        
        return (int) round($score);
    }
}
