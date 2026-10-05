<?php

namespace Kreatiflabs\BankGuard\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Kreatiflabs\BankGuard\Enums\BankCategory;
use Kreatiflabs\BankGuard\Models\Bank;

/**
 * @method static Collection all()
 * @method static Bank|null find(string $identifier)
 * @method static Bank findOrFail(string $identifier)
 * @method static bool exists(string $identifier)
 * @method static Collection category(BankCategory|string $category)
 * @method static Collection search(string $query)
 * @method static array codes()
 * @method static string sanitize(?string $accountNumber)
 * @method static string mask(string $accountNumber, int $visibleStart = 0, int $visibleEnd = 4, string $maskChar = '*')
 * @method static array validate(string|Bank $bank, string $accountNumber, bool $checkBlacklist = true)
 * @method static bool isValid(string|Bank $bank, string $accountNumber, bool $checkBlacklist = true)
 * @method static Bank validateOrFail(string|Bank $bank, string $accountNumber, bool $checkBlacklist = true)
 * @method static bool isBlacklisted(string $accountNumber, ?string $bankIdentifier = null)
 * @method static Collection ewallets()
 * @method static \Kreatiflabs\BankGuard\Models\VirtualAccountInfo detectVirtualAccount(string|Bank $bank, string $accountNumber)
 * @method static bool isVirtualAccount(string|Bank $bank, string $accountNumber)
 * @method static \Kreatiflabs\BankGuard\Models\FraudReport checkFraud(string|Bank $bank, string $accountNumber)
 *
 * @see \Kreatiflabs\BankGuard\BankGuard
 */
class BankGuard extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bank-guard';
    }
}
