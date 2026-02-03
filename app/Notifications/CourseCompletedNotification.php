<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Enrollment $enrollment)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->enrollment->course;
        $certificate = $this->enrollment->user->certificates()
            ->where('course_id', $course->id)
            ->first();
        
        $mailMessage = (new MailMessage)
            ->subject('تهانينا! أتممت دورة: ' . $course->title)
            ->greeting('تهانينا ' . $notifiable->name . '! 🎉')
            ->line('لقد أتممت بنجاح جميع محاضرات دورة "' . $course->title . '"')
            ->line('المدرس: ' . $course->instructor?->name);
        
        if ($certificate) {
            $mailMessage->line('تم إصدار شهادة إتمام لك!')
                ->action('عرض الشهادة', url('/admin/my-certificates'));
        } else {
            $mailMessage->action('عرض الدورة', url('/admin/view-course/' . $course->id));
        }
        
        return $mailMessage->line('استمر في رحلة التعلم!');
    }

    public function toArray(object $notifiable): array
    {
        $course = $this->enrollment->course;
        $certificate = $this->enrollment->user->certificates()
            ->where('course_id', $course->id)
            ->first();
        
        return [
            'type' => 'course_completed',
            'title' => 'تهانينا! أتممت الدورة',
            'message' => 'لقد أتممت دورة "' . $course->title . '" بنجاح' . ($certificate ? ' وتم إصدار شهادتك' : ''),
            'course_id' => $course->id,
            'enrollment_id' => $this->enrollment->id,
            'certificate_id' => $certificate?->id,
        ];
    }
}
