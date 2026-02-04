<?php

namespace App\Notifications;

use App\Models\AssignmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentGradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AssignmentSubmission $submission
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $passed = $this->submission->isPassed();
        
        return (new MailMessage)
            ->subject('تم تقييم واجبك: ' . $this->submission->assignment->title)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('تم تقييم واجبك في دورة ' . $this->submission->assignment->course->title)
            ->line('الواجب: ' . $this->submission->assignment->title)
            ->line('الدرجة: ' . $this->submission->score . '/' . $this->submission->assignment->max_score)
            ->line($passed ? '🎉 تهانينا! لقد اجتزت الواجب بنجاح.' : '😔 للأسف لم تصل لدرجة النجاح. حاول مرة أخرى!')
            ->action('عرض التفاصيل', route('site.my-assignments'))
            ->line('شكراً لاستخدامك منصة Pegasus Academy');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'assignment_graded',
            'title' => 'تم تقييم واجبك',
            'message' => 'حصلت على ' . $this->submission->score . '/' . $this->submission->assignment->max_score . ' في ' . $this->submission->assignment->title,
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
            'score' => $this->submission->score,
            'passed' => $this->submission->isPassed(),
        ];
    }
}
