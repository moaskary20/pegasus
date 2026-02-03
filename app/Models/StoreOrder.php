<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreOrder extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'payment_status',
        'payment_method',
        'transaction_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'shipping_postal_code',
        'billing_address',
        'billing_city',
        'subtotal',
        'shipping_cost',
        'tax_amount',
        'discount_amount',
        'total',
        'coupon_code',
        'shipping_method',
        'tracking_number',
        'notes',
        'admin_notes',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];
    
    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
    
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_REFUNDED = 'refunded';
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $prefix = StoreSetting::getValue('order_prefix', 'ORD-');
                $order->order_number = $prefix . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function items(): HasMany
    {
        return $this->hasMany(StoreOrderItem::class);
    }
    
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    
    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }
    
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'قيد الانتظار',
            self::STATUS_PROCESSING => 'قيد المعالجة',
            self::STATUS_SHIPPED => 'تم الشحن',
            self::STATUS_DELIVERED => 'تم التسليم',
            self::STATUS_CANCELLED => 'ملغي',
            self::STATUS_REFUNDED => 'مسترد',
            default => 'غير محدد',
        };
    }
    
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_SHIPPED => 'primary',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_REFUNDED => 'gray',
            default => 'gray',
        };
    }
    
    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_PENDING => 'في انتظار الدفع',
            self::PAYMENT_PAID => 'مدفوع',
            self::PAYMENT_FAILED => 'فشل الدفع',
            self::PAYMENT_REFUNDED => 'مسترد',
            default => 'غير محدد',
        };
    }
    
    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }
    
    public function markAsPaid(): void
    {
        $this->update([
            'payment_status' => self::PAYMENT_PAID,
            'paid_at' => now(),
        ]);
    }
    
    public function markAsShipped(?string $trackingNumber = null): void
    {
        $this->update([
            'status' => self::STATUS_SHIPPED,
            'tracking_number' => $trackingNumber,
            'shipped_at' => now(),
        ]);
    }
    
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);
    }
    
    public function cancel(): void
    {
        // Restore stock
        foreach ($this->items as $item) {
            if ($item->product) {
                $item->product->incrementStock($item->quantity);
            }
        }
        
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }
}
