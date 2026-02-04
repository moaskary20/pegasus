<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateOrUpdateAdmin extends Command
{
    protected $signature = 'admin:ensure 
        {email : البريد الإلكتروني} 
        {password : كلمة المرور}
        {--name= : الاسم (افتراضي: Admin)}';

    protected $description = 'إنشاء أو تحديث مستخدم مدير بالبريد وكلمة المرور';

    public function handle(): int
    {
        $email = trim($this->argument('email'));
        $password = $this->argument('password');
        $name = $this->option('name') ?: 'Admin';

        // Ensure admin role exists
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
            ]);
            $user->syncRoles(['admin']);
            $this->info("تم تحديث المستخدم: {$user->name} ({$email})");
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $user->assignRole('admin');
            $this->info("تم إنشاء المستخدم: {$user->name} ({$email})");
        }

        return self::SUCCESS;
    }
}
