<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $title,
        public string $message,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('تذكير: ' . $this->title)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line($this->message);
        
        if ($this->actionUrl) {
            $mail->action($this->actionLabel ?? 'عرض', $this->actionUrl);
        }
        
        return $mail->line('شكراً لاستخدامك منصة Pegasus Academy');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reminder_' . $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'action_label' => $this->actionLabel,
        ];
    }
}
