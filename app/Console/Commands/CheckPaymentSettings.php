<?php

namespace App\Console\Commands;

use App\Models\PlatformSetting;
use App\Services\PaymentGatewaysService;
use Illuminate\Console\Command;

class CheckPaymentSettings extends Command
{
    protected $signature = 'payment:check {--show-empty : عرض الحقول الفارغة}';

    protected $description = 'التحقق من إعدادات بوابات الدفع في قاعدة البيانات';

    public function handle(): int
    {
        $this->info('=== فحص إعدادات بوابات الدفع ===');
        $this->newLine();

        $rows = PlatformSetting::where('group', 'payment')->get(['key', 'value', 'type']);

        $settings = [];
        foreach ($rows as $row) {
            $val = $row->value ?? '';
            $settings[$row->key] = PlatformSetting::castValueStatic($val, (string) ($row->type ?? 'string'));
        }

        $keys = [
            'kashier_enabled',
            'kashier_merchant_id',
            'kashier_api_key',
            'kashier_encryption_key',
            'kashier_mode',
        ];

        foreach ($keys as $key) {
            $val = $settings[$key] ?? null;
            $display = $this->formatValue($key, $val);
            if ($display !== null || $this->option('show-empty')) {
                $this->line("  {$key}: {$display}");
            }
        }

        $methods = PaymentGatewaysService::getEnabledPaymentMethods();
        $this->newLine();
        $this->info('طرق الدفع المفعّلة: ' . implode(', ', array_keys($methods) ?: ['لا يوجد']));

        $ok = ! empty($settings['kashier_merchant_id'] ?? '') && ! empty($settings['kashier_encryption_key'] ?? '');
        $this->newLine();
        if ($ok && ($settings['kashier_enabled'] ?? false)) {
            $this->info('✓ إعدادات كاشير مكتملة وجاهزة للاستخدام.');
        } elseif ($settings['kashier_enabled'] ?? false) {
            $this->warn('⚠ كاشير مفعّل لكن بيانات التاجر ناقصة (merchant_id أو encryption_key).');
        } else {
            $this->comment('كاشير غير مفعّل في الإعدادات.');
        }

        return self::SUCCESS;
    }

    private function formatValue(string $key, mixed $val): ?string
    {
        if ($val === null || $val === '') {
            return $this->option('show-empty') ? '(فارغ)' : null;
        }

        if (str_contains($key, 'key') || str_contains($key, 'secret') || str_contains($key, 'encryption')) {
            return strlen((string) $val) > 0 ? '***' . substr((string) $val, -4) : '(فارغ)';
        }

        return (string) $val;
    }
}
