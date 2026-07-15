<?php

declare(strict_types=1);

namespace App\Enums;

enum VehicleRole: string
{
    case RA = 'RA';
    case JEEP = 'JEEP';

    public function label(): string
    {
        return match($this) {
            self::RA => 'Road Ambulance',
            self::JEEP => 'Jeep',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::RA => 'badge-soft badge-info',
            self::JEEP => 'badge-soft badge-warning',
        };
    }
}
