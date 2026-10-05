<?php

namespace Kreatiflabs\BankGuard\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class VirtualAccountInfo implements Arrayable, Jsonable
{
    public function __construct(
        public readonly bool $is_virtual_account,
        public readonly ?string $bank_code = null,
        public readonly ?string $bank_name = null,
        public readonly ?string $provider = null,
        public readonly ?string $prefix = null,
        public readonly ?string $customer_number = null,
        public readonly ?string $raw_number = null
    ) {}

    public function toArray(): array
    {
        return [
            'is_virtual_account' => $this->is_virtual_account,
            'bank_code' => $this->bank_code,
            'bank_name' => $this->bank_name,
            'provider' => $this->provider,
            'prefix' => $this->prefix,
            'customer_number' => $this->customer_number,
            'raw_number' => $this->raw_number,
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
