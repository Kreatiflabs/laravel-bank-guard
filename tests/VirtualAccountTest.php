<?php

namespace Kreatiflabs\BankGuard\Tests;

use Kreatiflabs\BankGuard\Facades\BankGuard;

class VirtualAccountTest extends TestCase
{
    public function test_it_detects_bca_gopay_virtual_account(): void
    {
        // BCA GoPay prefix: 3901 + 08123456789
        $vaInfo = BankGuard::detectVirtualAccount('bca', '390108123456789');

        $this->assertTrue($vaInfo->is_virtual_account);
        $this->assertEquals('3901', $vaInfo->prefix);
        $this->assertStringContainsString('GoPay', $vaInfo->provider);
        $this->assertEquals('08123456789', $vaInfo->customer_number);

        $this->assertTrue(BankGuard::isVirtualAccount('bca', '390108123456789'));
    }

    public function test_it_detects_mandiri_ovo_virtual_account(): void
    {
        // Mandiri OVO prefix: 84000
        $vaInfo = BankGuard::detectVirtualAccount('008', '84000081234567890');

        $this->assertTrue($vaInfo->is_virtual_account);
        $this->assertEquals('OVO', $vaInfo->provider);
        $this->assertEquals('84000', $vaInfo->prefix);
    }

    public function test_it_returns_false_for_regular_account(): void
    {
        $vaInfo = BankGuard::detectVirtualAccount('bca', '1234567890');

        $this->assertFalse($vaInfo->is_virtual_account);
        $this->assertNull($vaInfo->provider);
        $this->assertFalse(BankGuard::isVirtualAccount('bca', '1234567890'));
    }
}
