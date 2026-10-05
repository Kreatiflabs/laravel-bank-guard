<?php

namespace Kreatiflabs\BankGuard\Tests;

use Kreatiflabs\BankGuard\Sanitizers\AccountSanitizer;
use PHPUnit\Framework\TestCase as BaseTestCase;

class AccountSanitizerTest extends BaseTestCase
{
    public function test_it_cleans_non_digit_characters(): void
    {
        $this->assertEquals('1234567890', AccountSanitizer::clean('123-456-7890'));
        $this->assertEquals('1234567890', AccountSanitizer::clean(' 123 456 7890 '));
        $this->assertEquals('1234567890', AccountSanitizer::clean('123.456.7890'));
        $this->assertEquals('1234567890', AccountSanitizer::clean('abc123def456ghi7890'));
        $this->assertEquals('', AccountSanitizer::clean(null));
    }

    public function test_it_masks_account_number(): void
    {
        $account = '1234567890';
        $this->assertEquals('******7890', AccountSanitizer::mask($account, 0, 4));
        $this->assertEquals('12****7890', AccountSanitizer::mask($account, 2, 4));
        $this->assertEquals('**********', AccountSanitizer::mask($account, 6, 6));
    }
}
