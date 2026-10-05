<?php

namespace Kreatiflabs\BankGuard\Commands;

use Illuminate\Console\Command;
use Kreatiflabs\BankGuard\Facades\BankGuard;

class ValidateAccountCommand extends Command
{
    protected $signature = 'bank-guard:validate
                            {bank : Bank code, short name, or alias (e.g. BCA, 014, mandiri)}
                            {account : Account number to validate}';

    protected $description = 'Validate an Indonesian bank account number against its bank format & rules';

    public function handle(): int
    {
        $bankIdentifier = $this->argument('bank');
        $accountNumber = $this->argument('account');

        $this->info("Validating account for bank: {$bankIdentifier}");

        $result = BankGuard::validate($bankIdentifier, $accountNumber);

        if ($result['valid']) {
            $bank = $result['bank'];
            $this->info("✔ VALID! Account number is valid for {$bank->name} ({$bank->short_name}).");
            $this->line("  Cleaned Account : " . $result['account']);
            $this->line("  Masked Account  : " . BankGuard::mask($result['account'], 2, 4));
            $this->line("  Transfer Code   : " . $bank->code);
            return self::SUCCESS;
        }

        $this->error("✖ INVALID: " . $result['message']);
        return self::FAILURE;
    }
}
