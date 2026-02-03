<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVoucher extends Model
{
    protected $fillable = [
        'voucher_number',
        'payout_request_id',
        'user_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'payment_proof',
        'notes',
        'issued_at',
        'issued_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_proof' => 'array',
            'issued_at' => 'datetime',
        ];
    }
    
    public static function generateVoucherNumber(): string
    {
        $prefix = 'VCH';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}-{$date}-{$random}";
    }

    public function payoutRequest(): BelongsTo
    {
        return $this->belongsTo(PayoutRequest::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
