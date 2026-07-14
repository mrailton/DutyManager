<?php

declare(strict_types=1);

namespace App\Enums;

enum ClinicalLevel: string
{
    case CFR = 'CFR';
    case FAR = 'FAR';
    case EFR = 'EFR';
    case EMT = 'EMT';
    case PARAMEDIC = 'PARAMEDIC';
    case ADVANCED_PARAMEDIC = 'ADVANCED_PARAMEDIC';

    public function label(): string
    {
        return match($this) {
            self::CFR => 'CFR',
            self::FAR => 'FAR',
            self::EFR => 'EFR',
            self::EMT => 'EMT',
            self::PARAMEDIC => 'Paramedic',
            self::ADVANCED_PARAMEDIC => 'Advanced Paramedic',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::CFR, self::FAR, self::EFR => 'badge-soft badge-error',
            self::EMT => 'badge-soft badge-success',
            self::PARAMEDIC => 'badge-soft badge-info',
            self::ADVANCED_PARAMEDIC => 'badge-soft badge-warning',
        };
    }
}
