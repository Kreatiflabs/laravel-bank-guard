<?php

namespace Kreatiflabs\BankGuard\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class FraudReport implements Arrayable, Jsonable
{
    public function __construct(
        public readonly bool $is_reported,
        public readonly string $bank_code,
        public readonly string $account_number,
        public readonly string $status = 'clean', // 'clean', 'suspicious', 'fraud'
        public readonly int $report_count = 0,
        public readonly ?string $source = null,
        public readonly ?string $notes = null
    ) {}

    public static function clean(string $bankCode, string $accountNumber, ?string $source = null): self
    {
        return new self(
            is_reported: false,
            bank_code: $bankCode,
            account_number: $accountNumber,
            status: 'clean',
            report_count: 0,
            source: $source ?? 'Local Blacklist',
            notes: 'No fraud reports found.'
        );
    }

    public static function flagged(string $bankCode, string $accountNumber, int $reports = 1, ?string $reason = null, ?string $source = null): self
    {
        return new self(
            is_reported: true,
            bank_code: $bankCode,
            account_number: $accountNumber,
            status: $reports >= 3 ? 'fraud' : 'suspicious',
            report_count: $reports,
            source: $source ?? 'Local Blacklist',
            notes: $reason ?? 'Flagged in security blacklist.'
        );
    }

    public function toArray(): array
    {
        return [
            'is_reported' => $this->is_reported,
            'bank_code' => $this->bank_code,
            'account_number' => $this->account_number,
            'status' => $this->status,
            'report_count' => $this->report_count,
            'source' => $this->source,
            'notes' => $this->notes,
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
