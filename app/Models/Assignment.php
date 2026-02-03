<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'lesson_id',
        'course_id',
        'title',
        'description',
        'instructions',
        'type',
        'max_score',
        'passing_score',
        'due_date',
        'allow_late_submission',
        'late_penalty_percent',
        'allow_resubmission',
        'max_submissions',
        'allowed_file_types',
        'max_file_size_mb',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'allow_late_submission' => 'boolean',
            'allow_resubmission' => 'boolean',
            'is_published' => 'boolean',
            'allowed_file_types' => 'array',
        ];
    }
    
    const TYPE_ASSIGNMENT = 'assignment';
    const TYPE_PROJECT = 'project';
    
    public static function getTypes(): array
    {
        return [
            self::TYPE_ASSIGNMENT => 'واجب',
            self::TYPE_PROJECT => 'مشروع',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
    
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
    
    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
    
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }
    
    public function canSubmit(User $user): bool
    {
        // Check if user is enrolled
        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $this->course_id)
            ->exists();
        
        if (!$isEnrolled) {
            return false;
        }
        
        // Check if past due date and late submissions not allowed
        if ($this->isOverdue() && !$this->allow_late_submission) {
            return false;
        }
        
        // Check max submissions
        if ($this->max_submissions) {
            $submissionCount = $this->submissions()
                ->where('user_id', $user->id)
                ->count();
            
            if ($submissionCount >= $this->max_submissions) {
                return false;
            }
        }
        
        // Check if resubmission is needed
        $lastSubmission = $this->submissions()
            ->where('user_id', $user->id)
            ->latest()
            ->first();
        
        if ($lastSubmission && !$this->allow_resubmission && $lastSubmission->status === 'graded') {
            return false;
        }
        
        return true;
    }
    
    public function getSubmissionStats(): array
    {
        $submissions = $this->submissions;
        
        return [
            'total' => $submissions->count(),
            'graded' => $submissions->where('status', 'graded')->count(),
            'pending' => $submissions->where('status', 'submitted')->count(),
            'average_score' => $submissions->where('status', 'graded')->avg('score') ?? 0,
            'passed' => $submissions->where('status', 'graded')
                ->where('score', '>=', $this->passing_score)->count(),
        ];
    }
}
