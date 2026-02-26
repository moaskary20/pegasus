<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'voucher_id',
        'start_date',
        'end_date',
        'status',
        'final_price',
        'manual_receipt_path',
        'manual_receipt_original_name',
        'manual_receipt_uploaded_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'final_price' => 'float',
        'manual_receipt_uploaded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class)->withDefault();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && now() <= $this->end_date;
    }
}
