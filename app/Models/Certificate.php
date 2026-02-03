<?php

namespace App\Models;

use App\Services\CertificateService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'uuid',
        'pdf_path',
        'issued_at',
        'intro_text',
        'completion_text',
        'award_text',
        'director_name',
        'academic_director_name',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($certificate) {
            if (empty($certificate->uuid)) {
                $certificate->uuid = (string) Str::uuid();
            }
            if (empty($certificate->issued_at)) {
                $certificate->issued_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
    
    /**
     * Generate PDF for this certificate
     */
    public function generatePdf(): string
    {
        $service = app(CertificateService::class);
        return $service->generatePdf($this);
    }
    
    /**
     * Get PDF download URL
     */
    public function getPdfUrl(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }
        
        return asset('storage/' . $this->pdf_path);
    }
}
