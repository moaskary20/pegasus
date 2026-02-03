<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorPayoutSetting extends Model
{
    protected $fillable = [
        'user_id',
        'commission_rate',
        'admin_fee_rate',
        'minimum_payout',
        'payment_method',
        'payment_details',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'admin_fee_rate' => 'decimal:2',
            'minimum_payout' => 'decimal:2',
            'payment_details' => 'array',
            'is_verified' => 'boolean',
        ];
    }
    
    public static function getPaymentMethods(): array
    {
        return [
            'bank_transfer' => 'تحويل بنكي',
            'vodafone_cash' => 'فودافون كاش',
            'instapay' => 'انستاباي',
            'paypal' => 'PayPal',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function getPaymentMethodLabel(): string
    {
        return self::getPaymentMethods()[$this->payment_method] ?? $this->payment_method;
    }
}
