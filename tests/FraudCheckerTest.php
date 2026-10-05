<?php

namespace Kreatiflabs\BankGuard\Tests;

use Illuminate\Support\Facades\Http;
use Kreatiflabs\BankGuard\Facades\BankGuard;
use Kreatiflabs\BankGuard\Services\FraudDrivers\CekRekeningApiDriver;

class FraudCheckerTest extends TestCase
{
    public function test_it_checks_clean_account_with_default_driver(): void
    {
        $report = BankGuard::checkFraud('014', '1234567890');

        $this->assertFalse($report->is_reported);
        $this->assertEquals('clean', $report->status);
        $this->assertEquals(0, $report->report_count);
    }

    public function test_it_checks_blacklisted_account_with_default_driver(): void
    {
        config(['bank-guard.blacklist' => ['9876543210']]);

        // Refresh container
        $this->app->forgetInstance('bank-guard');
        $this->app->forgetInstance(\Kreatiflabs\BankGuard\Validators\BlacklistGuard::class);

        $report = BankGuard::checkFraud('014', '9876543210');

        $this->assertTrue($report->is_reported);
        $this->assertNotEquals('clean', $report->status);
    }

    public function test_cekrekening_api_driver_returns_flagged_when_reported(): void
    {
        Http::fake([
            'api.cekrekening.id/*' => Http::response([
                'is_reported' => true,
                'report_count' => 5,
                'notes' => 'Indikasi penipuan belanja online',
            ], 200),
        ]);

        $driver = new CekRekeningApiDriver(
            endpoint: 'https://api.cekrekening.id/v1/check',
            apiKey: 'dummy_secret_key'
        );

        $report = $driver->check('014', '1234567890');

        $this->assertTrue($report->is_reported);
        $this->assertEquals('fraud', $report->status);
        $this->assertEquals(5, $report->report_count);
        $this->assertStringContainsString('penipuan', $report->notes);
    }
}
