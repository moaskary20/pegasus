<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * يمنع تسجيل نفس رقم الهاتف بصيغ مختلفة (مثل 01... و +20...).
 */
class UniqueNormalizedPhone implements ValidationRule
{
    public function __construct(
        private ?int $ignoreUserId = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $normalized = User::normalizePhone((string) $value);
        if ($normalized === '') {
            return;
        }

        foreach (User::query()->whereNotNull('phone')->where('phone', '!=', '')->cursor() as $user) {
            if ($this->ignoreUserId !== null && (int) $user->id === $this->ignoreUserId) {
                continue;
            }
            if (User::normalizePhone((string) $user->phone) === $normalized) {
                $fail('هذا رقم الهاتف مسجّل مسبقاً.');

                return;
            }
        }
    }
}
