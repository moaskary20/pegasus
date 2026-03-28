<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\EgyptianMobilePhone;
use App\Rules\UniqueNormalizedPhone;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function show(Request $request)
    {
        if (Auth::check()) {
            return redirect()->intended(url('/admin'));
        }

        $this->storeIntendedUrlIfValid($request->query('intended'));

        return view('pages.auth');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if (Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], (bool) ($validated['remember'] ?? false))) {
            $request->session()->regenerate();

            return redirect()->intended(route('site.home'))->with('notice', [
                'type' => 'success',
                'message' => 'تم تسجيل الدخول بنجاح',
            ]);
        }

        return back()->withErrors([
            'email' => __('بيانات الدخول غير صحيحة.'),
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $allowRegistration = \App\Models\PlatformSetting::get('allow_registration', true);
        if (! $allowRegistration) {
            return back()->withErrors(['register' => 'التسجيل معطل حالياً.']);
        }

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email', ''))),
            'phone' => User::normalizePhone(trim((string) $request->input('phone', ''))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', new EgyptianMobilePhone, new UniqueNormalizedPhone],
            'password' => ['required', 'string', 'confirmed', PasswordRule::defaults()],
            'user_type' => ['required', 'string', 'in:student,instructor'],
        ], [
            'email.unique' => 'هذا البريد الإلكتروني مسجّل مسبقاً.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['user_type']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('site.home'))->with('notice', [
            'type' => 'success',
            'message' => 'تم إنشاء حسابك بنجاح. مرحباً بك!',
        ]);
    }

    /**
     * عرض صفحة طلب استرجاع كلمة المرور
     */
    public function showForgotPasswordForm()
    {
        if (Auth::check()) {
            return redirect()->intended(url('/admin'));
        }

        return view('pages.forgot-password');
    }

    /**
     * إرسال رابط استرجاع كلمة المرور إلى البريد الإلكتروني
     */
    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink([
            'email' => $validated['email'],
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __('تم إرسال رابط استرجاع كلمة المرور إلى بريدك الإلكتروني. تحقق من صندوق الوارد أو البريد العشوائي.'));
        }

        return back()->withErrors([
            'email' => __($status),
        ])->onlyInput('email');
    }

    /**
     * عرض صفحة إدخال كلمة المرور الجديدة
     */
    public function showResetPasswordForm(Request $request, string $token)
    {
        if (Auth::check()) {
            return redirect()->intended(url('/admin'));
        }

        return view('pages.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * تحديث كلمة المرور بعد التحقق من الرابط
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'password_confirmation' => $validated['password_confirmation'],
                'token' => $validated['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('site.auth')->with('notice', [
                'type' => 'success',
                'message' => __('تم تغيير كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.'),
            ]);
        }

        return back()->withErrors([
            'email' => __($status),
        ])->withInput($request->only('email'));
    }

    /**
     * حفظ عنوان العودة بعد تسجيل الدخول (نفس النطاق فقط — منع open redirect).
     */
    private function storeIntendedUrlIfValid(mixed $url): void
    {
        if (! is_string($url) || $url === '') {
            return;
        }
        $url = urldecode($url);
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return;
        }
        $base = rtrim((string) config('app.url'), '/');
        if ($url !== $base && ! str_starts_with($url, $base.'/')) {
            return;
        }
        session(['url.intended' => $url]);
    }
}
