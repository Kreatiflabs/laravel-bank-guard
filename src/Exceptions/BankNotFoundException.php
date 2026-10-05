<?php

namespace Kreatiflabs\BankGuard\Exceptions;

use Exception;

class BankNotFoundException extends Exception
{
    public static function forIdentifier(string $identifier): self
    {
        return new self("Bank with identifier '{$identifier}' could not be found.");
    }
}
