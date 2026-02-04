<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'city',
        'job',
        'skills',
        'interests',
        'academic_history',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'skills' => 'array',
            'interests' => 'array',
        ];
    }

    /**
     * Check if user can access Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow all authenticated users to access the panel
        return true;
    }

    public function enrollments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function videoProgress(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VideoProgress::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function certificates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function earnings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InstructorEarning::class);
    }
    
    public function instructorEarnings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InstructorEarning::class);
    }

    public function courses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Course::class, 'user_id');
    }
    
    /**
     * Search history relationship
     */
    public function searchHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SearchHistory::class);
    }
    
    /**
     * Conversations the user participates in
     */
    public function conversations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['last_read_at', 'is_muted'])
            ->withTimestamps()
            ->orderByDesc('last_message_at');
    }
    
    /**
     * Messages sent by this user
     */
    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Message::class);
    }
    
    /**
     * Get unread messages count
     */
    public function getUnreadMessagesCountAttribute(): int
    {
        return \App\Models\Message::whereIn('conversation_id', $this->conversations()->pluck('conversations.id'))
            ->where('user_id', '!=', $this->id)
            ->whereRaw('created_at > COALESCE(
                (SELECT last_read_at FROM conversation_participants 
                 WHERE conversation_id = messages.conversation_id AND user_id = ?), 
                "1970-01-01"
            )', [$this->id])
            ->count();
    }
    
    /**
     * Point transactions relationship
     */
    public function pointTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PointTransaction::class)->orderByDesc('created_at');
    }
    
    /**
     * Reward redemptions relationship
     */
    public function rewardRedemptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }
    
    /**
     * Get rank label
     */
    public function getRankLabelAttribute(): string
    {
        return match ($this->rank ?? 'bronze') {
            'bronze' => 'برونزي',
            'silver' => 'فضي',
            'gold' => 'ذهبي',
            'platinum' => 'بلاتيني',
            'diamond' => 'ماسي',
            default => 'برونزي',
        };
    }
    
    /**
     * Get rank color
     */
    public function getRankColorAttribute(): string
    {
        return match ($this->rank ?? 'bronze') {
            'bronze' => '#cd7f32',
            'silver' => '#c0c0c0',
            'gold' => '#ffd700',
            'platinum' => '#e5e4e2',
            'diamond' => '#b9f2ff',
            default => '#cd7f32',
        };
    }
    
    /**
     * Update rank based on total points
     */
    public function updateRank(): void
    {
        $rank = match (true) {
            $this->total_points >= 10000 => 'diamond',
            $this->total_points >= 5000 => 'platinum',
            $this->total_points >= 2000 => 'gold',
            $this->total_points >= 500 => 'silver',
            default => 'bronze',
        };
        
        if ($this->rank !== $rank) {
            $this->update(['rank' => $rank]);
        }
    }
    
    /**
     * Get the full URL for avatar (يدعم المسار أو الرابط الكامل)
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $value = $this->attributes['avatar'] ?? null;
        if (empty($value)) {
            return null;
        }
        if (str_starts_with($value, 'http')) {
            return $value;
        }
        return asset('storage/' . ltrim($value, '/'));
    }

    /**
     * Scope to search users by name, email, job, city
     */
    public function scopeSearch($query, string $search)
    {
        $search = trim($search);
        
        if (strlen($search) < 2) {
            return $query;
        }
        
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('job', 'LIKE', "%{$search}%")
              ->orWhere('city', 'LIKE', "%{$search}%");
        });
    }
    
    /**
     * Scope to filter instructors only
     */
    public function scopeInstructors($query)
    {
        return $query->whereHas('roles', fn($q) => $q->where('name', 'instructor'));
    }
    
    /**
     * Scope to filter students only
     */
    public function scopeStudents($query)
    {
        return $query->whereHas('roles', fn($q) => $q->where('name', 'student'));
    }
    
    /**
     * Get published courses count for instructor
     */
    public function getPublishedCoursesCountAttribute(): int
    {
        return $this->courses()->where('is_published', true)->count();
    }

    /**
     * Student subscriptions relationship
     */
    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentSubscription::class);
    }

    /**
     * Daily lesson access relationship
     */
    public function dailyLessonAccess(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DailyLessonAccess::class);
    }

    /**
     * Voucher usage relationship
     */
    public function voucherUsages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }
}
