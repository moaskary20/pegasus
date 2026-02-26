<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

        $email = strtolower(trim($validated['email']));
        $lockoutKey = 'login_lockout:' . md5($email);
        $attemptsKey = 'login_attempts:' . md5($email);

        if (Cache::has($lockoutKey)) {
            $mins = (int) PlatformSetting::get('lockout_duration_minutes', 30);
            return response()->json([
                'message' => "تم قفل الحساب مؤقتاً بعد عدد كبير من المحاولات الفاشلة. حاول بعد {$mins} دقيقة.",
                'errors' => ['email' => ['الحساب مقفل مؤقتاً.']],
            ], 429);
        }

        if (! Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ])) {
            $maxAttempts = (int) PlatformSetting::get('max_failed_login_attempts', 5);
            $attempts = (int) Cache::get($attemptsKey, 0) + 1;
            Cache::put($attemptsKey, $attempts, now()->addMinutes(30));

            if ($attempts >= $maxAttempts) {
                $lockoutMins = (int) PlatformSetting::get('lockout_duration_minutes', 30);
                Cache::put($lockoutKey, true, now()->addMinutes($lockoutMins));
                Cache::forget($attemptsKey);
                return response()->json([
                    'message' => "تم قفل الحساب مؤقتاً بعد {$maxAttempts} محاولات فاشلة. حاول بعد {$lockoutMins} دقيقة.",
                    'errors' => ['email' => ['تم قفل الحساب مؤقتاً.']],
                ], 429);
            }

            return response()->json([
                'message' => 'بيانات الدخول غير صحيحة.',
                'errors' => ['email' => ['بيانات الدخول غير صحيحة.']],
            ], 422);
        }

        Cache::forget($attemptsKey);
        Cache::forget($lockoutKey);

        $user = Auth::user();
        $maxDevices = (int) PlatformSetting::get('max_devices_per_account', 3);
        $token = $user->createToken('mobile')->plainTextToken;
        $tokens = $user->tokens()->where('name', 'mobile')->orderBy('created_at')->get();
        if ($tokens->count() > $maxDevices) {
            $toDelete = $tokens->take($tokens->count() - $maxDevices);
            foreach ($toDelete as $t) {
                $t->delete();
            }
        }

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
            'total_points' => (int) ($user->total_points ?? 0),
            'available_points' => (int) ($user->available_points ?? 0),
            'rank' => $user->rank ?? 'bronze',
            'rank_label' => $user->rank_label,
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
