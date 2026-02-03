<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة مستخدم')
                ->icon('heroicon-o-plus'),
        ];
    }
    
    public function getHeader(): ?View
    {
        return view('filament.resources.users.header', [
            'totalUsers' => User::count(),
            'admins' => User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->count(),
            'instructors' => User::whereHas('roles', fn($q) => $q->where('name', 'instructor'))->count(),
            'students' => User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count(),
            'verifiedUsers' => User::whereNotNull('email_verified_at')->count(),
            'newThisMonth' => User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'createUrl' => static::getResource()::getUrl('create'),
        ]);
    }
}
