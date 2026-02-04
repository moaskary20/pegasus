<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportComplaint extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
    ];

    public const TYPE_COMPLAINT = 'complaint';
    public const TYPE_CONTACT = 'contact';

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
