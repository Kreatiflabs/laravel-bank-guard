<?php

namespace Kreatiflabs\BankGuard\Tests;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Kreatiflabs\BankGuard\Rules\BankAccount;
use Kreatiflabs\BankGuard\Rules\BankCode;

class BankAccountRuleTest extends TestCase
{
    public function test_it_passes_valid_bank_account_object_rule(): void
    {
        $validator = Validator::make([
            'account_number' => '1234567890',
        ], [
            'account_number' => ['required', new BankAccount('BCA')],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_it_fails_invalid_bank_account_object_rule(): void
    {
        $validator = Validator::make([
            'account_number' => '12345',
        ], [
            'account_number' => ['required', new BankAccount('BCA')],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('account_number', $validator->errors()->messages());
    }

    public function test_it_validates_dynamically_using_bank_field(): void
    {
        $validator = Validator::make([
            'bank_code' => '008', // Mandiri (13 digits)
            'account_number' => '1234567890123',
        ], [
            'bank_code' => ['required', new BankCode()],
            'account_number' => ['required', (new BankAccount())->forBankField('bank_code')],
        ]);

        $this->assertTrue($validator->passes());

        // Now test with invalid length for Mandiri
        $invalidValidator = Validator::make([
            'bank_code' => '008',
            'account_number' => '1234567890', // only 10 digits
        ], [
            'bank_code' => ['required', new BankCode()],
            'account_number' => ['required', (new BankAccount())->forBankField('bank_code')],
        ]);

        $this->assertFalse($invalidValidator->passes());
    }

    public function test_it_supports_rule_macro_syntax(): void
    {
        $validator = Validator::make([
            'bank' => 'bca',
            'account' => '1234567890',
        ], [
            'bank' => Rule::bankCode(),
            'account' => Rule::bankAccount('bca'),
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_it_supports_string_validator_rule_syntax(): void
    {
        $validator = Validator::make([
            'bank' => 'bca',
            'account' => '1234567890',
        ], [
            'bank' => 'required|bank_code',
            'account' => 'required|bank_account:bca',
        ]);

        $this->assertTrue($validator->passes());
    }
}
