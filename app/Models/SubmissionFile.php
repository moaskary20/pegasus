<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SubmissionFile extends Model
{
    protected $fillable = [
        'submission_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(AssignmentSubmission::class, 'submission_id');
    }
    
    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }
    
    public function getFormattedSize(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }
    
    public function getIconClass(): string
    {
        return match($this->file_type) {
            'pdf' => 'text-red-500',
            'doc', 'docx' => 'text-blue-500',
            'xls', 'xlsx' => 'text-green-500',
            'ppt', 'pptx' => 'text-orange-500',
            'zip', 'rar' => 'text-purple-500',
            'jpg', 'jpeg', 'png', 'gif' => 'text-pink-500',
            default => 'text-gray-500',
        };
    }
}
