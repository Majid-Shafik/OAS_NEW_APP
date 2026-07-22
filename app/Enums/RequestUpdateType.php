<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RequestUpdateType: int implements HasColor, HasLabel
{
    case ACCEPT_RATE = 1;
    case COORDINATION_PERIOD = 2;
    case PAYMENT_PERIOD = 3;
    case SEC_SCHOOL_AGE = 4;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACCEPT_RATE => 'تعديل معدل الثانوية',
            self::COORDINATION_PERIOD => 'تعديل فترة التنسيق',
            self::PAYMENT_PERIOD => 'تمديد فترة التسديد',
            self::SEC_SCHOOL_AGE => 'تعديل عمر الثانوية',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACCEPT_RATE => 'info',
            self::COORDINATION_PERIOD => 'success',
            self::PAYMENT_PERIOD => 'warning',
            self::SEC_SCHOOL_AGE => 'danger',
        };
    }
}
