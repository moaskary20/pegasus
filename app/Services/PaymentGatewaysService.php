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
     */
    public static function getEnabledPaymentMethods(): array
    {
        $methods = [];

        if (PlatformSetting::get('kashier_enabled', false)) {
            $methods['kashier'] = self::$gatewayLabels['kashier'];
        }
        if (PlatformSetting::get('paypal_enabled', false)) {
            $methods['paypal'] = self::$gatewayLabels['paypal'];
        }
        if (PlatformSetting::get('paymob_enabled', false)) {
            $methods['paymob'] = self::$gatewayLabels['paymob'];
        }
        if (PlatformSetting::get('stripe_enabled', false)) {
            $methods['stripe'] = self::$gatewayLabels['stripe'];
        }
        if (PlatformSetting::get('moyasar_enabled', false)) {
            $methods['moyasar'] = self::$gatewayLabels['moyasar'];
        }
        if (PlatformSetting::get('paytabs_enabled', false)) {
            $methods['paytabs'] = self::$gatewayLabels['paytabs'];
        }
        if (PlatformSetting::get('manual_payment_enabled', true)) {
            $methods['manual'] = self::$gatewayLabels['manual'];
        }

        return $methods;
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

        if ($gateway === 'kashier' && PlatformSetting::get('kashier_enabled', false)) {
            return self::buildKashierPaymentUrl($order);
        }

        // TODO: PayPal, Paymob, Stripe, Moyasar, PayTabs
        return null;
    }

    protected static function buildKashierPaymentUrl(Order $order): ?string
    {
        $mid = PlatformSetting::get('kashier_merchant_id', '');
        $apiKey = PlatformSetting::get('kashier_api_key', '');
        $encryptionKey = PlatformSetting::get('kashier_encryption_key', '');
        $mode = PlatformSetting::get('kashier_mode', 'test');

        if (empty($mid) || empty($encryptionKey)) {
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
            $paymentUrl = \Asciisd\Kashier\Facades\Kashier::buildPaymentUrl(
                $amount,
                $orderId,
                []
            );
            return $paymentUrl;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }
}
