<?php

namespace Kreatiflabs\BankGuard\Contracts;

use Kreatiflabs\BankGuard\Models\FraudReport;

interface FraudCheckerInterface
{
    /**
     * Check fraud report status for a bank account.
     */
    public function check(string $bankCode, string $accountNumber): FraudReport;
}
