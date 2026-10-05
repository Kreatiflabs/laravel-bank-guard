<?php

namespace Kreatiflabs\BankGuard\Validators;

use Closure;
use Kreatiflabs\BankGuard\Sanitizers\AccountSanitizer;

class BlacklistGuard
{
    /**
     * @var array<int|string, mixed>
     */
    protected array $blacklist = [];

    /**
     * @var Closure|null
     */
    protected static ?Closure $customResolver = null;

    public function __construct(array $blacklist = [])
    {
        $this->blacklist = $blacklist;
    }

    /**
     * Register a custom callback for dynamically checking blacklist (e.g. database query, external API).
     */
    public static function resolveUsing(?Closure $resolver): void
    {
        static::$customResolver = $resolver;
    }

    /**
     * Check if an account number is blacklisted.
     */
    public function isBlacklisted(string $accountNumber, ?string $bankIdentifier = null): bool
    {
        $clean = AccountSanitizer::clean($accountNumber);

        if ($clean === '') {
            return false;
        }

        // 1. Custom callback resolver check
        if (static::$customResolver) {
            $result = call_user_func(static::$customResolver, $clean, $bankIdentifier);
            if ($result === true || (is_array($result) && !empty($result['blacklisted']))) {
                return true;
            }
        }

        // 2. Configured blacklist check
        foreach ($this->blacklist as $entry) {
            if (is_string($entry)) {
                if (AccountSanitizer::clean($entry) === $clean) {
                    return true;
                }
            } elseif (is_array($entry)) {
                $entryAcc = AccountSanitizer::clean($entry['account'] ?? '');
                if ($entryAcc === $clean) {
                    if (empty($entry['bank']) || empty($bankIdentifier)) {
                        return true;
                    }
                    if (strtolower(trim($entry['bank'])) === strtolower(trim($bankIdentifier))) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get the reason why an account is blacklisted, if specified.
     */
    public function getReason(string $accountNumber, ?string $bankIdentifier = null): ?string
    {
        $clean = AccountSanitizer::clean($accountNumber);

        foreach ($this->blacklist as $entry) {
            if (is_array($entry) && AccountSanitizer::clean($entry['account'] ?? '') === $clean) {
                return $entry['reason'] ?? 'Flagged as high-risk account.';
            }
        }

        return 'Account is present on the security blacklist.';
    }
}
