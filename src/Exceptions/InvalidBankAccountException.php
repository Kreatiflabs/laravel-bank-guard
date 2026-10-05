<?php

namespace Kreatiflabs\BankGuard\Exceptions;

use Exception;

class InvalidBankAccountException extends Exception
{
    public static function forBank(string $bank, string $account, string $reason): self
    {
        return new self("Invalid account number '{$account}' for bank '{$bank}': {$reason}");
    }
}
