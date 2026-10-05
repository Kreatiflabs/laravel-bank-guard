<?php

namespace Kreatiflabs\BankGuard\Enums;

enum BankCategory: string
{
    case BUMN = 'bumn';
    case SWASTA = 'swasta';
    case SYARIAH = 'syariah';
    case DIGITAL = 'digital';
    case BPD = 'bpd';

    public function label(): string
    {
        return match ($this) {
            self::BUMN => 'Bank BUMN / Pemerintah',
            self::SWASTA => 'Bank Swasta Nasional',
            self::SYARIAH => 'Bank Syariah',
            self::DIGITAL => 'Bank Digital',
            self::BPD => 'Bank Pembangunan Daerah (BPD)',
        };
    }
}
