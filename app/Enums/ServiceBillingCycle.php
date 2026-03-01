<?php

namespace App\Enums;

enum ServiceBillingCycle: string
{
    case Monthly = 'Monthly';
    case Yearly = 'Yearly';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Bulanan',
            self::Yearly => 'Tahunan',
        };
    }
}
