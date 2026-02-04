<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\SupportComplaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(): View
    {
        $supportEmail = PlatformSetting::get('support_email', '');
        $supportPhone = PlatformSetting::get('support_phone', '');
        $supportPhone2 = PlatformSetting::get('support_phone_2', '');

        if (empty($supportEmail)) {
            $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com';
            $supportEmail = "support@{$domain}";
        }

        return view('pages.support', [
            'supportEmail' => $supportEmail,
            'supportPhone' => $supportPhone,
            'supportPhone2' => $supportPhone2,
        ]);
    }

    public function storeComplaint(Request $request): RedirectResponse
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
            'user_id' => auth()->id(),
            'type' => SupportComplaint::TYPE_COMPLAINT,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        return redirect()
            ->route('site.support')
            ->with('notice', ['type' => 'success', 'message' => 'تم استلام شكواك بنجاح. سنتواصل معك في أقرب وقت.'])
            ->withFragment('complaint');
    }

    public function storeContact(Request $request): RedirectResponse
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
            'user_id' => auth()->id(),
            'type' => SupportComplaint::TYPE_CONTACT,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        return redirect()
            ->route('site.support')
            ->with('notice', ['type' => 'success', 'message' => 'تم استلام رسالتك بنجاح. سنتواصل معك في أقرب وقت.'])
            ->withFragment('contact');
    }
}
