<?php

namespace Kreatiflabs\BankGuard;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rule;
use Kreatiflabs\BankGuard\Commands\ListBanksCommand;
use Kreatiflabs\BankGuard\Commands\ValidateAccountCommand;
use Kreatiflabs\BankGuard\Data\BankRepository;
use Kreatiflabs\BankGuard\Rules\BankAccount;
use Kreatiflabs\BankGuard\Rules\BankCode;
use Kreatiflabs\BankGuard\Validators\AccountValidator;
use Kreatiflabs\BankGuard\Validators\BlacklistGuard;

class BankGuardServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bank-guard.php', 'bank-guard');

        $this->app->singleton(BankRepository::class, function ($app) {
            $config = $app['config']->get('bank-guard', []);
            return new BankRepository(
                dataPath: __DIR__ . '/../resources/data/banks.json',
                customBanks: $config['custom_banks'] ?? []
            );
        });

        $this->app->singleton(BlacklistGuard::class, function ($app) {
            $config = $app['config']->get('bank-guard', []);
            return new BlacklistGuard($config['blacklist'] ?? []);
        });

        $this->app->singleton(AccountValidator::class, function ($app) {
            $config = $app['config']->get('bank-guard', []);
            return new AccountValidator(
                repository: $app->make(BankRepository::class),
                blacklistGuard: $app->make(BlacklistGuard::class),
                strict: (bool) ($config['strict'] ?? true)
            );
        });

        $this->app->singleton('bank-guard', function ($app) {
            return new BankGuard(
                repository: $app->make(BankRepository::class),
                validator: $app->make(AccountValidator::class),
                blacklistGuard: $app->make(BlacklistGuard::class)
            );
        });

        $this->app->alias('bank-guard', BankGuard::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/bank-guard.php' => $this->app->configPath('bank-guard.php'),
            ], 'bank-guard-config');

            $this->publishes([
                __DIR__ . '/../resources/data/banks.json' => $this->app->resourcePath('bank-guard/banks.json'),
            ], 'bank-guard-data');

            $this->commands([
                ListBanksCommand::class,
                ValidateAccountCommand::class,
            ]);
        }

        $this->registerValidationRules();
    }

    /**
     * Register custom validation rules & macros for Laravel Validator.
     */
    protected function registerValidationRules(): void
    {
        // 1. String rule: 'account_number' => 'required|bank_account:bca'
        Validator::extend('bank_account', function ($attribute, $value, $parameters, $validator) {
            $bankIdentifier = $parameters[0] ?? null;

            // If parameter references another field in request (e.g., bank_account:bank_field)
            if ($bankIdentifier && array_key_exists($bankIdentifier, $validator->getData())) {
                $bankIdentifier = $validator->getData()[$bankIdentifier];
            }

            if (!$bankIdentifier) {
                return false;
            }

            /** @var BankGuard $bankGuard */
            $bankGuard = $this->app->make('bank-guard');
            return $bankGuard->isValid((string) $bankIdentifier, (string) $value);
        }, 'The :attribute is not a valid bank account number.');

        // 2. String rule: 'bank_code' => 'required|bank_code'
        Validator::extend('bank_code', function ($attribute, $value) {
            /** @var BankGuard $bankGuard */
            $bankGuard = $this->app->make('bank-guard');
            return $bankGuard->exists((string) $value);
        }, 'The selected :attribute is not a recognized bank.');

        // 3. Rule macro: Rule::bankAccount('BCA')
        if (class_exists(Rule::class)) {
            Rule::macro('bankAccount', function (?string $bank = null) {
                return new BankAccount($bank);
            });

            Rule::macro('bankCode', function () {
                return new BankCode();
            });
        }
    }
}
