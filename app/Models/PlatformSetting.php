<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("platform_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value]
        );

        Cache::forget("platform_setting_{$key}");
    }

    /**
     * Get all settings for a group
     */
    public static function getGroup(string $group): array
    {
        return Cache::remember("platform_settings_group_{$group}", 3600, function () use ($group) {
            $settings = self::where('group', $group)->get();
            $result = [];

            foreach ($settings as $setting) {
                $result[$setting->key] = self::castValue($setting->value, $setting->type);
            }

            return $result;
        });
    }

    /**
     * Clear cache for a group
     */
    public static function clearGroupCache(string $group): void
    {
        Cache::forget("platform_settings_group_{$group}");
        
        $settings = self::where('group', $group)->pluck('key');
        foreach ($settings as $key) {
            Cache::forget("platform_setting_{$key}");
        }
    }

    /**
     * Cast value based on type
     */
    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            'text', 'string' => $value,
            default => $value,
        };
    }

    /**
     * Get all groups
     */
    public static function getGroups(): array
    {
        return [
            'lessons' => 'إعدادات الدروس',
            'security' => 'إعدادات الأمان',
            'social' => 'تسجيل الدخول الاجتماعي',
            'analytics' => 'التحليلات والتتبع',
            'email' => 'إعدادات البريد',
            'seo' => 'تحسين محركات البحث',
            'general' => 'إعدادات عامة',
        ];
    }
}
