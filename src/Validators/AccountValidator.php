<?php

namespace Kreatiflabs\BankGuard\Validators;

use Kreatiflabs\BankGuard\Data\BankRepository;
use Kreatiflabs\BankGuard\Exceptions\BlacklistedAccountException;
use Kreatiflabs\BankGuard\Exceptions\InvalidBankAccountException;
use Kreatiflabs\BankGuard\Models\Bank;
use Kreatiflabs\BankGuard\Sanitizers\AccountSanitizer;

class AccountValidator
{
    public function __construct(
        protected BankRepository $repository,
        protected BlacklistGuard $blacklistGuard,
        protected bool $strict = true
    ) {}

    /**
     * Validate an account number against a given bank identifier.
     *
     * @return array{valid: bool, bank: ?Bank, account: string, message: ?string}
     */
    public function validate(string|Bank $bank, string $accountNumber, bool $checkBlacklist = true): array
    {
        $bankModel = is_string($bank) ? $this->repository->find($bank) : $bank;

        if (!$bankModel) {
            return [
                'valid' => false,
                'bank' => null,
                'account' => $accountNumber,
                'message' => is_string($bank)
                    ? "Bank with identifier '{$bank}' is not recognized."
                    : "Invalid bank instance.",
            ];
        }

        $cleanAccount = AccountSanitizer::clean($accountNumber);

        if ($cleanAccount === '') {
            return [
                'valid' => false,
                'bank' => $bankModel,
                'account' => $accountNumber,
                'message' => 'The bank account number must contain digits.',
            ];
        }

        // Anti-fraud / Blacklist validation
        if ($checkBlacklist && $this->blacklistGuard->isBlacklisted($cleanAccount, $bankModel->code)) {
            $reason = $this->blacklistGuard->getReason($cleanAccount, $bankModel->code);
            return [
                'valid' => false,
                'bank' => $bankModel,
                'account' => $cleanAccount,
                'message' => "Account is blacklisted: {$reason}",
            ];
        }

        // Bank-specific format validation
        if (!$bankModel->isValidAccount($cleanAccount, $this->strict)) {
            $expectedLengths = !empty($bankModel->account_lengths)
                ? implode(' or ', $bankModel->account_lengths) . ' digits'
                : 'standard banking format';

            return [
                'valid' => false,
                'bank' => $bankModel,
                'account' => $cleanAccount,
                'message' => "Account number for {$bankModel->short_name} must match {$expectedLengths}.",
            ];
        }

        return [
            'valid' => true,
            'bank' => $bankModel,
            'account' => $cleanAccount,
            'message' => null,
        ];
    }

    /**
     * Check and throw exceptions on failure.
     *
     * @throws InvalidBankAccountException
     * @throws BlacklistedAccountException
     */
    public function validateOrFail(string|Bank $bank, string $accountNumber, bool $checkBlacklist = true): Bank
    {
        $result = $this->validate($bank, $accountNumber, $checkBlacklist);

        if (!$result['valid']) {
            $bankName = $result['bank'] ? $result['bank']->short_name : (string) $bank;
            if ($checkBlacklist && $this->blacklistGuard->isBlacklisted($result['account'], $result['bank']?->code)) {
                throw BlacklistedAccountException::forAccount($result['account'], $result['message']);
            }

            throw InvalidBankAccountException::forBank($bankName, $result['account'], (string) $result['message']);
        }

        return $result['bank'];
    }

    /**
     * Check quickly if valid (boolean).
     */
    public function isValid(string|Bank $bank, string $accountNumber, bool $checkBlacklist = true): bool
    {
        return $this->validate($bank, $accountNumber, $checkBlacklist)['valid'];
    }
}
