<?php

namespace Kreatiflabs\BankGuard;

use Illuminate\Support\Collection;
use Kreatiflabs\BankGuard\Contracts\FraudCheckerInterface;
use Kreatiflabs\BankGuard\Data\BankRepository;
use Kreatiflabs\BankGuard\Enums\BankCategory;
use Kreatiflabs\BankGuard\Models\Bank;
use Kreatiflabs\BankGuard\Models\FraudReport;
use Kreatiflabs\BankGuard\Models\VirtualAccountInfo;
use Kreatiflabs\BankGuard\Sanitizers\AccountSanitizer;
use Kreatiflabs\BankGuard\Services\VirtualAccountDetector;
use Kreatiflabs\BankGuard\Validators\AccountValidator;
use Kreatiflabs\BankGuard\Validators\BlacklistGuard;

class BankGuard
{
    public function __construct(
        protected BankRepository $repository,
        protected AccountValidator $validator,
        protected BlacklistGuard $blacklistGuard,
        protected ?FraudCheckerInterface $fraudChecker = null
    ) {}

    /**
     * Get all banks.
     *
     * @return Collection<int, Bank>
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Find a bank by code, alias, short name, or swift code.
     */
    public function find(string $identifier): ?Bank
    {
        return $this->repository->find($identifier);
    }

    /**
     * Find a bank or throw BankNotFoundException.
     */
    public function findOrFail(string $identifier): Bank
    {
        return $this->repository->findOrFail($identifier);
    }

    /**
     * Determine if a bank identifier exists.
     */
    public function exists(string $identifier): bool
    {
        return $this->repository->exists($identifier);
    }

    /**
     * Get banks filtered by category (BUMN, SWASTA, SYARIAH, DIGITAL, BPD).
     *
     * @return Collection<int, Bank>
     */
    public function category(BankCategory|string $category): Collection
    {
        return $this->repository->getByCategory($category);
    }

    /**
     * Search banks by keyword.
     *
     * @return Collection<int, Bank>
     */
    public function search(string $query): Collection
    {
        return $this->repository->search($query);
    }

    /**
     * Get array of all 3-digit bank codes.
     *
     * @return array<int, string>
     */
    public function codes(): array
    {
        return $this->repository->codes();
    }

    /**
     * Clean and sanitize bank account number (digits only).
     */
    public function sanitize(?string $accountNumber): string
    {
        return AccountSanitizer::clean($accountNumber);
    }

    /**
     * Mask bank account number for secure display.
     */
    public function mask(string $accountNumber, int $visibleStart = 0, int $visibleEnd = 4, string $maskChar = '*'): string
    {
        return AccountSanitizer::mask($accountNumber, $visibleStart, $visibleEnd, $maskChar);
    }

    /**
     * Validate an account number against a bank.
     *
     * @return array{valid: bool, bank: ?Bank, account: string, message: ?string}
     */
    public function validate(string|Bank $bank, string $accountNumber, bool $checkBlacklist = true): array
    {
        return $this->validator->validate($bank, $accountNumber, $checkBlacklist);
    }

    /**
     * Quick boolean check if bank account number is valid.
     */
    public function isValid(string|Bank $bank, string $accountNumber, bool $checkBlacklist = true): bool
    {
        return $this->validator->isValid($bank, $accountNumber, $checkBlacklist);
    }

    /**
     * Validate an account number or throw an exception.
     */
    public function validateOrFail(string|Bank $bank, string $accountNumber, bool $checkBlacklist = true): Bank
    {
        return $this->validator->validateOrFail($bank, $accountNumber, $checkBlacklist);
    }

    /**
     * Check if an account number is blacklisted.
     */
    public function isBlacklisted(string $accountNumber, ?string $bankIdentifier = null): bool
    {
        return $this->blacklistGuard->isBlacklisted($accountNumber, $bankIdentifier);
    }

    /**
     * Get all supported E-Wallets in Indonesia.
     *
     * @return Collection<int, Bank>
     */
    public function ewallets(): Collection
    {
        return $this->category(BankCategory::EWALLET);
    }

    /**
     * Detect if an account number is an Indonesian Bank Virtual Account.
     */
    public function detectVirtualAccount(string|Bank $bank, string $accountNumber): VirtualAccountInfo
    {
        $bankModel = is_string($bank) ? $this->find($bank) : $bank;
        $bankCode = $bankModel ? $bankModel->code : (string) $bank;

        return VirtualAccountDetector::detect($bankCode, $accountNumber);
    }

    /**
     * Check if an account number is a Virtual Account.
     */
    public function isVirtualAccount(string|Bank $bank, string $accountNumber): bool
    {
        return $this->detectVirtualAccount($bank, $accountNumber)->is_virtual_account;
    }

    /**
     * Check live fraud report status for a bank account (CekRekening.id / Local).
     */
    public function checkFraud(string|Bank $bank, string $accountNumber): FraudReport
    {
        $bankModel = is_string($bank) ? $this->find($bank) : $bank;
        $bankCode = $bankModel ? $bankModel->code : (string) $bank;
        $clean = $this->sanitize($accountNumber);

        if ($this->fraudChecker) {
            return $this->fraudChecker->check($bankCode, $clean);
        }

        // Fallback: check blacklist guard
        if ($this->isBlacklisted($clean, $bankCode)) {
            $reason = $this->blacklistGuard->getReason($clean, $bankCode);
            return FraudReport::flagged($bankCode, $clean, 1, $reason, 'BankGuard Blacklist');
        }

        return FraudReport::clean($bankCode, $clean, 'BankGuard Blacklist');
    }

    public function fraudChecker(): ?FraudCheckerInterface
    {
        return $this->fraudChecker;
    }

    public function repository(): BankRepository
    {
        return $this->repository;
    }

    public function validator(): AccountValidator
    {
        return $this->validator;
    }

    public function blacklistGuard(): BlacklistGuard
    {
        return $this->blacklistGuard;
    }
}
