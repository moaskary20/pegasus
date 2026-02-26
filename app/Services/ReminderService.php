<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Conversation;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\CourseQuestion;
use App\Models\CourseRating;
use App\Models\Enrollment;
use App\Models\Message;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Reminder;
use App\Models\ReminderSetting;
use App\Models\User;
use App\Models\VideoProgress;
use Illuminate\Support\Collection;

class ReminderService
{
    /**
     * Generate all reminders for a user
     */
    public function generateReminders(User $user): Collection
    {
        $reminders = collect();
        
        // Check user settings and generate enabled reminders
        $settings = $this->getUserSettings($user);
        
        if ($settings['quiz'] ?? true) {
            $reminders = $reminders->merge($this->getQuizReminders($user));
        }
        
        if ($settings['message'] ?? true) {
            $reminders = $reminders->merge($this->getMessageReminders($user));
        }
        
        if ($settings['lesson'] ?? true) {
            $reminders = $reminders->merge($this->getLessonReminders($user));
        }
        
        if ($settings['coupon'] ?? true) {
            $reminders = $reminders->merge($this->getCouponReminders($user));
        }
        
        if ($settings['certificate'] ?? true) {
            $reminders = $reminders->merge($this->getCertificateReminders($user));
        }
        
        if ($settings['rating'] ?? true) {
            $reminders = $reminders->merge($this->getRatingReminders($user));
        }
        
        if ($settings['question'] ?? true) {
            $reminders = $reminders->merge($this->getQuestionReminders($user));
        }
        
        return $reminders->sortByDesc('priority');
    }
    
    /**
     * Get user's reminder settings
     */
    protected function getUserSettings(User $user): array
    {
        $settings = ReminderSetting::where('user_id', $user->id)
            ->pluck('enabled', 'type')
            ->toArray();
        
        // Default all types to enabled
        foreach (Reminder::getTypes() as $type => $label) {
            if (!isset($settings[$type])) {
                $settings[$type] = true;
            }
        }
        
        return $settings;
    }
    
    /**
     * Quiz reminders - pending quizzes for enrolled courses
     */
    protected function getQuizReminders(User $user): Collection
    {
        $reminders = collect();
        
        // Get user's enrolled courses
        $enrolledCourseIds = Enrollment::where('user_id', $user->id)
            ->whereNull('completed_at')
            ->pluck('course_id');
        
        if ($enrolledCourseIds->isEmpty()) {
            return $reminders;
        }
        
        // Find quizzes in enrolled courses that haven't been attempted or not passed
        $quizzes = Quiz::with(['lesson.section.course'])
            ->whereHas('lesson.section', function ($q) use ($enrolledCourseIds) {
                $q->whereIn('course_id', $enrolledCourseIds);
            })
            ->get();
        
        foreach ($quizzes as $quiz) {
            $attempt = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->where('passed', true)
                ->first();
            
            if (!$attempt) {
                $course = $quiz->lesson?->section?->course;
                $reminders->push([
                    'type' => Reminder::TYPE_QUIZ,
                    'title' => 'اختبار في انتظارك',
                    'message' => "لديك اختبار \"{$quiz->title}\" لم تجتازه بعد",
                    'icon' => Reminder::getTypeIcon(Reminder::TYPE_QUIZ),
                    'color' => Reminder::getTypeColor(Reminder::TYPE_QUIZ),
                    'action_url' => route('filament.admin.pages.watch-video', ['lesson' => $quiz->lesson_id]),
                    'action_label' => 'ابدأ الاختبار',
                    'remindable_type' => Quiz::class,
                    'remindable_id' => $quiz->id,
                    'priority' => 8,
                    'course_slug' => $course?->slug,
                    'lesson_id' => $quiz->lesson_id,
                ]);
            }
        }
        
        return $reminders;
    }
    
    /**
     * Message reminders - unread messages
     */
    protected function getMessageReminders(User $user): Collection
    {
        $reminders = collect();
        
        $unreadCount = Message::whereHas('conversation.participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('user_id', '!=', $user->id)
        ->whereDoesntHave('conversation.participants', function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->whereColumn('last_read_at', '>=', 'messages.created_at');
        })
        ->count();
        
        if ($unreadCount > 0) {
            $reminders->push([
                'type' => Reminder::TYPE_MESSAGE,
                'title' => 'رسائل غير مقروءة',
                'message' => "لديك {$unreadCount} رسالة غير مقروءة",
                'icon' => Reminder::getTypeIcon(Reminder::TYPE_MESSAGE),
                'color' => Reminder::getTypeColor(Reminder::TYPE_MESSAGE),
                'action_url' => route('filament.admin.pages.messages'),
                'action_label' => 'عرض الرسائل',
                'priority' => 9,
            ]);
        }
        
        return $reminders;
    }
    
    /**
     * Lesson reminders - courses in progress
     */
    protected function getLessonReminders(User $user): Collection
    {
        $reminders = collect();
        
        // Get courses in progress (not completed, with some progress)
        $enrollments = Enrollment::where('user_id', $user->id)
            ->whereNull('completed_at')
            ->where('progress_percentage', '>', 0)
            ->where('progress_percentage', '<', 100)
            ->with('course')
            ->get();
        
        foreach ($enrollments as $enrollment) {
            $lastActivity = VideoProgress::where('user_id', $user->id)
                ->whereHas('lesson.section', function ($q) use ($enrollment) {
                    $q->where('course_id', $enrollment->course_id);
                })
                ->max('last_watched_at');
            
            // Remind if no activity in last 3 days
            if (!$lastActivity || \Carbon\Carbon::parse($lastActivity)->lt(now()->subDays(3))) {
                $reminders->push([
                    'type' => Reminder::TYPE_LESSON,
                    'title' => 'أكمل تعلمك',
                    'message' => "تقدمك في \"{$enrollment->course->title}\" هو {$enrollment->progress_percentage}% - تابع التعلم!",
                    'icon' => Reminder::getTypeIcon(Reminder::TYPE_LESSON),
                    'color' => Reminder::getTypeColor(Reminder::TYPE_LESSON),
                    'action_url' => route('filament.admin.resources.courses.edit', ['record' => $enrollment->course_id]),
                    'action_label' => 'متابعة التعلم',
                    'remindable_type' => Enrollment::class,
                    'remindable_id' => $enrollment->id,
                    'priority' => 7,
                    'course_slug' => $enrollment->course?->slug,
                ]);
            }
        }
        
        return $reminders;
    }
    
    /**
     * Coupon reminders - expiring soon
     */
    protected function getCouponReminders(User $user): Collection
    {
        $reminders = collect();
        
        // Only for admins - show coupons expiring in 3 days
        if (!$user->hasRole('admin')) {
            return $reminders;
        }
        
        $expiringCoupons = Coupon::where('is_active', true)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(3)])
            ->get();
        
        foreach ($expiringCoupons as $coupon) {
            $daysLeft = now()->diffInDays($coupon->expires_at);
            $reminders->push([
                'type' => Reminder::TYPE_COUPON,
                'title' => 'كوبون قارب على الانتهاء',
                'message' => "الكوبون \"{$coupon->code}\" سينتهي خلال {$daysLeft} يوم",
                'icon' => Reminder::getTypeIcon(Reminder::TYPE_COUPON),
                'color' => Reminder::getTypeColor(Reminder::TYPE_COUPON),
                'action_url' => route('filament.admin.resources.coupons.edit', ['record' => $coupon->id]),
                'action_label' => 'عرض الكوبون',
                'remindable_type' => Coupon::class,
                'remindable_id' => $coupon->id,
                'priority' => 6,
            ]);
        }
        
        return $reminders;
    }
    
    /**
     * Certificate reminders - completed but not downloaded
     */
    protected function getCertificateReminders(User $user): Collection
    {
        $reminders = collect();
        
        // Find completed courses without downloaded certificates
        $completedWithoutCert = Enrollment::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->whereDoesntHave('course.certificates', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with('course')
            ->get();
        
        foreach ($completedWithoutCert as $enrollment) {
            $reminders->push([
                'type' => Reminder::TYPE_CERTIFICATE,
                'title' => 'احصل على شهادتك',
                'message' => "أكملت دورة \"{$enrollment->course->title}\" - احصل على شهادتك الآن!",
                'icon' => Reminder::getTypeIcon(Reminder::TYPE_CERTIFICATE),
                'color' => Reminder::getTypeColor(Reminder::TYPE_CERTIFICATE),
                'action_url' => route('filament.admin.resources.certificates.index'),
                'action_label' => 'عرض الشهادات',
                'remindable_type' => Enrollment::class,
                'remindable_id' => $enrollment->id,
                'priority' => 5,
                'course_slug' => $enrollment->course?->slug,
            ]);
        }
        
        return $reminders;
    }
    
    /**
     * Rating reminders - completed but not rated
     */
    protected function getRatingReminders(User $user): Collection
    {
        $reminders = collect();
        
        // Find completed courses without rating
        $completedWithoutRating = Enrollment::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->whereDoesntHave('course.ratings', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with('course')
            ->get();
        
        foreach ($completedWithoutRating as $enrollment) {
            $reminders->push([
                'type' => Reminder::TYPE_RATING,
                'title' => 'شاركنا رأيك',
                'message' => "هل أعجبتك دورة \"{$enrollment->course->title}\"? شاركنا تقييمك!",
                'icon' => Reminder::getTypeIcon(Reminder::TYPE_RATING),
                'color' => Reminder::getTypeColor(Reminder::TYPE_RATING),
                'action_url' => route('filament.admin.resources.course-ratings.create'),
                'action_label' => 'أضف تقييم',
                'remindable_type' => Enrollment::class,
                'remindable_id' => $enrollment->id,
                'priority' => 4,
                'course_slug' => $enrollment->course?->slug,
            ]);
        }
        
        return $reminders;
    }
    
    /**
     * Question reminders - unanswered questions for instructors
     */
    protected function getQuestionReminders(User $user): Collection
    {
        $reminders = collect();
        
        // Only for instructors
        if (!$user->hasAnyRole(['admin', 'instructor'])) {
            return $reminders;
        }
        
        $courseIds = Course::where('user_id', $user->id)->pluck('id');
        
        if ($courseIds->isEmpty() && !$user->hasRole('admin')) {
            return $reminders;
        }
        
        $query = CourseQuestion::where('is_answered', false);
        
        if (!$user->hasRole('admin')) {
            $query->whereIn('course_id', $courseIds);
        }
        
        $unansweredCount = $query->count();
        
        if ($unansweredCount > 0) {
            $reminders->push([
                'type' => Reminder::TYPE_QUESTION,
                'title' => 'أسئلة بانتظار الإجابة',
                'message' => "لديك {$unansweredCount} سؤال من الطلاب بانتظار إجابتك",
                'icon' => Reminder::getTypeIcon(Reminder::TYPE_QUESTION),
                'color' => Reminder::getTypeColor(Reminder::TYPE_QUESTION),
                'action_url' => route('filament.admin.resources.course-questions.index'),
                'action_label' => 'عرض الأسئلة',
                'priority' => 8,
            ]);
        }
        
        return $reminders;
    }
    
    /**
     * Get reminder counts by type
     */
    public function getReminderCounts(User $user): array
    {
        $reminders = $this->generateReminders($user);
        
        $counts = [];
        foreach (Reminder::getTypes() as $type => $label) {
            $counts[$type] = $reminders->where('type', $type)->count();
        }
        $counts['total'] = $reminders->count();
        
        return $counts;
    }
    
    /**
     * Dismiss a reminder
     */
    public function dismissReminder(User $user, string $type, ?int $remindableId = null): void
    {
        Reminder::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $type,
                'remindable_id' => $remindableId,
            ],
            [
                'title' => '',
                'message' => '',
                'dismissed_at' => now(),
            ]
        );
    }
}
