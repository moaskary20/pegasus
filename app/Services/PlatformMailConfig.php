<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Config;

class PlatformMailConfig
{
    /**
     * تطبيق إعدادات البريد من لوحة التحكم على إعدادات Laravel
     * يتم استخدام الإعدادات المحفوظة في الإعدادات العامة - إعدادات البريد الإلكتروني
     */
    public static function apply(): void
    {
        try {
            $provider = PlatformSetting::get('email_provider', 'smtp');

            if ($provider === 'smtp') {
                self::applySmtpConfig();
            } elseif ($provider === 'brevo') {
                self::applyBrevoConfig();
            }

            self::applyFromAddress();
        } catch (\Throwable $e) {
            // في حال فشل تحميل الإعدادات (مثلاً أثناء التنقلات)، نستخدم .env
            report($e);
        }
    }

    protected static function applySmtpConfig(): void
    {
        $host = PlatformSetting::get('smtp_host', '');
        if (empty($host)) {
            return;
        }

        $encryption = PlatformSetting::get('smtp_encryption', 'tls') ?: null;
        $port = (int) PlatformSetting::get('smtp_port', 587);
        $scheme = ($encryption === 'ssl' || $port === 465) ? 'smtps' : 'smtp';

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', array_merge(
            Config::get('mail.mailers.smtp', []),
            [
                'transport' => 'smtp',
                'host' => $host,
                'port' => $port,
                'scheme' => $scheme,
                'username' => PlatformSetting::get('smtp_username', ''),
                'password' => PlatformSetting::get('smtp_password', ''),
                'timeout' => null,
            ]
        ));
    }

    protected static function applyBrevoConfig(): void
    {
        $apiKey = PlatformSetting::get('brevo_api_key', '');
        if (empty($apiKey)) {
            return;
        }

        // Brevo يوفر SMTP - نستخدم الإعدادات القياسية لـ Brevo
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', array_merge(
            Config::get('mail.mailers.smtp', []),
            [
                'transport' => 'smtp',
                'host' => 'smtp-relay.brevo.com',
                'port' => 587,
                'scheme' => 'smtp',
                'username' => PlatformSetting::get('brevo_sender_email', ''),
                'password' => $apiKey,
                'timeout' => null,
            ]
        ));
    }

    protected static function applyFromAddress(): void
    {
        $address = PlatformSetting::get('email_from_address', '');
        $name = PlatformSetting::get('email_from_name', config('app.name'));

        if (!empty($address)) {
            Config::set('mail.from', [
                'address' => $address,
                'name' => $name,
            ]);
        } elseif (PlatformSetting::get('email_provider') === 'brevo') {
            $brevoEmail = PlatformSetting::get('brevo_sender_email', '');
            $brevoName = PlatformSetting::get('brevo_sender_name', config('app.name'));
            if (!empty($brevoEmail)) {
                Config::set('mail.from', [
                    'address' => $brevoEmail,
                    'name' => $brevoName,
                ]);
            }
        }
    }

    /**
     * هل إعدادات البريد من لوحة التحكم مُعدّة وجاهزة؟
     */
    public static function isConfigured(): bool
    {
        try {
            $provider = PlatformSetting::get('email_provider', 'smtp');
            if ($provider === 'smtp') {
                return !empty(PlatformSetting::get('smtp_host', ''));
            }
            if ($provider === 'brevo') {
                return !empty(PlatformSetting::get('brevo_api_key', ''))
                    && !empty(PlatformSetting::get('brevo_sender_email', ''));
            }
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
