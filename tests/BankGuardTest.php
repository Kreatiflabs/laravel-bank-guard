<?php

namespace Kreatiflabs\BankGuard\Tests;

use Kreatiflabs\BankGuard\Facades\BankGuard;

class BankGuardTest extends TestCase
{
    public function test_facade_all_returns_collection_of_banks(): void
    {
        $banks = BankGuard::all();
        $this->assertNotEmpty($banks);
    }

    public function test_facade_find_and_exists(): void
    {
        $this->assertTrue(BankGuard::exists('014'));
        $this->assertTrue(BankGuard::exists('bca'));
        $this->assertTrue(BankGuard::exists('CENAIDJA'));
        $this->assertFalse(BankGuard::exists('non_existent_bank_xyz'));

        $bank = BankGuard::find('bca');
        $this->assertNotNull($bank);
        $this->assertEquals('014', $bank->code);
    }

    public function test_facade_codes(): void
    {
        $codes = BankGuard::codes();
        $this->assertIsArray($codes);
        $this->assertContains('014', $codes);
        $this->assertContains('008', $codes);
    }

    public function test_facade_mask(): void
    {
        $masked = BankGuard::mask('1234567890', 2, 4);
        $this->assertEquals('12****7890', $masked);
    }

    public function test_facade_sanitize(): void
    {
        $sanitized = BankGuard::sanitize('123-456-7890');
        $this->assertEquals('1234567890', $sanitized);
    }
}
