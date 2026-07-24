<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethodEnum: int implements HasLabel, HasColor, HasIcon
{
    case NONE = 0;
    case POST = 1;
    case CAC_BANK = 2;
    case UNIVERSITY = 3;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NONE => 'غير مسدد',
            self::POST => 'البريد',
            self::CAC_BANK => 'بنك التسليف الزراعي',
            self::UNIVERSITY => 'مسئول التحصيل في الجامعة',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NONE => 'gray',
            self::POST => 'warning',
            self::CAC_BANK => 'success',
            self::UNIVERSITY => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::NONE => 'heroicon-o-minus',
            self::POST => 'heroicon-o-envelope',
            self::CAC_BANK => 'heroicon-o-building-library',
            self::UNIVERSITY => 'heroicon-o-user',
        };
    }
}
