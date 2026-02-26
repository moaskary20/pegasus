<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Console\Command;

/**
 * تجربة الإشعارات والرسائل — ينشئ بيانات تجريبية للاختبار في التطبيق
 *
 * الاستخدام:
 *   php artisan test:notifications-messages
 *   php artisan test:notifications-messages --email=user@example.com
 */
class TestNotificationsAndMessages extends Command
{
    protected $signature = 'test:notifications-messages 
                            {--email= : بريد المستخدم الذي سيستلم الإشعارات والمحادثة}
                            {--count=3 : عدد الإشعارات التجريبية}';

    protected $description = 'إنشاء إشعارات ورسائل تجريبية للاختبار في تطبيق الموبايل';

    public function handle(): int
    {
        $email = $this->option('email');
        $count = (int) $this->option('count');

        $user1 = $email
            ? User::where('email', $email)->first()
            : User::orderBy('id')->first();

        if (!$user1) {
            $this->error($email ? "لم يُعثر على مستخدم بالبريد: {$email}" : 'لا يوجد مستخدمين في النظام.');
            return 1;
        }

        $user2 = User::where('id', '!=', $user1->id)->orderBy('id')->first();
        if (!$user2) {
            $this->warn('يوجد مستخدم واحد فقط. سيتم إنشاء الإشعارات فقط بدون محادثة.');
        }

        // 1. إشعارات تجريبية
        $this->info('جاري إنشاء إشعارات تجريبية...');
        $samples = [
            ['title' => 'مرحباً بك في أكاديمية بيغاسوس', 'message' => 'نتمنى لك رحلة تعليمية ممتعة ومفيدة.'],
            ['title' => 'تذكير: لديك اختبار قادم', 'message' => 'اختبار الدرس الأول متاح الآن. ادخل وأكمل الاختبار.'],
            ['title' => 'دورة جديدة متاحة', 'message' => 'تم إضافة دورة "أساسيات البرمجة" — سجّل الآن واستفد من العرض.'],
        ];

        for ($i = 0; $i < min($count, count($samples)); $i++) {
            $user1->notify(new TestNotification(
                $samples[$i]['title'],
                $samples[$i]['message'],
                'test_' . ($i + 1)
            ));
        }

        $this->info('تم إرسال ' . min($count, count($samples)) . ' إشعارات تجريبية إلى: ' . $user1->email);

        // 2. محادثة ورسائل تجريبية
        if ($user2) {
            $this->info('جاري إنشاء محادثة تجريبية...');

            $conversation = Conversation::getOrCreatePrivate($user1->id, $user2->id);

            $messages = [
                ['user' => $user2, 'body' => 'مرحباً! أود الاستفسار عن الدورة المتاحة.'],
                ['user' => $user1, 'body' => 'أهلاً بك، كيف يمكنني مساعدتك؟'],
                ['user' => $user2, 'body' => 'ما هي متطلبات الاشتراك في دورة البرمجة؟'],
                ['user' => $user1, 'body' => 'لا يوجد متطلبات مسبقة، يمكنك البدء مباشرة.'],
            ];

            foreach ($messages as $msg) {
                Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $msg['user']->id,
                    'body' => $msg['body'],
                    'type' => Message::TYPE_TEXT,
                ]);
            }

            // لا نحدّث last_read_at حتى تظهر الرسائل كغير مقروءة
            $this->info('تم إنشاء محادثة تجريبية بين ' . $user1->name . ' و ' . $user2->name . '.');
            $this->line('  المحادثة رقم: ' . $conversation->id);
        }

        $this->newLine();
        $this->info('تم الانتهاء. سجّل الدخول في التطبيق كـ: ' . $user1->email);
        $this->line('لعرض الإشعارات: الإشعارات والتنبيهات');
        $this->line('لعرض الرسائل: الرسائل');

        return 0;
    }
}
