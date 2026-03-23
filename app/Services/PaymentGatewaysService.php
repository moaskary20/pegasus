<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Config;

class PaymentGatewaysService
{
    protected static array $gatewayLabels = [
        'kashier' => 'الدفع بالفيزا والبطاقات البنكية',
        'paypal' => 'باي بال',
        'paymob' => 'باي موب',
        'stripe' => 'سترايب',
        'moyasar' => 'مويَسَر',
        'paytabs' => 'باي تابس',
        'manual' => 'تحويل/دفع يدوي (مؤقت)',
    ];

    /**
     * الحصول على طرق الدفع المفعّلة فقط [id => label]
     * يقرأ مباشرة من قاعدة البيانات لتجنّب الـ cache
     */
    public static function getEnabledPaymentMethods(): array
    {
        $methods = [];
        $settings = self::getPaymentSettingsFresh();

        if (self::isTruthy($settings['kashier_enabled'] ?? false)) {
            $methods['kashier'] = self::$gatewayLabels['kashier'];
        }
        if (self::isTruthy($settings['paypal_enabled'] ?? false)) {
            $methods['paypal'] = self::$gatewayLabels['paypal'];
        }
        if (self::isTruthy($settings['paymob_enabled'] ?? false)) {
            $methods['paymob'] = self::$gatewayLabels['paymob'];
        }
        if (self::isTruthy($settings['stripe_enabled'] ?? false)) {
            $methods['stripe'] = self::$gatewayLabels['stripe'];
        }
        if (self::isTruthy($settings['moyasar_enabled'] ?? false)) {
            $methods['moyasar'] = self::$gatewayLabels['moyasar'];
        }
        if (self::isTruthy($settings['paytabs_enabled'] ?? false)) {
            $methods['paytabs'] = self::$gatewayLabels['paytabs'];
        }
        if (self::isTruthy($settings['manual_payment_enabled'] ?? true)) {
            $methods['manual'] = self::$gatewayLabels['manual'];
        }

        return $methods;
    }

    /**
     * قراءة إعدادات الدفع مباشرة من DB بدون cache
     */
    protected static function getPaymentSettingsFresh(): array
    {
        $rows = PlatformSetting::query()
            ->where('group', 'payment')
            ->get(['key', 'value', 'type']);

        $result = [];
        foreach ($rows as $row) {
            $result[$row->key] = PlatformSetting::castValueStatic($row->value ?? '', (string) ($row->type ?? 'string'));
        }

        return $result;
    }

    protected static function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $value;
    }

    /**
     * هل البوابة المحددة تحتاج تحويل لصفحة الدفع؟
     */
    public static function isOnlineGateway(string $gateway): bool
    {
        return ! in_array($gateway, ['manual'], true);
    }

    /**
     * الحصول على رابط الدفع للتحويل إليه (كاشير فقط حالياً)
     */
    public static function getPaymentRedirectUrl(Order $order): ?string
    {
        $gateway = $order->payment_gateway;
        $settings = self::getPaymentSettingsFresh();

        if ($gateway === 'kashier' && self::isTruthy($settings['kashier_enabled'] ?? false)) {
            return self::buildKashierPaymentUrl($order, $settings);
        }

        // TODO: PayPal, Paymob, Stripe, Moyasar, PayTabs
        return null;
    }

    protected static function buildKashierPaymentUrl(Order $order, ?array $settings = null): ?string
    {
        $settings = $settings ?? self::getPaymentSettingsFresh();

        $mid = trim((string) ($settings['kashier_merchant_id'] ?? ''));
        $apiKey = trim((string) ($settings['kashier_api_key'] ?? ''));
        $encryptionKey = trim((string) ($settings['kashier_encryption_key'] ?? ''));
        $mode = (string) ($settings['kashier_mode'] ?? 'test');

        if ($mid === '' || $encryptionKey === '') {
            return null;
        }

        Config::set('kashier.mid', $mid);
        Config::set('kashier.apikey', $encryptionKey);
        Config::set('kashier.secretKey', $apiKey);
        Config::set('kashier.mode', $mode);
        Config::set('kashier.currency', 'EGP');

        try {
            $amount = (int) round((float) $order->total * 100);
            if ($amount <= 0) {
                return null;
            }
            $orderId = $order->order_number ?: (string) $order->id;

            return \Asciisd\Kashier\Facades\Kashier::buildPaymentUrl(
                $amount,
                $orderId,
                []
            );
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
