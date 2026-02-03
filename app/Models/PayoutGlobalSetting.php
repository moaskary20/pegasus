<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PayoutGlobalSetting extends Model
{
    protected $table = 'payout_global_settings';
    
    protected $fillable = [
        'key',
        'value',
        'type',
        'label',
        'description',
    ];
    
    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("payout_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }
    
    /**
     * Set a setting value
     */
    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        Cache::forget("payout_setting_{$key}");
    }
    
    /**
     * Get all settings as array
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('payout_all_settings', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }
    
    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget("payout_setting_{$setting->key}");
        }
        Cache::forget('payout_all_settings');
    }
    
    /**
     * Get default commission rate
     */
    public static function getDefaultCommissionRate(): float
    {
        return (float) static::getValue('default_commission_rate', 70);
    }
    
    /**
     * Get admin fee rate
     */
    public static function getAdminFeeRate(): float
    {
        return (float) static::getValue('admin_fee_rate', 5);
    }
    
    /**
     * Get minimum payout amount
     */
    public static function getMinimumPayout(): float
    {
        return (float) static::getValue('minimum_payout', 100);
    }
    
    /**
     * Get processing days
     */
    public static function getProcessingDays(): int
    {
        return (int) static::getValue('payout_processing_days', 7);
    }
}
