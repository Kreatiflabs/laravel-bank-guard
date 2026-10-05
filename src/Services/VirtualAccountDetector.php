<?php

namespace Kreatiflabs\BankGuard\Services;

use Kreatiflabs\BankGuard\Models\VirtualAccountInfo;
use Kreatiflabs\BankGuard\Sanitizers\AccountSanitizer;

class VirtualAccountDetector
{
    /**
     * Common Indonesian Bank Virtual Account Prefix Mapping.
     */
    protected static array $prefixes = [
        '014' => [ // BCA
            '3901'  => 'GoPay / DANA',
            '39358' => 'OVO',
            '122'   => 'ShopeePay',
        ],
        '008' => [ // Mandiri
            '60737' => 'GoPay',
            '84000' => 'OVO',
            '89508' => 'DANA',
            '893'   => 'ShopeePay',
            '88708' => 'Tokopedia',
        ],
        '002' => [ // BRI (BRIVA)
            '301341' => 'GoPay',
            '88099'  => 'OVO',
            '88810'  => 'DANA',
            '112'    => 'ShopeePay',
        ],
        '009' => [ // BNI
            '9003' => 'GoPay',
            '8740' => 'OVO',
            '8810' => 'DANA',
            '8807' => 'ShopeePay',
        ],
        '013' => [ // Permata
            '898'  => 'GoPay',
            '84'   => 'OVO',
            '8528' => 'DANA',
        ],
        '022' => [ // CIMB Niaga
            '2849' => 'GoPay',
            '8099' => 'OVO',
        ],
    ];

    /**
     * Detect if an account number is a Virtual Account.
     */
    public static function detect(string $bankCode, string $accountNumber): VirtualAccountInfo
    {
        $clean = AccountSanitizer::clean($accountNumber);
        $normalizedBank = str_pad(ltrim($bankCode, '0'), 3, '0', STR_PAD_LEFT);

        if (isset(static::$prefixes[$normalizedBank])) {
            foreach (static::$prefixes[$normalizedBank] as $prefix => $provider) {
                if (str_starts_with($clean, $prefix)) {
                    $customerNumber = substr($clean, strlen($prefix));
                    return new VirtualAccountInfo(
                        is_virtual_account: true,
                        bank_code: $normalizedBank,
                        bank_name: static::getBankName($normalizedBank),
                        provider: $provider,
                        prefix: $prefix,
                        customer_number: $customerNumber,
                        raw_number: $clean
                    );
                }
            }
        }

        return new VirtualAccountInfo(
            is_virtual_account: false,
            bank_code: $normalizedBank,
            bank_name: static::getBankName($normalizedBank),
            provider: null,
            prefix: null,
            customer_number: null,
            raw_number: $clean
        );
    }

    protected static function getBankName(string $code): string
    {
        return match ($code) {
            '014' => 'BCA',
            '008' => 'Mandiri',
            '002' => 'BRI',
            '009' => 'BNI',
            '013' => 'Permata',
            '022' => 'CIMB Niaga',
            default => 'Bank',
        };
    }
}
