<?php

namespace Kreatiflabs\BankGuard\Tests;

use Kreatiflabs\BankGuard\Facades\BankGuard;

class EWalletTest extends TestCase
{
    public function test_it_loads_ewallets(): void
    {
        $ewallets = BankGuard::ewallets();

        $this->assertNotEmpty($ewallets);
        $codes = $ewallets->pluck('code')->all();

        $this->assertContains('GOPAY', $codes);
        $this->assertContains('OVO', $codes);
        $this->assertContains('DANA', $codes);
        $this->assertContains('SHOPEEPAY', $codes);
        $this->assertContains('LINKAJA', $codes);
    }

    public function test_it_validates_gopay_number(): void
    {
        // Valid 08xx (11-12 digits)
        $valid = BankGuard::validate('gopay', '081234567890');
        $this->assertTrue($valid['valid']);

        // Valid with format 628xx
        $valid62 = BankGuard::validate('gopay', '6281234567890');
        $this->assertTrue($valid62['valid']);

        // Invalid: too short or non-phone format
        $invalid = BankGuard::validate('gopay', '123456');
        $this->assertFalse($invalid['valid']);
    }

    public function test_it_validates_dana_number(): void
    {
        $valid = BankGuard::validate('dana', '085712345678');
        $this->assertTrue($valid['valid']);
    }

    public function test_it_validates_ovo_number(): void
    {
        $valid = BankGuard::validate('ovo', '089812345678');
        $this->assertTrue($valid['valid']);
    }
}
