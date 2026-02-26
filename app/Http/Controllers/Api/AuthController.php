<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * تسجيل الدخول (للتطبيق - يرجع توكن)
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ])) {
            return response()->json([
                'message' => 'بيانات الدخول غير صحيحة.',
                'errors' => ['email' => ['بيانات الدخول غير صحيحة.']],
            ], 422);
        }

        $user = Auth::user();
        $user->tokens()->where('name', 'mobile')->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    /**
     * إنشاء حساب جديد (للتطبيق - يرجع توكن)
     */
    public function register(Request $request): JsonResponse
    {
        $allowRegistration = \App\Models\PlatformSetting::get('allow_registration', true);
        if (! $allowRegistration) {
            return response()->json([
                'message' => 'التسجيل معطل حالياً.',
                'errors' => ['register' => ['التسجيل معطل حالياً.']],
            ], 422);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'user_type' => ['required', 'string', 'in:student,instructor'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => trim((string) $validated['phone']),
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['user_type']);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'تم إنشاء حسابك بنجاح. مرحباً بك!',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->getRoleNames(),
            ],
        ], 201);
    }

    /**
     * تسجيل الخروج (إبطال التوكن)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    /**
     * بيانات المستخدم الحالي
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar_url,
            'city' => $user->city,
            'job' => $user->job,
            'roles' => $user->getRoleNames(),
        ]);
    }

    /**
     * تحديث الملف الشخصي (اسم، بريد، هاتف، مدينة، عمل، صورة)
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'job' => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,gif,png', 'max:2048'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
            'city' => $validated['city'] ?? $user->city,
            'job' => $validated['job'] ?? $user->job,
        ];

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $avatarPath;
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'تم تحديث البيانات بنجاح',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'avatar_url' => $user->fresh()->avatar_url,
                'city' => $user->city,
                'job' => $user->job,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    /**
     * تغيير كلمة المرور
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => [
                'required',
                function ($attribute, $value, $fail) use ($user) {
                    if (! Hash::check($value, $user->password)) {
                        $fail('كلمة المرور الحالية غير صحيحة.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return response()->json([
            'message' => 'تم تحديث كلمة المرور بنجاح',
        ]);
    }
}
