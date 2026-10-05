<?php

namespace Kreatiflabs\BankGuard\Services\FraudDrivers;

use Illuminate\Support\Facades\Http;
use Kreatiflabs\BankGuard\Contracts\FraudCheckerInterface;
use Kreatiflabs\BankGuard\Models\FraudReport;
use Throwable;

class CekRekeningApiDriver implements FraudCheckerInterface
{
    public function __construct(
        protected ?string $endpoint = null,
        protected ?string $apiKey = null,
        protected int $timeout = 5
    ) {}

    public function check(string $bankCode, string $accountNumber): FraudReport
    {
        if (empty($this->endpoint) || empty($this->apiKey)) {
            return FraudReport::clean(
                bankCode: $bankCode,
                accountNumber: $accountNumber,
                source: 'CekRekening API (Disabled/Unconfigured)'
            );
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->endpoint, [
                    'bank' => $bankCode,
                    'account' => $accountNumber,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $isReported = (bool) ($data['is_reported'] ?? $data['reported'] ?? false);
                $reports = (int) ($data['report_count'] ?? $data['reports'] ?? 0);
                $notes = $data['notes'] ?? $data['message'] ?? null;

                if ($isReported || $reports > 0) {
                    return FraudReport::flagged(
                        bankCode: $bankCode,
                        accountNumber: $accountNumber,
                        reports: max(1, $reports),
                        reason: $notes,
                        source: 'CekRekening.id API'
                    );
                }
            }
        } catch (Throwable $e) {
            // Log or fallback to clean if network fails
        }

        return FraudReport::clean(
            bankCode: $bankCode,
            accountNumber: $accountNumber,
            source: 'CekRekening.id API'
        );
    }
}
