<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayoutRequest extends Model
{
    protected $fillable = [
        'request_number',
        'user_id',
        'requested_amount',
        'commission_amount',
        'admin_fee',
        'deductions',
        'net_amount',
        'status',
        'notes',
        'rejection_reason',
        'payment_method',
        'payment_details',
        'requested_at',
        'approved_at',
        'processed_at',
        'completed_at',
        'approved_by',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'deductions' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'payment_details' => 'array',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'processed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
    
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'قيد الانتظار',
            self::STATUS_APPROVED => 'تمت الموافقة',
            self::STATUS_PROCESSING => 'قيد المعالجة',
            self::STATUS_COMPLETED => 'مكتمل',
            self::STATUS_REJECTED => 'مرفوض',
        ];
    }
    
    public static function getStatusColor(string $status): string
    {
        return match($status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'gray',
        };
    }
    
    public static function generateRequestNumber(): string
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}-{$date}-{$random}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
    
    public function voucher(): HasOne
    {
        return $this->hasOne(PaymentVoucher::class, 'payout_request_id');
    }
    
    public function transactions(): HasMany
    {
        return $this->hasMany(EarningTransaction::class, 'payout_request_id');
    }
    
    public function approve(int $approverId): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $approverId,
        ]);
    }
    
    public function reject(int $approverId, string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'approved_at' => now(),
            'approved_by' => $approverId,
        ]);
        
        // Release the transactions back to available
        $this->transactions()->update(['status' => 'available', 'payout_request_id' => null]);
    }
    
    public function markAsProcessing(int $processorId): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'processed_at' => now(),
            'processed_by' => $processorId,
        ]);
    }
    
    public function complete(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
        
        // Mark transactions as paid out
        $this->transactions()->update(['status' => 'paid_out']);
    }
}
