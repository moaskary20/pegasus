<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->intended(url('/admin'));
        }

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
            return redirect(url('/'))->with('notice', [
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
        if (!$allowRegistration) {
            return back()->withErrors(['register' => 'التسجيل معطل حالياً.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'user_type' => ['required', 'string', 'in:student,instructor'],
        ]);

        $phone = isset($validated['phone']) && trim((string) $validated['phone']) !== ''
            ? trim((string) $validated['phone'])
            : null;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phone,
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['user_type']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(url('/admin'))->with('notice', [
            'type' => 'success',
            'message' => 'تم إنشاء حسابك بنجاح. مرحباً بك!',
        ]);
    }
}
