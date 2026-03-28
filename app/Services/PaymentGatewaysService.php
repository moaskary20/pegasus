<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class PaymentGatewaysService
{
    /** يطابق حزمة asciisd/kashier — المبلغ بالعملة الأساسية (جنيه) وليس بالقرش */
    private const KASHIER_CHECKOUT_BASE_URL = 'https://checkout.kashier.io/';

    private const KASHIER_DEFAULT_ALLOWED_METHODS = 'card,wallet,bank_installments';

    /** سبب فشل آخر محاولة للحصول على رابط الدفع (للتشخيص) */
    public static ?string $lastFailureReason = null;

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
     * قراءة إعدادات الدفع مباشرة من DB بدون cache.
     * يستخدم Query Builder مباشرة (بدون Eloquent) لتجنّب أي كاش أو مشاكل في بيئات الإنتاج.
     */
    protected static function getPaymentSettingsFresh(): array
    {
        $result = [];

        try {
            $rows = DB::table('platform_settings')
                ->where('group', 'payment')
                ->get(['key', 'value', 'type']);
        } catch (\Throwable $e) {
            Log::error('PaymentGatewaysService: فشل قراءة إعدادات الدفع من DB', ['error' => $e->getMessage()]);

            return [];
        }

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
        self::$lastFailureReason = null;
        $gateway = $order->payment_gateway;
        $settings = self::getPaymentSettingsFresh();

        $kashierEnabled = self::isTruthy($settings['kashier_enabled'] ?? false);
        if ($gateway === 'kashier' && $kashierEnabled) {
            $url = self::buildKashierPaymentUrl($order, $settings);
            if ($url) {
                return $url;
            }
            return null; // lastFailureReason يحدّده buildKashierPaymentUrl
        }

        self::$lastFailureReason = 'gateway_not_supported';
        return null;
    }

    protected static function buildKashierPaymentUrl(Order $order, ?array $settings = null): ?string
    {
        $settings = $settings ?? self::getPaymentSettingsFresh();

        $mid = trim((string) ($settings['kashier_merchant_id'] ?? ''));
        $apiKey = trim((string) ($settings['kashier_api_key'] ?? ''));
        $mode = (string) ($settings['kashier_mode'] ?? 'test');

        // توقيع صفحة الدفع واستجابة كاشير يعتمدان على API Key (سر التوقيع)، وليس مفتاح التشفير الاختياري.
        // انظر: Asciisd\Kashier\KashierService::generateOrderHash و config('kashier.apikey').
        if ($mid === '' || $apiKey === '') {
            self::$lastFailureReason = 'missing_credentials';
            Log::warning('Kashier: بيانات غير مكتملة في platform_settings', [
                'has_mid' => $mid !== '',
                'has_api_key' => $apiKey !== '',
                'order_id' => $order->id,
                'keys_from_db' => array_keys($settings),
            ]);

            return null;
        }

        Config::set('kashier.mid', $mid);
        Config::set('kashier.apikey', $apiKey);
        Config::set('kashier.secretKey', $apiKey);
        Config::set('kashier.mode', $mode);
        Config::set('kashier.currency', 'EGP');

        try {
            $total = round((float) $order->total, 2);
            if ($total <= 0) {
                self::$lastFailureReason = 'zero_amount';
                Log::warning('Kashier: مبلغ الطلب صفر أو سالب', [
                    'order_id' => $order->id,
                    'order_total' => $order->total,
                ]);

                return null;
            }

            // كاشير يبني الـ hash من المسار: /?payment={mid}.{orderId}.{amount}.{currency}
            // المبلغ يجب أن يكون بالجنيه (نفس WooCommerce: order total) وليس ×100 — وإلا يظهر على البوابة ضعف منزلتين (00 زائدة).
            $amountStr = number_format($total, 2, '.', '');
            $orderId = $order->order_number ?: (string) $order->id;
            $currency = (string) config('kashier.currency');
            $path = "/?payment={$mid}.{$orderId}.{$amountStr}.{$currency}";
            $hash = hash_hmac('sha256', $path, $apiKey, false);

            $callbackUrl = URL::to('/kashier/response');
            $webhookUrl = URL::to('/kashier/webhook');

            $queryParams = [
                'merchantId' => $mid,
                'orderId' => $orderId,
                'amount' => $amountStr,
                'currency' => $currency,
                'mode' => (string) config('kashier.mode'),
                'hash' => $hash,
                'merchantRedirect' => $callbackUrl,
                'serverWebhook' => $webhookUrl,
                'allowedMethods' => self::KASHIER_DEFAULT_ALLOWED_METHODS,
                'display' => 'en',
                'redirectMethod' => 'post',
            ];

            return self::KASHIER_CHECKOUT_BASE_URL.'?'.http_build_query($queryParams);
        } catch (\Throwable $e) {
            self::$lastFailureReason = 'exception:'.$e->getMessage();
            Log::error('Kashier: فشل إنشاء رابط الدفع', [
                'order_id' => $order->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            report($e);

            return null;
        }
    }
}
