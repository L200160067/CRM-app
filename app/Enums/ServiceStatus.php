<?php

namespace App\Enums;

enum ServiceStatus: string
{
    case Active = 'Active';
    case Pending = 'Pending';
    case Suspended = 'Suspended';
    case Expired = 'Expired';

    public function label(): string
    {
        return match($this) {
            self::Active => 'Aktif',
            self::Pending => 'Tertunda',
            self::Suspended => 'Ditangguhkan',
            self::Expired => 'Kedaluwarsa',
        };
    }
}
