<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    /**
     * GET certificate URL or download. Generate PDF if not exists.
     */
    public function download(string $courseSlug): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $course = Course::query()
            ->where('is_published', true)
            ->where('slug', $courseSlug)
            ->first();

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Not enrolled in this course'], 403);
        }

        if (!$enrollment->completed_at) {
            return response()->json(['message' => 'Course not completed yet'], 403);
        }

        $cert = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$cert) {
            return response()->json(['message' => 'Certificate not found'], 404);
        }

        if (!$cert->pdf_path || !Storage::disk('public')->exists($cert->pdf_path)) {
            $service = app(CertificateService::class);
            $path = $service->saveCertificatePdf($cert);
            $cert->update(['pdf_path' => $path]);
        }

        $url = asset('storage/' . ltrim($cert->pdf_path, '/'));
        $baseUrl = rtrim(config('app.url', request()->getSchemeAndHttpHost()), '/');
        if (!str_starts_with($url, 'http')) {
            $url = $baseUrl . '/' . ltrim($url, '/');
        }

        return response()->json([
            'url' => $url,
            'filename' => 'شهادة_' . \Illuminate\Support\Str::slug($course->title) . '.pdf',
        ]);
    }
}
