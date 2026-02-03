<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Message $message)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sender = $this->message->user;
        $conversation = $this->message->conversation;
        
        $conversationName = $conversation->type === 'private'
            ? $sender->name
            : $conversation->name;
        
        return (new MailMessage)
            ->subject('رسالة جديدة من ' . $sender->name)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('لديك رسالة جديدة من ' . $sender->name)
            ->line('المحادثة: ' . $conversationName)
            ->line('الرسالة: ' . \Str::limit($this->message->body, 100))
            ->action('عرض المحادثة', url('/admin/conversation/' . $conversation->id))
            ->line('شكراً لاستخدامك منصة Pegasus Academy!');
    }

    public function toArray(object $notifiable): array
    {
        $sender = $this->message->user;
        $conversation = $this->message->conversation;
        
        return [
            'type' => 'new_message',
            'title' => 'رسالة جديدة من ' . $sender->name,
            'message' => \Str::limit($this->message->body, 100) ?: 'مرفق',
            'conversation_id' => $conversation->id,
            'message_id' => $this->message->id,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'sender_avatar' => $sender->avatar,
        ];
    }
}
