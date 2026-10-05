<?php

namespace Kreatiflabs\BankGuard\Exceptions;

use Exception;

class BlacklistedAccountException extends Exception
{
    public static function forAccount(string $account, ?string $reason = null): self
    {
        $message = "Bank account number '{$account}' is blacklisted and flagged as high risk.";
        if ($reason) {
            $message .= " Reason: {$reason}";
        }

        return new self($message);
    }
}
