<?php

namespace Kreatiflabs\BankGuard\Tests;

use Kreatiflabs\BankGuard\BankGuardServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    public static mixed $latestResponse = null;

    protected function getPackageProviders($app): array
    {
        return [
            BankGuardServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'BankGuard' => \Kreatiflabs\BankGuard\Facades\BankGuard::class,
        ];
    }
}
