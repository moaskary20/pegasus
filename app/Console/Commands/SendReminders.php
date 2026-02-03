<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use App\Models\CourseQuestion;
use App\Models\Enrollment;
use App\Models\Message;
use App\Models\Reminder;
use App\Models\ReminderSetting;
use App\Models\User;
use App\Models\VideoProgress;
use App\Notifications\ReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendReminders extends Command
{
    protected $signature = 'reminders:send {--type=all : Type of reminders to send (all, quiz, lesson, coupon, etc.)}';
    
    protected $description = 'Send reminder notifications to users';

    public function handle(): int
    {
        $type = $this->option('type');
        
        $this->info('Sending reminders...');
        
        if ($type === 'all' || $type === 'lesson') {
            $this->sendLessonReminders();
        }
        
        if ($type === 'all' || $type === 'coupon') {
            $this->sendCouponReminders();
        }
        
        if ($type === 'all' || $type === 'question') {
            $this->sendQuestionReminders();
        }
        
        $this->info('Reminders sent successfully!');
        
        return Command::SUCCESS;
    }
    
    /**
     * Send reminders for incomplete courses
     */
    protected function sendLessonReminders(): void
    {
        $this->info('Checking lesson reminders...');
        
        // Find users with incomplete courses who haven't been active for 3+ days
        $enrollments = Enrollment::whereNull('completed_at')
            ->where('progress_percentage', '>', 0)
            ->where('progress_percentage', '<', 100)
            ->with(['user', 'course'])
            ->get();
        
        $sentCount = 0;
        
        foreach ($enrollments as $enrollment) {
            if (!$this->isReminderEnabled($enrollment->user_id, Reminder::TYPE_LESSON)) {
                continue;
            }
            
            // Check last activity
            $lastActivity = VideoProgress::where('user_id', $enrollment->user_id)
                ->whereHas('lesson.section', function ($q) use ($enrollment) {
                    $q->where('course_id', $enrollment->course_id);
                })
                ->max('last_watched_at');
            
            if ($lastActivity && Carbon::parse($lastActivity)->gte(now()->subDays(3))) {
                continue; // User was active recently
            }
            
            // Check if reminder was already sent recently
            $recentReminder = Reminder::where('user_id', $enrollment->user_id)
                ->where('type', Reminder::TYPE_LESSON)
                ->where('remindable_id', $enrollment->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();
            
            if ($recentReminder) {
                continue;
            }
            
            // Send notification
            $enrollment->user->notify(new ReminderNotification(
                type: Reminder::TYPE_LESSON,
                title: 'أكمل تعلمك!',
                message: "تقدمك في \"{$enrollment->course->title}\" هو {$enrollment->progress_percentage}% - عد لإكمال الدورة!",
                actionUrl: url('/admin/resources/courses/' . $enrollment->course_id),
                actionLabel: 'متابعة التعلم'
            ));
            
            // Record the reminder
            Reminder::create([
                'user_id' => $enrollment->user_id,
                'type' => Reminder::TYPE_LESSON,
                'title' => 'أكمل تعلمك',
                'message' => "تذكير بإكمال دورة {$enrollment->course->title}",
                'remindable_type' => Enrollment::class,
                'remindable_id' => $enrollment->id,
            ]);
            
            $sentCount++;
        }
        
        $this->info("Sent {$sentCount} lesson reminders.");
    }
    
    /**
     * Send reminders for expiring coupons
     */
    protected function sendCouponReminders(): void
    {
        $this->info('Checking coupon reminders...');
        
        $expiringCoupons = Coupon::where('is_active', true)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(3)])
            ->get();
        
        if ($expiringCoupons->isEmpty()) {
            $this->info('No expiring coupons found.');
            return;
        }
        
        // Notify admins
        $admins = User::role('admin')->get();
        $sentCount = 0;
        
        foreach ($expiringCoupons as $coupon) {
            $daysLeft = now()->diffInDays($coupon->expires_at);
            
            foreach ($admins as $admin) {
                if (!$this->isReminderEnabled($admin->id, Reminder::TYPE_COUPON)) {
                    continue;
                }
                
                // Check if reminder was already sent
                $recentReminder = Reminder::where('user_id', $admin->id)
                    ->where('type', Reminder::TYPE_COUPON)
                    ->where('remindable_id', $coupon->id)
                    ->where('created_at', '>=', now()->subDay())
                    ->exists();
                
                if ($recentReminder) {
                    continue;
                }
                
                $admin->notify(new ReminderNotification(
                    type: Reminder::TYPE_COUPON,
                    title: 'كوبون قارب على الانتهاء',
                    message: "الكوبون \"{$coupon->code}\" سينتهي خلال {$daysLeft} يوم",
                    actionUrl: url('/admin/resources/coupons/' . $coupon->id . '/edit'),
                    actionLabel: 'عرض الكوبون'
                ));
                
                Reminder::create([
                    'user_id' => $admin->id,
                    'type' => Reminder::TYPE_COUPON,
                    'title' => 'كوبون قارب على الانتهاء',
                    'message' => "الكوبون {$coupon->code} سينتهي قريباً",
                    'remindable_type' => Coupon::class,
                    'remindable_id' => $coupon->id,
                ]);
                
                $sentCount++;
            }
        }
        
        $this->info("Sent {$sentCount} coupon reminders.");
    }
    
    /**
     * Send reminders for unanswered questions
     */
    protected function sendQuestionReminders(): void
    {
        $this->info('Checking question reminders...');
        
        // Find unanswered questions older than 24 hours
        $questions = CourseQuestion::where('is_answered', false)
            ->where('created_at', '<=', now()->subDay())
            ->with(['course', 'user'])
            ->get();
        
        if ($questions->isEmpty()) {
            $this->info('No pending questions found.');
            return;
        }
        
        $sentCount = 0;
        
        foreach ($questions as $question) {
            $instructor = $question->course->instructor;
            
            if (!$instructor || !$this->isReminderEnabled($instructor->id, Reminder::TYPE_QUESTION)) {
                continue;
            }
            
            // Check if reminder was already sent
            $recentReminder = Reminder::where('user_id', $instructor->id)
                ->where('type', Reminder::TYPE_QUESTION)
                ->where('remindable_id', $question->id)
                ->where('created_at', '>=', now()->subDay())
                ->exists();
            
            if ($recentReminder) {
                continue;
            }
            
            $instructor->notify(new ReminderNotification(
                type: Reminder::TYPE_QUESTION,
                title: 'سؤال بانتظار إجابتك',
                message: "الطالب {$question->user->name} ينتظر إجابتك على سؤال في دورة {$question->course->title}",
                actionUrl: url('/admin/resources/course-questions'),
                actionLabel: 'عرض السؤال'
            ));
            
            Reminder::create([
                'user_id' => $instructor->id,
                'type' => Reminder::TYPE_QUESTION,
                'title' => 'سؤال بانتظار إجابة',
                'message' => "سؤال من {$question->user->name}",
                'remindable_type' => CourseQuestion::class,
                'remindable_id' => $question->id,
            ]);
            
            $sentCount++;
        }
        
        $this->info("Sent {$sentCount} question reminders.");
    }
    
    /**
     * Check if reminder type is enabled for user
     */
    protected function isReminderEnabled(int $userId, string $type): bool
    {
        $setting = ReminderSetting::where('user_id', $userId)
            ->where('type', $type)
            ->first();
        
        return $setting ? $setting->enabled : true; // Default to enabled
    }
}
