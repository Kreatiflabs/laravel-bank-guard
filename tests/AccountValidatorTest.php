<?php

namespace Kreatiflabs\BankGuard\Tests;

use Kreatiflabs\BankGuard\Facades\BankGuard;

class AccountValidatorTest extends TestCase
{
    public function test_it_validates_bca_account_number(): void
    {
        // BCA is 10 digits
        $validResult = BankGuard::validate('BCA', '1234567890');
        $this->assertTrue($validResult['valid']);

        // Invalid: 9 digits
        $invalidResult = BankGuard::validate('BCA', '123456789');
        $this->assertFalse($invalidResult['valid']);

        // Invalid: 11 digits
        $invalidResult2 = BankGuard::validate('BCA', '12345678901');
        $this->assertFalse($invalidResult2['valid']);
    }

    public function test_it_validates_mandiri_account_number(): void
    {
        // Mandiri is 13 digits
        $validResult = BankGuard::validate('Mandiri', '1234567890123');
        $this->assertTrue($validResult['valid']);

        $invalidResult = BankGuard::validate('Mandiri', '1234567890');
        $this->assertFalse($invalidResult['valid']);
    }

    public function test_it_validates_bri_account_number(): void
    {
        // BRI is 15 digits
        $validResult = BankGuard::validate('002', '123456789012345');
        $this->assertTrue($validResult['valid']);

        $invalidResult = BankGuard::validate('002', '1234567890');
        $this->assertFalse($invalidResult['valid']);
    }

    public function test_it_handles_formatted_input_with_sanitization(): void
    {
        // BCA 10 digits formatted with dashes
        $result = BankGuard::validate('bca', '014-123-4567');
        $this->assertTrue($result['valid']);
        $this->assertEquals('0141234567', $result['account']);
    }

    public function test_it_blocks_blacklisted_accounts(): void
    {
        config(['bank-guard.blacklist' => ['9999999999']]);

        // Refresh container to apply new config
        $this->app->forgetInstance('bank-guard');
        $this->app->forgetInstance(\Kreatiflabs\BankGuard\Validators\BlacklistGuard::class);
        $this->app->forgetInstance(\Kreatiflabs\BankGuard\Validators\AccountValidator::class);

        $result = BankGuard::validate('BCA', '9999999999');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('blacklisted', $result['message']);
    }
}
