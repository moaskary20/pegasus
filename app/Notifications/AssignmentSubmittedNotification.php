<?php

namespace App\Notifications;

use App\Models\AssignmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentSubmittedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('تسليم واجب جديد: ' . $this->submission->assignment->title)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('قام الطالب ' . $this->submission->user->name . ' بتسليم واجب جديد.')
            ->line('الواجب: ' . $this->submission->assignment->title)
            ->line('الدورة: ' . $this->submission->assignment->course->title)
            ->action('عرض التسليم', url('/admin/resources/assignments/' . $this->submission->assignment_id))
            ->line('شكراً لاستخدامك منصة Pegasus Academy');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'assignment_submitted',
            'title' => 'تسليم واجب جديد',
            'message' => 'قام ' . $this->submission->user->name . ' بتسليم واجب: ' . $this->submission->assignment->title,
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
        ];
    }
}
