<?php

namespace Kreatiflabs\BankGuard\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Kreatiflabs\BankGuard\Enums\BankCategory;
use Kreatiflabs\BankGuard\Sanitizers\AccountSanitizer;

class Bank implements Arrayable, Jsonable
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $short_name,
        public readonly array $aliases = [],
        public readonly ?string $swift_code = null,
        public readonly ?string $bi_fast_code = null,
        public readonly ?BankCategory $category = null,
        public readonly array $account_lengths = [],
        public readonly ?string $account_regex = null,
        public readonly bool $is_active = true
    ) {}

    /**
     * Create a Bank instance from an array representation.
     */
    public static function fromArray(array $data): self
    {
        $category = null;
        if (!empty($data['category'])) {
            $category = is_string($data['category'])
                ? BankCategory::tryFrom(strtolower($data['category']))
                : ($data['category'] instanceof BankCategory ? $data['category'] : null);
        }

        return new self(
            code: (string) ($data['code'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            short_name: (string) ($data['short_name'] ?? ''),
            aliases: (array) ($data['aliases'] ?? []),
            swift_code: $data['swift_code'] ?? null,
            bi_fast_code: $data['bi_fast_code'] ?? null,
            category: $category,
            account_lengths: (array) ($data['account_lengths'] ?? []),
            account_regex: $data['account_regex'] ?? null,
            is_active: (bool) ($data['is_active'] ?? true)
        );
    }

    /**
     * Check if this bank matches a given identifier (code, name, short name, or alias).
     */
    public function matches(string $identifier): bool
    {
        $normalized = strtolower(trim($identifier));
        $normalizedCode = str_pad(ltrim($normalized, '0'), 3, '0', STR_PAD_LEFT);

        if ($this->code === $normalized || $this->code === $normalizedCode) {
            return true;
        }

        if (strtolower($this->short_name) === $normalized) {
            return true;
        }

        if (strtolower($this->name) === $normalized) {
            return true;
        }

        if ($this->swift_code && strtolower($this->swift_code) === $normalized) {
            return true;
        }

        if ($this->bi_fast_code && strtolower($this->bi_fast_code) === $normalized) {
            return true;
        }

        foreach ($this->aliases as $alias) {
            if (strtolower(trim($alias)) === $normalized) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate an account number specifically for this bank.
     */
    public function isValidAccount(string $accountNumber, bool $strict = true): bool
    {
        $clean = AccountSanitizer::clean($accountNumber);

        if ($clean === '') {
            return false;
        }

        // Check exact length if specified
        if (!empty($this->account_lengths)) {
            if (!in_array(strlen($clean), $this->account_lengths, true)) {
                return false;
            }
        }

        // Check custom regex if specified
        if ($this->account_regex) {
            return (bool) preg_match('/' . trim($this->account_regex, '/') . '/', $clean);
        }

        // Default sanity check: numbers only, 8-20 digits
        if (!$strict) {
            $len = strlen($clean);
            return $len >= 8 && $len <= 20;
        }

        return true;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'aliases' => $this->aliases,
            'swift_code' => $this->swift_code,
            'bi_fast_code' => $this->bi_fast_code,
            'category' => $this->category?->value,
            'category_label' => $this->category?->label(),
            'account_lengths' => $this->account_lengths,
            'account_regex' => $this->account_regex,
            'is_active' => $this->is_active,
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
