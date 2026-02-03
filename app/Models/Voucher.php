<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_percentage',
        'discount_amount',
        'max_usage_count',
        'usage_count',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'discount_percentage' => 'float',
        'discount_amount' => 'float',
        'max_usage_count' => 'integer',
        'usage_count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->start_date && now() < $this->start_date) {
            return false;
        }

        if ($this->end_date && now() > $this->end_date) {
            return false;
        }

        if ($this->max_usage_count && $this->usage_count >= $this->max_usage_count) {
            return false;
        }

        return true;
    }
}
