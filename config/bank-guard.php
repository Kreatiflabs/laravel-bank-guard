<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto Sanitize Account Number
    |--------------------------------------------------------------------------
    |
    | When enabled, input account numbers with formatting characters like spaces,
    | dashes, or periods (e.g. "014-123-4567") will automatically be stripped
    | down to numeric characters only prior to validation and lookup.
    |
    */
    'auto_sanitize' => true,

    /*
    |--------------------------------------------------------------------------
    | Strict Validation Mode
    |--------------------------------------------------------------------------
    |
    | When strict mode is enabled, account numbers must strictly match the
    | exact allowed lengths and regex patterns defined for that bank.
    | If disabled, any numeric string between 8 and 20 digits is allowed for
    | banks without an explicit regex pattern.
    |
    */
    'strict' => true,

    /*
    |--------------------------------------------------------------------------
    | Blacklisted Account Numbers (Anti-Fraud Guard)
    |--------------------------------------------------------------------------
    |
    | List of account numbers or bank+account combinations flagged for fraud,
    | scam, or suspicious activity. Can also be extended via custom callback.
    |
    | Format:
    | - Simple string: '1234567890' (blocks this account on ANY bank)
    | - Associative: ['bank' => 'BCA', 'account' => '1234567890', 'reason' => 'Fraud report #123']
    |
    */
    'blacklist' => [
        // '0000000000',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom or Override Banks
    |--------------------------------------------------------------------------
    |
    | You can add your own custom financial institutions, virtual accounts,
    | or override existing bank rules here.
    |
    */
    'custom_banks' => [
        // [
        //     'code' => '999',
        //     'name' => 'Bank Custom Indonesia',
        //     'short_name' => 'CustomBank',
        //     'aliases' => ['custom', 'custombank'],
        //     'category' => 'digital',
        //     'account_lengths' => [10],
        //     'account_regex' => '^[0-9]{10}$',
        //     'is_active' => true,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Live Fraud Checker (CekRekening.id / Kredibel / Custom API)
    |--------------------------------------------------------------------------
    |
    | Supported drivers: 'config' (local blacklist array), 'api' (external HTTP API).
    |
    */
    'fraud' => [
        'driver' => env('BANK_GUARD_FRAUD_DRIVER', 'config'),
        'api' => [
            'endpoint' => env('BANK_GUARD_FRAUD_ENDPOINT'),
            'api_key' => env('BANK_GUARD_FRAUD_API_KEY'),
            'timeout' => 5,
        ],
    ],
];
