<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param Order $order
     * @param string $recipientType 'customer' or 'instructor'
     */
    public function __construct(
        public Order $order,
        public string $recipientType = 'customer'
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->recipientType === 'instructor') {
            $courses = $this->order->items()
                ->whereHas('course', fn($q) => $q->where('user_id', $notifiable->id))
                ->with('course')
                ->get();
            
            $courseNames = $courses->pluck('course.title')->join('، ');
            $totalAmount = $courses->sum('price');
            
            return (new MailMessage)
                ->subject('عملية بيع جديدة!')
                ->greeting('مرحباً ' . $notifiable->name)
                ->line('تم شراء دورتك من قبل طالب جديد!')
                ->line('الدورات: ' . $courseNames)
                ->line('المبلغ: ' . number_format($totalAmount, 2) . ' ج.م')
                ->action('عرض الأرباح', url('/admin/earnings-report'))
                ->line('استمر في تقديم محتوى رائع!');
        }
        
        return (new MailMessage)
            ->subject('تأكيد الطلب رقم: #' . $this->order->id)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('شكراً لك على الطلب!')
            ->line('رقم الطلب: #' . $this->order->id)
            ->line('المبلغ الإجمالي: ' . number_format($this->order->total, 2) . ' ج.م')
            ->line('حالة الدفع: ' . ($this->order->payment_status === 'paid' ? 'مدفوع' : 'في انتظار الدفع'))
            ->action('عرض الدورات', url('/admin/my-courses'))
            ->line('نتمنى لك تجربة تعلم ممتعة!');
    }

    public function toArray(object $notifiable): array
    {
        if ($this->recipientType === 'instructor') {
            $courses = $this->order->items()
                ->whereHas('course', fn($q) => $q->where('user_id', $notifiable->id))
                ->with('course')
                ->get();
            
            return [
                'type' => 'new_sale',
                'title' => 'عملية بيع جديدة',
                'message' => 'تم شراء دورتك! المبلغ: ' . number_format($courses->sum('price'), 2) . ' ج.م',
                'order_id' => $this->order->id,
                'amount' => $courses->sum('price'),
            ];
        }
        
        return [
            'type' => 'order_confirmed',
            'title' => 'تأكيد الطلب',
            'message' => 'تم تأكيد طلبك رقم #' . $this->order->id . ' بمبلغ ' . number_format($this->order->total, 2) . ' ج.م',
            'order_id' => $this->order->id,
            'total' => $this->order->total,
        ];
    }
}
