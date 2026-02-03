<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'order_id',
        'price_paid',
        'enrolled_at',
        'completed_at',
        'progress_percentage',
        'subscription_type',
        'access_expires_at',
        'sessions_total',
        'sessions_remaining',
        'payment_method',
        'voucher_code',
        'voucher_id',
    ];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
            'progress_percentage' => 'decimal:2',
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
            'access_expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
