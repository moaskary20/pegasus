<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * رقم هاتف مصري: 11 رقماً بعد التطبيع، يبدأ بـ 010 أو 011 أو 012 أو 015.
 */
class EgyptianMobilePhone implements ValidationRule
{
    public function __construct(
        protected bool $allowEmpty = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = User::normalizePhone((string) $value);

        if ($normalized === '') {
            if ($this->allowEmpty) {
                return;
            }
            $fail('رقم الهاتف مطلوب.');

            return;
        }

        if (! User::isValidEgyptianMobile($normalized)) {
            $fail('يجب أن يكون رقم الهاتف 11 رقماً ويبدأ بـ 010 أو 011 أو 012 أو 015.');
        }
    }
}
