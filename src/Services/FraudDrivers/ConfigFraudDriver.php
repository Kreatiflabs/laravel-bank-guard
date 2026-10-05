<?php

namespace Kreatiflabs\BankGuard\Services\FraudDrivers;

use Kreatiflabs\BankGuard\Contracts\FraudCheckerInterface;
use Kreatiflabs\BankGuard\Models\FraudReport;
use Kreatiflabs\BankGuard\Validators\BlacklistGuard;

class ConfigFraudDriver implements FraudCheckerInterface
{
    public function __construct(
        protected BlacklistGuard $blacklistGuard
    ) {}

    public function check(string $bankCode, string $accountNumber): FraudReport
    {
        if ($this->blacklistGuard->isBlacklisted($accountNumber, $bankCode)) {
            $reason = $this->blacklistGuard->getReason($accountNumber, $bankCode);
            return FraudReport::flagged(
                bankCode: $bankCode,
                accountNumber: $accountNumber,
                reports: 1,
                reason: $reason,
                source: 'BankGuard Blacklist'
            );
        }

        return FraudReport::clean(
            bankCode: $bankCode,
            accountNumber: $accountNumber,
            source: 'BankGuard Blacklist'
        );
    }
}
