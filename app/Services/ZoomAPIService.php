<?php

namespace App\Services;

use App\Models\PlatformSetting;
use App\Models\ZoomMeeting;
use App\Models\Lesson;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ZoomAPIService
{
    private string $apiKey;
    private string $apiSecret;
    private string $accountId;
    private string $userId;
    private string $baseUrl = 'https://api.zoom.us/v2';
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->apiKey = PlatformSetting::get('zoom_api_key', '');
        $this->apiSecret = PlatformSetting::get('zoom_api_secret', '');
        $this->accountId = PlatformSetting::get('zoom_account_id', '');
        $this->userId = PlatformSetting::get('zoom_user_id', '');
    }

    /**
     * الحصول على توكن الوصول
     */
    private function getAccessToken(): ?string
    {
        try {
            if ($this->accessToken) {
                return $this->accessToken;
            }

            // للحصول على المزيد من المعلومات عن Server-to-Server OAuth
            // https://developers.zoom.us/docs/internal-apps/s2s-oauth/

            $payload = [
                'iss' => $this->apiKey,
                'exp' => time() + 3600,
            ];

            // بدلاً من JWT، يمكنك استخدام OAuth2 إذا كان متوفراً
            // هذا مثال باستخدام Basic Auth كبديل
            
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->post($this->baseUrl . '/users/me', [
                    'action' => 'create',
                ])
                ->json();

            if (isset($response['access_token'])) {
                $this->accessToken = $response['access_token'];
                return $this->accessToken;
            }

            return null;
        } catch (Exception $e) {
            Log::error('Zoom API: Failed to get access token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * إنشاء اجتماع Zoom
     */
    public function createMeeting(Lesson $lesson, string $scheduledTime, int $duration = 60): ?ZoomMeeting
    {
        try {
            if (!$this->userId) {
                throw new Exception('Zoom User ID not configured');
            }

            $meetingData = [
                'topic' => $lesson->title,
                'type' => 2, // scheduled meeting
                'start_time' => $scheduledTime,
                'duration' => $duration,
                'timezone' => PlatformSetting::get('zoom_timezone', 'Africa/Cairo'),
                'agenda' => $lesson->description ?? $lesson->title,
                'settings' => [
                    'host_video' => PlatformSetting::get('zoom_host_video', true),
                    'participant_video' => PlatformSetting::get('zoom_participant_video', true),
                    'join_before_host' => false,
                    'mute_upon_entry' => true,
                    'waiting_room' => PlatformSetting::get('zoom_waiting_room_enabled', false),
                    'audio' => PlatformSetting::get('zoom_audio_type', 'both'),
                    'auto_recording' => PlatformSetting::get('zoom_enable_auto_recording', true) ? 'cloud' : 'none',
                    'require_password_for_scheduling_new_meetings' => PlatformSetting::get('zoom_require_password', true),
                    'require_password_for_instant_meetings' => false,
                    'require_password_for_pmi_meetings' => false,
                    'meeting_invitees' => [],
                ],
            ];

            if (PlatformSetting::get('zoom_require_password', true)) {
                $meetingData['settings']['password'] = $this->generateMeetingPassword();
            }

            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->post($this->baseUrl . "/users/{$this->userId}/meetings", $meetingData)
                ->json();

            if (isset($response['id'])) {
                $zoomMeeting = ZoomMeeting::create([
                    'lesson_id' => $lesson->id,
                    'zoom_meeting_id' => $response['id'],
                    'topic' => $lesson->title,
                    'description' => $lesson->description,
                    'scheduled_start_time' => $scheduledTime,
                    'duration' => $duration,
                    'timezone' => PlatformSetting::get('zoom_timezone', 'Africa/Cairo'),
                    'join_url' => $response['join_url'] ?? null,
                    'start_url' => $response['start_url'] ?? null,
                    'password' => $meetingData['settings']['password'] ?? null,
                    'host_id' => $this->userId,
                    'status' => 'scheduled',
                ]);

                Log::info('Zoom Meeting Created', [
                    'lesson_id' => $lesson->id,
                    'zoom_meeting_id' => $response['id'],
                ]);

                return $zoomMeeting;
            } else {
                Log::error('Zoom API: Failed to create meeting', ['response' => $response]);
                return null;
            }
        } catch (Exception $e) {
            Log::error('Zoom API: Exception creating meeting', [
                'error' => $e->getMessage(),
                'lesson_id' => $lesson->id,
            ]);
            return null;
        }
    }

    /**
     * تحديث اجتماع موجود
     */
    public function updateMeeting(ZoomMeeting $zoomMeeting, array $data): bool
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->patch($this->baseUrl . "/meetings/{$zoomMeeting->zoom_meeting_id}", $data)
                ->json();

            if (isset($response['id'])) {
                $zoomMeeting->update($data);
                Log::info('Zoom Meeting Updated', ['zoom_meeting_id' => $zoomMeeting->zoom_meeting_id]);
                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error('Zoom API: Exception updating meeting', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * حذف اجتماع Zoom
     */
    public function deleteMeeting(ZoomMeeting $zoomMeeting): bool
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->delete($this->baseUrl . "/meetings/{$zoomMeeting->zoom_meeting_id}")
                ->json();

            $zoomMeeting->update(['status' => 'cancelled']);
            Log::info('Zoom Meeting Deleted', ['zoom_meeting_id' => $zoomMeeting->zoom_meeting_id]);
            return true;
        } catch (Exception $e) {
            Log::error('Zoom API: Exception deleting meeting', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * الحصول على تفاصيل الاجتماع
     */
    public function getMeetingDetails(string $meetingId): ?array
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get($this->baseUrl . "/meetings/{$meetingId}")
                ->json();

            return $response;
        } catch (Exception $e) {
            Log::error('Zoom API: Exception getting meeting details', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * اختبار الاتصال بـ Zoom API
     */
    public function testConnection(): array
    {
        try {
            if (!$this->apiKey || !$this->apiSecret || !$this->userId) {
                return [
                    'success' => false,
                    'message' => 'بيانات اعتماد Zoom غير مكتملة',
                ];
            }

            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->timeout(10)
                ->get($this->baseUrl . "/users/{$this->userId}")
                ->json();

            if (isset($response['id'])) {
                return [
                    'success' => true,
                    'message' => 'تم الاتصال بنجاح',
                    'user' => $response,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'فشل الاتصال',
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'خطأ: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * توليد كلمة مرور الاجتماع
     */
    private function generateMeetingPassword(): string
    {
        // Zoom passwords should be between 1-10 characters
        return substr(bin2hex(random_bytes(5)), 0, 10);
    }

    /**
     * التحقق من أن جميع البيانات المطلوبة موجودة
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) 
            && !empty($this->apiSecret) 
            && !empty($this->userId)
            && !empty($this->accountId);
    }
}
