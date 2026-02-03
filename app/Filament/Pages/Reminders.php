<?php

namespace App\Filament\Pages;

use App\Models\Reminder;
use App\Models\ReminderSetting;
use App\Services\ReminderService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Reminders extends Page
{
    protected static ?string $navigationLabel = 'التذكيرات';
    
    protected static ?string $title = 'التذكيرات';
    
    protected static ?int $navigationSort = 25;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;
    
    protected string $view = 'filament.pages.reminders';
    
    public static function getNavigationGroup(): ?string
    {
        return 'الإشعارات';
    }
    
    public string $activeFilter = 'all';
    
    public function getRemindersProperty()
    {
        $service = app(ReminderService::class);
        $reminders = $service->generateReminders(auth()->user());
        
        if ($this->activeFilter !== 'all') {
            $reminders = $reminders->where('type', $this->activeFilter);
        }
        
        return $reminders->values();
    }
    
    public function getReminderCountsProperty()
    {
        $service = app(ReminderService::class);
        return $service->getReminderCounts(auth()->user());
    }
    
    public function getSettingsProperty()
    {
        $settings = ReminderSetting::where('user_id', auth()->id())
            ->pluck('enabled', 'type')
            ->toArray();
        
        // Default all to enabled
        foreach (Reminder::getTypes() as $type => $label) {
            if (!isset($settings[$type])) {
                $settings[$type] = true;
            }
        }
        
        return $settings;
    }
    
    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
    }
    
    public function toggleSetting(string $type): void
    {
        $setting = ReminderSetting::firstOrCreate(
            ['user_id' => auth()->id(), 'type' => $type],
            ['enabled' => true]
        );
        
        $setting->update(['enabled' => !$setting->enabled]);
    }
    
    public function dismissReminder(string $type, ?int $remindableId = null): void
    {
        app(ReminderService::class)->dismissReminder(auth()->user(), $type, $remindableId);
    }
    
    public static function getNavigationBadge(): ?string
    {
        $service = app(ReminderService::class);
        $counts = $service->getReminderCounts(auth()->user());
        return $counts['total'] > 0 ? (string) $counts['total'] : null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
