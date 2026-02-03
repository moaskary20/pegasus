<?php

namespace App\Observers;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Notifications\CourseCompletedNotification;
use App\Notifications\EnrollmentNotification;
use App\Services\CertificateService;
use App\Services\PointsService;
use Illuminate\Support\Facades\Log;

class EnrollmentObserver
{
    protected CertificateService $certificateService;
    protected PointsService $pointsService;
    
    public function __construct(CertificateService $certificateService, PointsService $pointsService)
    {
        $this->certificateService = $certificateService;
        $this->pointsService = $pointsService;
    }
    
    /**
     * Handle the Enrollment "created" event.
     */
    public function created(Enrollment $enrollment): void
    {
        try {
            // Load relationships
            $enrollment->load(['user', 'course.instructor']);
            
            // Notify the student about successful enrollment
            if ($enrollment->user) {
                $enrollment->user->notify(new EnrollmentNotification($enrollment, 'student'));
            }
            
            // Notify the instructor about new student
            if ($enrollment->course?->instructor) {
                $enrollment->course->instructor->notify(new EnrollmentNotification($enrollment, 'instructor'));
            }
            
            Log::info('Enrollment notifications sent', [
                'enrollment_id' => $enrollment->id,
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send enrollment notifications', [
                'error' => $e->getMessage(),
                'enrollment_id' => $enrollment->id,
            ]);
        }
    }
    
    /**
     * Handle the Enrollment "updated" event.
     */
    public function updated(Enrollment $enrollment): void
    {
        // Check if course is completed (completed_at is set and wasn't set before)
        $originalCompletedAt = $enrollment->getOriginal('completed_at');
        $currentCompletedAt = $enrollment->completed_at;
        
        // Only create certificate if completed_at changed from null to a date
        if ($currentCompletedAt && !$originalCompletedAt) {
            $this->createCertificate($enrollment);
            $this->sendCompletionNotification($enrollment);
            $this->awardCourseCompletionPoints($enrollment);
        }
    }
    
    /**
     * Award points for course completion
     */
    protected function awardCourseCompletionPoints(Enrollment $enrollment): void
    {
        try {
            $enrollment->load(['user', 'course']);
            
            if ($enrollment->user && $enrollment->course) {
                $this->pointsService->awardCourseCompleted($enrollment->user, $enrollment->course);
                
                Log::info('Course completion points awarded', [
                    'user_id' => $enrollment->user_id,
                    'course_id' => $enrollment->course_id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to award course completion points', [
                'error' => $e->getMessage(),
                'enrollment_id' => $enrollment->id,
            ]);
        }
    }
    
    /**
     * Send course completion notification
     */
    protected function sendCompletionNotification(Enrollment $enrollment): void
    {
        try {
            $enrollment->load('user');
            
            if ($enrollment->user) {
                $enrollment->user->notify(new CourseCompletedNotification($enrollment));
                
                Log::info('Course completion notification sent', [
                    'user_id' => $enrollment->user_id,
                    'course_id' => $enrollment->course_id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send completion notification', [
                'error' => $e->getMessage(),
                'enrollment_id' => $enrollment->id,
            ]);
        }
    }
    
    /**
     * Create certificate for completed enrollment
     */
    protected function createCertificate(Enrollment $enrollment): void
    {
        try {
            // Check if certificate already exists
            $existingCertificate = Certificate::where('user_id', $enrollment->user_id)
                ->where('course_id', $enrollment->course_id)
                ->first();
            
            if ($existingCertificate) {
                Log::info('Certificate already exists for enrollment', [
                    'user_id' => $enrollment->user_id,
                    'course_id' => $enrollment->course_id,
                ]);
                return;
            }
            
            // Create new certificate
            $certificate = Certificate::create([
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'issued_at' => now(),
            ]);
            
            // Generate and save PDF
            $pdfPath = $this->certificateService->saveCertificatePdf($certificate);
            
            // Update certificate with PDF path
            $certificate->update([
                'pdf_path' => $pdfPath,
            ]);
            
            Log::info('Certificate created successfully', [
                'certificate_id' => $certificate->id,
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create certificate', [
                'error' => $e->getMessage(),
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
            ]);
        }
    }
}
