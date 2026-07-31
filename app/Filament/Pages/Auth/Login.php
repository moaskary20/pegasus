<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function mount(): void
    {
        if (Filament::auth()->check()) {
            $user = Filament::auth()->user();

            if ($user instanceof FilamentUser && ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())) {
                Filament::auth()->logout();

                Notification::make()
                    ->title('غير مسموح')
                    ->body('لوحة التحكم مخصصة للإدارة والمدربين فقط.')
                    ->danger()
                    ->persistent()
                    ->send();
            } else {
                redirect()->intended(Filament::getUrl());

                return;
            }
        }

        $this->form->fill();
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $authGuard = Filament::auth();
        $authProvider = $authGuard->getProvider(); /** @phpstan-ignore-line */
        $credentials = $this->getCredentialsFromFormData($data);
        $user = $authProvider->retrieveByCredentials($credentials);

        if ($user && $authProvider->validateCredentials($user, $credentials)) {
            if ($user instanceof User
                && $user->hasRole('student')
                && ! $user->hasAnyRole(['admin', 'instructor'])
            ) {
                throw ValidationException::withMessages([
                    'data.email' => 'غير مسموح. لا يمكن للطلاب الدخول إلى لوحة التحكم.',
                ]);
            }
        }

        return parent::authenticate();
    }
}
