<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param Enrollment $enrollment
     * @param string $recipientType 'student' or 'instructor'
     */
    public function __construct(
        public Enrollment $enrollment,
        public string $recipientType = 'student'
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->enrollment->course;
        $student = $this->enrollment->user;
        
        if ($this->recipientType === 'instructor') {
            return (new MailMessage)
                ->subject('تسجيل جديد في دورتك: ' . $course->title)
                ->greeting('مرحباً ' . $notifiable->name)
                ->line('قام طالب جديد بالتسجيل في دورتك!')
                ->line('الطالب: ' . $student->name)
                ->line('الدورة: ' . $course->title)
                ->action('عرض الدورة', url('/admin/courses/' . $course->id . '/edit'))
                ->line('شكراً لك على تقديم محتوى تعليمي قيم!');
        }
        
        return (new MailMessage)
            ->subject('تم تسجيلك في الدورة: ' . $course->title)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('تم تسجيلك بنجاح في الدورة: ' . $course->title)
            ->line('المدرس: ' . $course->instructor?->name)
            ->action('ابدأ التعلم الآن', url('/admin/view-course/' . $course->id))
            ->line('نتمنى لك رحلة تعلم ممتعة!');
    }

    public function toArray(object $notifiable): array
    {
        $course = $this->enrollment->course;
        $student = $this->enrollment->user;
        
        if ($this->recipientType === 'instructor') {
            return [
                'type' => 'new_enrollment',
                'title' => 'تسجيل طالب جديد',
                'message' => 'قام ' . $student->name . ' بالتسجيل في دورة "' . $course->title . '"',
                'course_id' => $course->id,
                'student_id' => $student->id,
                'enrollment_id' => $this->enrollment->id,
            ];
        }
        
        return [
            'type' => 'enrollment_confirmed',
            'title' => 'تم التسجيل بنجاح',
            'message' => 'تم تسجيلك في دورة "' . $course->title . '" بنجاح',
            'course_id' => $course->id,
            'enrollment_id' => $this->enrollment->id,
        ];
    }
}
