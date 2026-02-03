<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'price',
        'duration_days',
        'max_lessons',
    ];

    protected $casts = [
        'price' => 'float',
        'duration_days' => 'integer',
        'max_lessons' => 'integer',
    ];

    public function studentSubscriptions(): HasMany
    {
        return $this->hasMany(StudentSubscription::class);
    }

    public function getTypeArabic(): string
    {
        return match($this->type) {
            'once' => 'اشتراك واحد (120 يوم)',
            'monthly' => 'اشتراك شهري',
            'daily' => 'اشتراك يومي',
            default => $this->type,
        };
    }
}
