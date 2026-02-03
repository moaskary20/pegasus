<?php

namespace App\Filament\Pages;

use App\Models\Reminder;
use App\Models\ReminderSetting;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ReminderSettings extends Page
{
    protected static ?string $navigationLabel = 'إعدادات التذكيرات';
    
    protected static ?string $title = 'إعدادات التذكيرات';
    
    protected static ?int $navigationSort = 26;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.reminder-settings';
    
    public static function getNavigationGroup(): ?string
    {
        return 'الإشعارات';
    }
    
    public function getSettingsProperty()
    {
        $settings = ReminderSetting::where('user_id', auth()->id())->get()->keyBy('type');
        
        $result = [];
        foreach (Reminder::getTypes() as $type => $label) {
            $setting = $settings->get($type);
            $result[$type] = [
                'label' => $label,
                'icon' => Reminder::getTypeIcon($type),
                'color' => Reminder::getTypeColor($type),
                'enabled' => $setting ? $setting->enabled : true,
                'email_enabled' => $setting ? $setting->email_enabled : false,
            ];
        }
        
        return $result;
    }
    
    public function toggleEnabled(string $type): void
    {
        $setting = ReminderSetting::firstOrCreate(
            ['user_id' => auth()->id(), 'type' => $type],
            ['enabled' => true, 'email_enabled' => false]
        );
        
        $setting->update(['enabled' => !$setting->enabled]);
    }
    
    public function toggleEmail(string $type): void
    {
        $setting = ReminderSetting::firstOrCreate(
            ['user_id' => auth()->id(), 'type' => $type],
            ['enabled' => true, 'email_enabled' => false]
        );
        
        $setting->update(['email_enabled' => !$setting->email_enabled]);
    }
    
    public function enableAll(): void
    {
        foreach (Reminder::getTypes() as $type => $label) {
            ReminderSetting::updateOrCreate(
                ['user_id' => auth()->id(), 'type' => $type],
                ['enabled' => true]
            );
        }
    }
    
    public function disableAll(): void
    {
        foreach (Reminder::getTypes() as $type => $label) {
            ReminderSetting::updateOrCreate(
                ['user_id' => auth()->id(), 'type' => $type],
                ['enabled' => false]
            );
        }
    }
}
