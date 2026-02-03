<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorEarning extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'earnings_type',
        'earnings_value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'earnings_value' => 'decimal:2',
            'is_active' => 'boolean',
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

    /**
     * حساب الأرباح بناءً على نوع الأرباح والطلاب المسجلين
     */
    public function calculateTotalEarnings(): float
    {
        if (!$this->is_active) {
            return 0;
        }

        $enrollments = $this->course->enrollments;
        $total = 0;

        // استخدام سعر الدورة الأصلي (offer_price أو price)
        $coursePrice = $this->course->offer_price ?? $this->course->price;

        if ($this->earnings_type === 'percentage') {
            // حساب النسبة المئوية من سعر الدورة × عدد الطلاب
            $total = ($coursePrice * $this->earnings_value / 100) * $enrollments->count();
        } else {
            // مبلغ ثابت لكل طالب
            $total = $this->earnings_value * $enrollments->count();
        }

        return $total;
    }

    /**
     * حساب إجمالي المدفوعات للدورة
     */
    public function getTotalPayments(): float
    {
        return $this->course->enrollments->sum('price_paid');
    }

    /**
     * عدد الطلاب المسجلين في الدورة
     */
    public function getStudentsCount(): int
    {
        return $this->course->enrollments->count();
    }
}
