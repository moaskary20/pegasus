<?php

namespace App\Notifications;

use App\Models\Lesson;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLessonAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lesson $lesson)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->lesson->section->course;
        
        return (new MailMessage)
            ->subject('محاضرة جديدة في الدورة: ' . $course->title)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('تم إضافة محاضرة جديدة في الدورة "' . $course->title . '"')
            ->line('المحاضرة: ' . $this->lesson->title)
            ->action('عرض المحاضرة', url('/admin/courses/' . $course->id . '/edit'))
            ->line('شكراً لاستخدامك منصة التعلم!');
    }

    public function toArray(object $notifiable): array
    {
        $course = $this->lesson->section->course;
        
        return [
            'type' => 'new_lesson',
            'title' => 'محاضرة جديدة',
            'message' => 'تم إضافة محاضرة جديدة "' . $this->lesson->title . '" في الدورة "' . $course->title . '"',
            'lesson_id' => $this->lesson->id,
            'course_id' => $course->id,
        ];
    }
}
