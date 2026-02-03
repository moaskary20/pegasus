<?php

namespace App\Observers;

use App\Models\Lesson;
use App\Services\ZoomAPIService;
use Illuminate\Support\Facades\Log;

class LessonObserver
{
    protected ZoomAPIService $zoomService;

    public function __construct(ZoomAPIService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    /**
     * عند إنشاء درس جديد
     */
    public function created(Lesson $lesson)
    {
        $this->handleZoomMeeting($lesson);
    }

    /**
     * عند تحديث درس
     */
    public function updated(Lesson $lesson)
    {
        // إذا تم تغيير بيانات Zoom
        if ($lesson->isDirty(['has_zoom_meeting', 'zoom_scheduled_time', 'zoom_duration', 'zoom_password'])) {
            $this->handleZoomMeeting($lesson);
        }
    }

    /**
     * معالجة الاجتماع
     */
    private function handleZoomMeeting(Lesson $lesson)
    {
        try {
            // إذا كان الدرس يحتوي على اجتماع Zoom
            if ($lesson->has_zoom_meeting && $lesson->zoom_scheduled_time) {
                // إنشاء اجتماع Zoom
                $zoomMeeting = $this->zoomService->createMeeting(
                    lesson: $lesson,
                    scheduledTime: $lesson->zoom_scheduled_time->toDateTimeString(),
                    duration: $lesson->zoom_duration ?? 60
                );

                if ($zoomMeeting) {
                    // حفظ رابط الاجتماع
                    $lesson->update([
                        'zoom_link' => $zoomMeeting->join_url,
                    ]);

                    Log::info('تم إنشاء اجتماع Zoom', [
                        'lesson_id' => $lesson->id,
                        'meeting_id' => $zoomMeeting->zoom_meeting_id,
                        'zoom_link' => $zoomMeeting->join_url,
                    ]);
                }
            } elseif (!$lesson->has_zoom_meeting && $lesson->zoom_link) {
                // إذا تم تعطيل Zoom، امسح الرابط
                $lesson->update([
                    'zoom_link' => null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء اجتماع Zoom', [
                'lesson_id' => $lesson->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
