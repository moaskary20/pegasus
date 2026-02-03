<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\WithPagination;

class Notifications extends Page
{
    use WithPagination;
    
    protected static ?string $navigationLabel = 'الإشعارات';
    
    protected static ?string $title = 'الإشعارات';
    
    protected static ?int $navigationSort = 99;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;
    
    protected string $view = 'filament.pages.notifications';
    
    protected static ?string $slug = 'notifications';
    
    public string $filter = 'all'; // 'all', 'unread', 'read'
    public ?string $typeFilter = null;
    
    public function mount(): void
    {
        //
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return true; // Show in navigation
    }
    
    public static function getNavigationGroup(): ?string
    {
        return 'الإشعارات';
    }
    
    public function getNotificationsProperty()
    {
        $query = auth()->user()->notifications();
        
        if ($this->filter === 'unread') {
            $query = auth()->user()->unreadNotifications();
        } elseif ($this->filter === 'read') {
            $query = auth()->user()->readNotifications();
        }
        
        if ($this->typeFilter) {
            $query->where('data->type', $this->typeFilter);
        }
        
        return $query->latest()->paginate(15);
    }
    
    public function getUnreadCountProperty(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }
    
    public function getNotificationTypesProperty(): array
    {
        return [
            'new_enrollment' => 'تسجيل طالب جديد',
            'enrollment_confirmed' => 'تأكيد التسجيل',
            'course_completed' => 'إتمام دورة',
            'new_lesson' => 'درس جديد',
            'new_question' => 'سؤال جديد',
            'question_answered' => 'إجابة على سؤال',
            'lesson_comment' => 'تعليق جديد',
            'order_confirmed' => 'تأكيد طلب',
            'new_sale' => 'عملية بيع',
            'new_message' => 'رسالة جديدة',
        ];
    }
    
    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }
    
    public function setTypeFilter(?string $type): void
    {
        $this->typeFilter = $type;
        $this->resetPage();
    }
    
    public function markAsRead(string $id): void
    {
        $notification = auth()->user()->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
        }
    }
    
    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }
    
    public function deleteNotification(string $id): void
    {
        $notification = auth()->user()->notifications()->find($id);
        
        if ($notification) {
            $notification->delete();
        }
    }
    
    public function deleteAllRead(): void
    {
        auth()->user()->readNotifications()->delete();
    }
}
