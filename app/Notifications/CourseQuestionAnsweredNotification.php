<?php

namespace App\Notifications;

use App\Models\CourseQuestion;
use App\Models\CourseAnswer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseQuestionAnsweredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CourseQuestion $question,
        public string $type = 'answered' // 'new_question' or 'answered'
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->type === 'new_question') {
            $questioner = $this->question->user;
            $lesson = $this->question->lesson;
            
            return (new MailMessage)
                ->subject('سؤال جديد في المحاضرة: ' . ($lesson ? $lesson->title : 'الدورة'))
                ->greeting('مرحباً ' . $notifiable->name)
                ->line('تم طرح سؤال جديد من قبل ' . $questioner->name)
                ->line('السؤال: ' . \Str::limit($this->question->question, 100))
                ->action('عرض السؤال', url('/admin/courses/' . $this->question->course_id . '/edit'))
                ->line('شكراً لاستخدامك منصة التعلم!');
        } else {
            // When instructor answers
            $answer = $this->question->answers()->latest()->first();
            $instructor = $answer ? $answer->user : null;
            $lesson = $this->question->lesson;
            
            return (new MailMessage)
                ->subject('تم الرد على سؤالك')
                ->greeting('مرحباً ' . $notifiable->name)
                ->line('تم الرد على سؤالك في المحاضرة "' . ($lesson ? $lesson->title : 'الدورة') . '"')
                ->line('الرد: ' . \Str::limit($answer->answer ?? '', 100))
                ->action('عرض الرد', url('/admin/courses/' . $this->question->course_id . '/edit'))
                ->line('شكراً لاستخدامك منصة التعلم!');
        }
    }

    public function toArray(object $notifiable): array
    {
        if ($this->type === 'new_question') {
            $questioner = $this->question->user;
            $lesson = $this->question->lesson;
            
            return [
                'type' => 'new_question',
                'title' => 'سؤال جديد',
                'message' => $questioner->name . ' طرح سؤالاً في المحاضرة: ' . ($lesson ? $lesson->title : 'الدورة'),
                'question_id' => $this->question->id,
                'course_id' => $this->question->course_id,
                'lesson_id' => $this->question->lesson_id,
            ];
        } else {
            $answer = $this->question->answers()->latest()->first();
            $lesson = $this->question->lesson;
            
            return [
                'type' => 'question_answered',
                'title' => 'تم الرد على سؤالك',
                'message' => 'تم الرد على سؤالك في المحاضرة: ' . ($lesson ? $lesson->title : 'الدورة'),
                'question_id' => $this->question->id,
                'course_id' => $this->question->course_id,
                'lesson_id' => $this->question->lesson_id,
            ];
        }
    }
}
