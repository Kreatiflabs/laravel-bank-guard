<?php

namespace Kreatiflabs\BankGuard;

use Illuminate\Support\Collection;
use Kreatiflabs\BankGuard\Data\BankRepository;
use Kreatiflabs\BankGuard\Enums\BankCategory;
use Kreatiflabs\BankGuard\Models\Bank;
use Kreatiflabs\BankGuard\Sanitizers\AccountSanitizer;
use Kreatiflabs\BankGuard\Validators\AccountValidator;
use Kreatiflabs\BankGuard\Validators\BlacklistGuard;

class BankGuard
{
    public function __construct(
        protected BankRepository $repository,
        protected AccountValidator $validator,
        protected BlacklistGuard $blacklistGuard
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
