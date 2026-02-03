<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderSetting extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'enabled',
        'email_enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'email_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
