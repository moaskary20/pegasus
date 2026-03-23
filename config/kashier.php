<?php

return [
    'mid' => env('KASHIER_MID', ''),
    'apikey' => env('KASHIER_API_KEY', ''),
    'secretKey' => env('KASHIER_SECRET_KEY', ''),
    'mode' => env('KASHIER_MODE', 'test'),
    'currency' => env('KASHIER_CURRENCY', 'EGP'),
    // بعد نجاح الدفع: إعادة التوجيه لصفحة إتمام الطلب
    'callbackUrl' => env('KASHIER_CALLBACK_URL')
        ?: (rtrim(env('APP_URL', 'http://localhost'), '/') . '/checkout/kashier/complete'),
];
