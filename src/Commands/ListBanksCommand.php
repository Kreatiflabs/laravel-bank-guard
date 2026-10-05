<?php

namespace Kreatiflabs\BankGuard\Commands;

use Illuminate\Console\Command;
use Kreatiflabs\BankGuard\Facades\BankGuard;
use Kreatiflabs\BankGuard\Models\Bank;

class ListBanksCommand extends Command
{
    protected $signature = 'bank-guard:list
                            {--c|category= : Filter by category (bumn, swasta, syariah, digital, bpd)}
                            {--s|search= : Search by bank name, code, or alias}';

    protected $description = 'List all registered Indonesian banks supported by BankGuard';

    public function handle(): int
    {
        $banks = BankGuard::all();

        if ($category = $this->option('category')) {
            $banks = BankGuard::category($category);
        }

        if ($search = $this->option('search')) {
            $banks = BankGuard::search($search);
        }

        if ($banks->isEmpty()) {
            $this->warn('No banks found matching the criteria.');
            return self::SUCCESS;
        }

        $rows = $banks->map(function (Bank $bank) {
            return [
                'Code' => $bank->code,
                'Short Name' => $bank->short_name,
                'Full Name' => $bank->name,
                'Category' => $bank->category?->value ?? '-',
                'Lengths' => !empty($bank->account_lengths) ? implode(', ', $bank->account_lengths) : 'any',
                'SWIFT' => $bank->swift_code ?? '-',
                'BI-FAST' => $bank->bi_fast_code ?? '-',
            ];
        })->all();

        $this->table(['Code', 'Short Name', 'Full Name', 'Category', 'Lengths', 'SWIFT', 'BI-FAST'], $rows);
        $this->info("Total: {$banks->count()} banks found.");

        return self::SUCCESS;
    }
}
