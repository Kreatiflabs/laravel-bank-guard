<?php

namespace Kreatiflabs\BankGuard\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Kreatiflabs\BankGuard\Facades\BankGuard;

class BankAccount implements ValidationRule, DataAwareRule
{
    protected array $data = [];
    protected ?string $bankIdentifier = null;
    protected ?string $bankField = null;
    protected bool $checkBlacklist = true;

    public function __construct(?string $bankIdentifier = null)
    {
        $this->bankIdentifier = $bankIdentifier;
    }

    /**
     * Specify the form input field that contains the bank identifier (code or alias).
     */
    public function forBankField(string $fieldName): self
    {
        $this->bankField = $fieldName;
        return $this;
    }

    /**
     * Set whether to check against blacklist.
     */
    public function withBlacklistCheck(bool $check = true): self
    {
        $this->checkBlacklist = $check;
        return $this;
    }

    /**
     * Set the data under validation.
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $bankIdentifier = $this->bankIdentifier;

        if ($this->bankField !== null) {
            $bankIdentifier = data_get($this->data, $this->bankField);
        }

        if (empty($bankIdentifier)) {
            $fail("The {$attribute} requires a valid bank identifier or matching field.");
            return;
        }

        $result = BankGuard::validate((string) $bankIdentifier, (string) $value, $this->checkBlacklist);

        if (!$result['valid']) {
            $fail($result['message'] ?? "The {$attribute} is not a valid bank account number.");
        }
    }
}
