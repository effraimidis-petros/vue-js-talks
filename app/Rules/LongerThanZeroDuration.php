<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LongerThanZeroDuration implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (array_sum(array_map('intval', explode(':', $value))) === 0) {
            $fail(__('Duration must be longer than 0:00.'));
        }
    }
}
