<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZoomMeeting extends Model
{
    protected $fillable = [
        'lesson_id',
        'zoom_meeting_id',
        'topic',
        'description',
        'scheduled_start_time',
        'duration',
        'timezone',
        'join_url',
        'start_url',
        'password',
        'host_id',
        'status', // pending, scheduled, started, ended, cancelled
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_start_time' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
