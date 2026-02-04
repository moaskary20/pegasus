<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword extends Command
{
    protected $signature = 'user:reset-password {email : البريد الإلكتروني} {password? : كلمة المرور الجديدة}';

    protected $description = 'إعادة تعيين كلمة مرور مستخدم بالبريد الإلكتروني';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        if (!$password) {
            $password = $this->secret('أدخل كلمة المرور الجديدة');
            if (!$password) {
                $this->error('يجب إدخال كلمة المرور.');
                return self::FAILURE;
            }
        }

        $user = User::query()->where('email', $email)->first();

        if (!$user) {
            $this->error("المستخدم بالبريد {$email} غير موجود.");
            return self::FAILURE;
        }

        $user->update(['password' => Hash::make($password)]);
        $this->info("تم تحديث كلمة المرور للمستخدم: {$user->name} ({$email})");

        return self::SUCCESS;
    }
}
