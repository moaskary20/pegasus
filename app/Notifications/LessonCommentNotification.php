<?php

namespace App\Notifications;

use App\Models\LessonComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LessonCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LessonComment $comment)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lesson = $this->comment->lesson;
        $commenter = $this->comment->user;
        
        return (new MailMessage)
            ->subject('تعليق جديد على المحاضرة: ' . $lesson->title)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('تم إضافة تعليق جديد على المحاضرة "' . $lesson->title . '" من قبل ' . $commenter->name)
            ->line('التعليق: ' . \Str::limit($this->comment->body, 100))
            ->action('عرض التعليق', url('/admin/courses/' . $lesson->section->course_id . '/edit'))
            ->line('شكراً لاستخدامك منصة التعلم!');
    }

    public function toArray(object $notifiable): array
    {
        $lesson = $this->comment->lesson;
        $commenter = $this->comment->user;
        
        return [
            'type' => 'lesson_comment',
            'title' => 'تعليق جديد على المحاضرة',
            'message' => $commenter->name . ' أضاف تعليقاً على المحاضرة: ' . $lesson->title,
            'lesson_id' => $lesson->id,
            'comment_id' => $this->comment->id,
            'course_id' => $lesson->section->course_id,
        ];
    }
}
