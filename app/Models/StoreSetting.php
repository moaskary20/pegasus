<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StoreSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];
    
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("store_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if (!$setting) {
                return $default;
            }
            
            return match ($setting->type) {
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'number' => (float) $setting->value,
                'json', 'array' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }
    
    public static function setValue(string $key, mixed $value): void
    {
        $setting = static::where('key', $key)->first();
        
        if ($setting) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $setting->update(['value' => $value]);
        }
        
        Cache::forget("store_setting_{$key}");
    }
    
    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)->get()->toArray();
    }
    
    public static function getAllSettings(): array
    {
        return Cache::remember('store_all_settings', 3600, function () {
            $settings = [];
            foreach (static::all() as $setting) {
                $settings[$setting->key] = match ($setting->type) {
                    'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                    'number' => (float) $setting->value,
                    'json', 'array' => json_decode($setting->value, true),
                    default => $setting->value,
                };
            }
            return $settings;
        });
    }
    
    public static function clearCache(): void
    {
        Cache::forget('store_all_settings');
        foreach (static::pluck('key') as $key) {
            Cache::forget("store_setting_{$key}");
        }
    }
}
