<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarningTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'enrollment_id',
        'course_id',
        'sale_amount',
        'commission_rate',
        'commission_amount',
        'platform_amount',
        'status',
        'payout_request_id',
    ];

    protected function casts(): array
    {
        return [
            'sale_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'platform_amount' => 'decimal:2',
        ];
    }
    
    const STATUS_AVAILABLE = 'available';
    const STATUS_PENDING_PAYOUT = 'pending_payout';
    const STATUS_PAID_OUT = 'paid_out';
    
    public static function getStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE => 'متاح للسحب',
            self::STATUS_PENDING_PAYOUT => 'قيد السحب',
            self::STATUS_PAID_OUT => 'تم الدفع',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }
    
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
    
    public function payoutRequest(): BelongsTo
    {
        return $this->belongsTo(PayoutRequest::class);
    }
}
