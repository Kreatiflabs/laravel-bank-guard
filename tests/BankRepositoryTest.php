<?php

namespace Kreatiflabs\BankGuard\Tests;

use Kreatiflabs\BankGuard\Data\BankRepository;
use Kreatiflabs\BankGuard\Enums\BankCategory;

class BankRepositoryTest extends TestCase
{
    protected BankRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(BankRepository::class);
    }

    public function test_it_loads_all_banks(): void
    {
        $banks = $this->repository->all();
        $this->assertNotEmpty($banks);
        $this->assertGreaterThan(30, $banks->count());
    }

    public function test_it_finds_bank_by_code(): void
    {
        $bca = $this->repository->find('014');
        $this->assertNotNull($bca);
        $this->assertEquals('BCA', $bca->short_name);

        $mandiri = $this->repository->find('008');
        $this->assertNotNull($mandiri);
        $this->assertEquals('Mandiri', $mandiri->short_name);
    }

    public function test_it_finds_bank_by_alias(): void
    {
        $bri = $this->repository->find('brimo');
        $this->assertNotNull($bri);
        $this->assertEquals('002', $bri->code);

        $jago = $this->repository->find('jago');
        $this->assertNotNull($jago);
        $this->assertEquals('Bank Jago', $jago->short_name);
    }

    public function test_it_filters_by_category(): void
    {
        $bumn = $this->repository->getByCategory(BankCategory::BUMN);
        $this->assertNotEmpty($bumn);
        $codes = $bumn->pluck('code')->all();
        $this->assertContains('002', $codes); // BRI
        $this->assertContains('008', $codes); // Mandiri
        $this->assertContains('009', $codes); // BNI

        $digital = $this->repository->getByCategory('digital');
        $this->assertNotEmpty($digital);
    }

    public function test_it_searches_banks(): void
    {
        $results = $this->repository->search('syariah');
        $this->assertNotEmpty($results);

        $bcaResults = $this->repository->search('014');
        $this->assertNotEmpty($bcaResults);
        $this->assertEquals('014', $bcaResults->first()->code);
    }
}
