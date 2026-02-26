<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    /**
     * إعدادات التطبيق للموبايل (لا يتطلب مصادقة)
     * تُستخدم للتحكم: منع الشاشة، العلامة المائية، سرعة التشغيل، إلخ
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user(); // قد يكون null

        $settings = [
            'prevent_screen_capture' => (bool) PlatformSetting::get('prevent_screen_capture', true),
            'max_devices_per_account' => (int) PlatformSetting::get('max_devices_per_account', 3),
            'max_views_per_lesson' => (int) PlatformSetting::get('max_views_per_lesson', 10),
            'enforce_lesson_order' => (bool) PlatformSetting::get('enforce_lesson_order', true),
            'require_lesson_completion' => (int) PlatformSetting::get('require_lesson_completion', 80),
            'enable_video_watermark' => (bool) PlatformSetting::get('enable_video_watermark', false),
            'watermark_text' => (string) PlatformSetting::get('watermark_text', '{user_email}'),
            'prevent_video_download' => (bool) PlatformSetting::get('prevent_video_download', true),
            'enable_playback_speed' => (bool) PlatformSetting::get('enable_playback_speed', true),
            'enable_video_resume' => (bool) PlatformSetting::get('enable_video_resume', true),
            'max_failed_login_attempts' => (int) PlatformSetting::get('max_failed_login_attempts', 5),
        ];

        $watermarkResolved = $settings['watermark_text'];
        if ($user && $settings['enable_video_watermark']) {
            $watermarkResolved = str_replace(
                ['{user_email}', '{user_name}', '{user_id}'],
                [$user->email ?? '', $user->name ?? '', (string) $user->id],
                $watermarkResolved
            );
        }

        $settings['watermark_text_resolved'] = $watermarkResolved;

        return response()->json($settings);
    }
}
