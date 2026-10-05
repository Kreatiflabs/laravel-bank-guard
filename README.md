# Laravel Bank Guard 🛡️🇮🇩

[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/Kreatiflabs/laravel-bank-guard/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Kreatiflabs/laravel-bank-guard/actions)
[![License](https://img.shields.io/github/license/Kreatiflabs/laravel-bank-guard?style=flat-square)](LICENSE.md)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.2-777BB4.svg?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10%20%7C%2011%20%7C%2012-FF2D20.svg?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![GitHub Stars](https://img.shields.io/github/stars/Kreatiflabs/laravel-bank-guard?style=flat-square)](https://github.com/Kreatiflabs/laravel-bank-guard)

**Laravel Bank Guard** adalah library Laravel komprehensif untuk mengelola **Master Data Bank Indonesia**, **Sanitasi Nomor Rekening**, dan **Validasi Akun Bank Presisi** beserta lapisan keamanan **Anti-Fraud / Blacklist Guard**.

Mendukung lebih dari 100+ bank di Indonesia: Bank BUMN (BRI, Mandiri, BNI, BTN), Bank Swasta (BCA, Danamon, CIMB, Permata, OCBC, dll.), Bank Syariah (BSI, BCA Syariah, Muamalat), Bank Digital (Jago, SeaBank, blu by BCA Digital, Allo Bank, Jenius, Krom, Raya), hingga seluruh BPD (Bank BJB, Bank DKI, Bank Jatim, dll.).

---

## ✨ Fitur Unggulan

- 🏦 **Master Data Bank Terlengkap & Up-to-Date:** Kode Transfer 3 digit (ATM Bersama / Prima), Kode SWIFT / BIC, Kode BI-FAST, alias populer, dan kategori.
- 🎯 **Validasi Nomor Rekening Presisi:** Aturan panjang digit pasti dan pola nomor rekening per bank (misal: BCA 10 digit, Mandiri 13 digit, BRI 15 digit, BNI 10 digit, BTPN 11 digit).
- 🔄 **Dynamic Form Validation Rule:** Validasi otomatis mencocokkan nomor rekening dengan field bank tujuan pada form request.
- 🧹 **Automatic Sanitizer:** Membersihkan format rekening dari spasi, tanda hubung (`-`), atau karakter non-angka secara instan.
- 🛡️ **Anti-Fraud & Blacklist Guard:** Pencegahan transaksi ke nomor rekening berisiko tinggi / terindikasi penipuan (dapat diintegrasikan ke database atau API pihak ketiga).
- 🎭 **Masking Helper:** Masking nomor rekening untuk log audit atau UI privasi (contoh: `1234567890` -> `12****7890`).
- ⚡ **Artisan CLI Tools:** Perintah bawaan terminal untuk mencari bank dan menguji validasi rekening langsung dari CLI.

---

## 📦 Instalasi

Install package melalui Composer:

```bash
composer require kreatiflabs/laravel-bank-guard
```

*(Opsional)* Publish file konfigurasi dan data master bank:

```bash
php artisan vendor:publish --tag=bank-guard-config
php artisan vendor:publish --tag=bank-guard-data
```

---

## 🚀 Penggunaan

### 1. Validasi di Form Request / Controller

#### A. Menggunakan Rule Object (Direkomendasikan)

```php
use Illuminate\Validation\Rule;
use Kreatiflabs\BankGuard\Rules\BankAccount;
use Kreatiflabs\BankGuard\Rules\BankCode;

public function rules(): array
{
    return [
        // Validasi bank tujuan valid di Indonesia
        'bank_code' => ['required', new BankCode()],

        // Validasi nomor rekening mencocokkan field 'bank_code' di request
        'account_number' => [
            'required',
            (new BankAccount())->forBankField('bank_code'),
        ],
    ];
}
```

#### B. Menggunakan Rule Macro

```php
use Illuminate\Validation\Rule;

public function rules(): array
{
    return [
        'bank'    => ['required', Rule::bankCode()],
        'account' => ['required', Rule::bankAccount('BCA')], // Bank statis (BCA)
    ];
}
```

#### C. Menggunakan String Syntax

```php
$request->validate([
    'bank'    => 'required|bank_code',
    'account' => 'required|bank_account:bca',
]);
```

---

### 2. Menggunakan Facade `BankGuard`

```php
use Kreatiflabs\BankGuard\Facades\BankGuard;

// 1. Mencari Bank berdasarkan Kode (014), Nama Singkat (BCA), Alias (bca), atau SWIFT
$bank = BankGuard::find('014');
$bank = BankGuard::find('bca');
$bank = BankGuard::find('jenius');
$bank = BankGuard::find('CENAIDJA');

echo $bank->name;        // "PT Bank Central Asia Tbk"
echo $bank->short_name;  // "BCA"
echo $bank->code;        // "014"
echo $bank->swift_code;  // "CENAIDJA"
echo $bank->bi_fast_code;// "CENAIDJA"

// 2. Cek Validasi Nomor Rekening Secara Manual
$result = BankGuard::validate('BCA', '1234567890');

if ($result['valid']) {
    echo "Rekening valid!";
    echo $result['account']; // Nomor rekening yang sudah disanitasi
} else {
    echo $result['message']; // Pesan kegagalan format
}

// 3. Quick Boolean Check
if (BankGuard::isValid('mandiri', '1234567890123')) {
    // Valid 13 digit
}

// 4. Validate or Throw Exception
BankGuard::validateOrFail('bri', '123456789012345'); // Melempar InvalidBankAccountException jika gagal
```

---

### 3. Sanitasi & Masking Nomor Rekening

```php
// Sanitasi: Menghapus spasi, strip, dan karakter non-angka
$clean = BankGuard::sanitize('014 - 123 - 4567');
// Output: '0141234567'

// Masking untuk tampilan UI atau Logging
$masked = BankGuard::mask('1234567890', 2, 4);
// Output: '12****7890'
```

---

### 4. Filter Kategori & Pencarian Bank

```php
use Kreatiflabs\BankGuard\Enums\BankCategory;

// Ambil semua bank BUMN (BRI, Mandiri, BNI, BTN)
$bumnBanks = BankGuard::category(BankCategory::BUMN);

// Ambil semua bank Digital (Jago, SeaBank, blu, Allo, Jenius, Krom, Raya)
$digitalBanks = BankGuard::category(BankCategory::DIGITAL);

// Ambil semua bank Syariah (BSI, BCA Syariah, Muamalat)
$syariahBanks = BankGuard::category(BankCategory::SYARIAH);

// Pencarian bebas berdasarkan kata kunci
$search = BankGuard::search('syariah');
```

---

### 5. Lapisan Anti-Fraud & Blacklist Guard

Anda dapat mendaftarkan nomor rekening bermasalah di file `config/bank-guard.php` atau menghubungkan resolver dinamis (ke Database / API CekRekening):

```php
use Kreatiflabs\BankGuard\Validators\BlacklistGuard;

// Daftarkan resolver custom di AppServiceProvider
BlacklistGuard::resolveUsing(function (string $accountNumber, ?string $bankCode) {
    // Cek ke tabel fraud/blacklist di database Anda:
    return \App\Models\FraudBlacklist::where('account_number', $accountNumber)->exists();
});
```

---

### 6. Perintah Artisan CLI

```bash
# Menampilkan seluruh daftar bank
php artisan bank-guard:list

# Filter berdasarkan kategori (bumn, swasta, syariah, digital, bpd)
php artisan bank-guard:list --category=digital

# Pencarian berdasarkan keyword
php artisan bank-guard:list --search=mandiri

# Validasi nomor rekening langsung dari terminal
php artisan bank-guard:validate bca 1234567890
php artisan bank-guard:validate 008 1234567890123
```

---

## 🧪 Testing

Jalankan test suite menggunakan PHPUnit:

```bash
composer test
# atau
vendor/bin/phpunit
```

---

## 📄 Lisensi

Open-source di bawah lisensi [MIT License](LICENSE.md). Dikembangkan dengan ❤️ oleh [Kreatiflabs](https://github.com/Kreatiflabs).
