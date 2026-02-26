<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\SupportComplaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * إعدادات الدعم (بريد، هواتف) للعرض في الموبايل.
     */
    public function index(): JsonResponse
    {
        $supportEmail = PlatformSetting::get('support_email', '');
        $supportPhone = PlatformSetting::get('support_phone', '');
        $supportPhone2 = PlatformSetting::get('support_phone_2', '');

        if (empty($supportEmail)) {
            $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com';
            $supportEmail = "support@{$domain}";
        }

        return response()->json([
            'support_email' => $supportEmail,
            'support_phone' => $supportPhone ?? '',
            'support_phone_2' => $supportPhone2 ?? '',
        ]);
    }

    /**
     * تقديم شكوى (للموبايل، يتطلب مصادقة اختيارية).
     */
    public function storeComplaint(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:30',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'أدخل بريداً إلكترونياً صحيحاً',
            'subject.required' => 'موضوع الشكوى مطلوب',
            'message.required' => 'تفاصيل الشكوى مطلوبة',
        ]);

        SupportComplaint::create([
            'user_id' => Auth::guard('sanctum')->id(),
            'type' => SupportComplaint::TYPE_COMPLAINT,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        return response()->json([
            'message' => 'تم استلام شكواك بنجاح. سنتواصل معك في أقرب وقت.',
        ]);
    }

    /**
     * تقديم رسالة تواصل (للموبايل).
     */
    public function storeContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:30',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'أدخل بريداً إلكترونياً صحيحاً',
            'subject.required' => 'الموضوع مطلوب',
            'message.required' => 'الرسالة مطلوبة',
        ]);

        SupportComplaint::create([
            'user_id' => Auth::guard('sanctum')->id(),
            'type' => SupportComplaint::TYPE_CONTACT,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        return response()->json([
            'message' => 'تم استلام رسالتك بنجاح. سنتواصل معك في أقرب وقت.',
        ]);
    }
}
