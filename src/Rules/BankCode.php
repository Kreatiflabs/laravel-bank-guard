<?php

namespace Kreatiflabs\BankGuard\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Kreatiflabs\BankGuard\Facades\BankGuard;

class BankCode implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) && !is_numeric($value)) {
            $fail("The {$attribute} must be a valid bank code string or number.");
            return;
        }

        if (!BankGuard::exists((string) $value)) {
            $fail("The selected {$attribute} is not a recognized Indonesian bank code or alias.");
        }
    }
}
